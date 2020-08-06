<?php

require_once('ipara_payment.php');

class EticSoft_ipara
{

    var $version = 200619;

    function pay($tr)
    {
        $tr->boid = $tr->id_cart;

        if ($tr->gateway_params->tdmode == 'on')
            $tr->tds = true;
        if ($tr->gateway_params->tdmode == 'off')
            $tr->tds = false;
        if ($tr->gateway_params->tdmode == 'auto') {

            $check_ipara = $this->getiParaOptions($tr);
            if (!$check_ipara OR $check_ipara == NULL) {
                $check_ipara = (object) array(
                            'result_code' => "Webservis çalışmıyor",
                            'supportsInstallment' => "1",
                            'cardThreeDSecureMandatory' => "1",
                            'merchantThreeDSecureMandatory' => "1",
                            'result' => "1",
                );
            }

            if ($check_ipara->result == '0') {
                $tr->debug('WebServis Hatası ' . $check_ipara->errorMessage);
                $tr->result_code = 'REST-' . $check_ipara->errorCode;
                $tr->result_message = 'WebServis Hatası ' . $check_ipara->errorMessage;
                $tr->result = false;
                return $tr;
            }
            if ($check_ipara->supportsInstallment != '1' AND $tr->installment > 1) {
                $tr->debug('Taksit Seçimi Hatası ' . $check_ipara->errorMessage);
                $tr->result_code = 'REST-3D-1';
                $tr->result_message = 'Kartınız taksitli alışverişi desteklemiyor. Lütfen tek çekim olarak deneyiniz';
                $tr->result = false;
                return $tr;
            }

            if ($check_ipara->cardThreeDSecureMandatory != '0'
                    OR $check_ipara->merchantThreeDSecureMandatory != '0')
                $tr->tds = true;
            else
                $tr->tds = false;
        }

        $obj = $this->getPaymentObject($tr);
        if ($tr->tds) {
            $tr->result_code = '3D-R';
            $tr->result_message = '3D formu oluşturuldu.';
            $tr->result = false;
            $tr->tds = true;
            $tr->save();
            try {
                $tr->tds_echo = $obj->payThreeD();
            } catch (Exception $e) {
                $tr->result_code = 'IPARA-LIB-ERROR';
                $tr->result_message = $e->getMessage();
                $tr->debug($tr->result_code . ' ' . $tr->result_message);
                $tr->result = false;
            }
            return $tr;
        }
        /* PAY Via API */
        try {
            $response = $obj->pay();
            $tr->result_code = $response['error_code'];
            $tr->boid = $response['order_id'];
            $tr->result_message = $response['error_message'];
            $tr->result = (string) $response['result'] == "1" ? true : false;
        } catch (Exception $e) {
            $tr->result_code = 'IPARA-LIB-ERROR';
            $tr->result_message = $e->getMessage();
            $tr->debug($tr->result_code . ' ' . $tr->result_message);
            $tr->result = false;
        }
        return $tr;
    }

    public function tdValidate($tr)
    {
        if (!isset($_POST['result']) AND ! isset($_POST['hash'])) {
            $tr->result_message = "Eksik Parametre " . Etictools::getValue('errorMessage');
            $tr->result = false;
            $tr->result_code = 0;
            return $tr;
        }

        $response = $_POST;
        $hash_text = $response['orderId']
                . $response['result']
                . $response['amount']
                . $response['mode']
                . $response['errorCode']
                . $response['errorMessage']
                . $response['transactionDate']
                . $response['publicKey']
                . $tr->gateway_params->private;
        $hash = base64_encode(sha1($hash_text, true));
        if ($hash != $response['hash']) { // has yanlışsa
            $tr->debug("HASH uymlu değil. Gelen:" . $response['hash'], true);
            $tr->result_message = "Hash uyumlu değil";
            $tr->result = false;
            $tr->result_code = $_POST['errorCode'];
            return $tr;
        }
        if ($response['result'] != 1) { // 3D doğrulama başarısz
            $tr->result = false;
            $tr->result_code = $response['errorMessage'];
            $tr->result_message = $response['errorCode'];
            return $tr;
        }

        $obj = $this->getPaymentObject($tr);
        $obj->three_d_secure_code = $response['threeDSecureCode'];
        $obj->order_id = $response['orderId'];
        $obj->amount = $response['amount'] / 100;
        $obj->echo = "EticSoft";
        try {
            $result = $obj->pay();
        } catch (Exception $e) { // çekim başarısız doğrulama başarılı
            $tr->debug("iPara Lib::Pay error" . $e->getMessage(), true);
            $tr->result_code = $_POST['errorCode'];
            $tr->result_message = $e->getMessage();
            $tr->result = false;
            return $tr;
        }
        $tr->result = (int) $result['result'] != 1 ? false : true;
        $tr->result_code = $result['errorCode'];
        $tr->result_message = $result['errorMessage'];
        return $tr;
    }

    private function getPaymentObject($tr)
    {
        $orderid = $tr->id_cart . time();
        $public_key = $tr->gateway_params->public;
        $private_key = $tr->gateway_params->private;
        $ipara_products = array();  // aşağıda düzenlenecek;
        $ipara_address = array();  //aşağıda düzenlenecek
        $ipara_purchaser = array();  // aşağıda düzenlenecek
        $ipara_card = array(// Kredi kartı bilgileri
            'owner_name' => $tr->cc_name,
            'number' => $tr->cc_number,
            'expire_month' => str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT),
            'expire_year' => $tr->cc_expire_year,
            'cvc' => $tr->cc_cvv
        );

        // Müşteri
        $ipara_purchaser['name'] = $tr->customer_firstname;
        $ipara_purchaser['surname'] = $tr->customer_lastname;
        $ipara_purchaser['email'] = $tr->customer_email;
        $ipara_purchaser['birthdate'] = NULL;
        $ipara_purchaser['gsm_number'] = $tr->customer_mobile;
        $ipara_purchaser['tc_certificate_number'] = NULL;


        // ADRES
        $ipara_address['name'] = $tr->customer_firstname;
        $ipara_address['surname'] = $tr->customer_lastname;
        $ipara_address['address'] = $tr->customer_address;
        $ipara_address['zipcode'] = null;
        $ipara_address['city_code'] = 34;
        $ipara_address['city_text'] = $tr->customer_city;
        $ipara_address['country_code'] = "TR";
        $ipara_address['country_text'] = "Türkiye";
        $ipara_address['phone_number'] = $tr->customer_phone;
        $ipara_address['tax_number'] = NULL;
        $ipara_address['tax_office'] = NULL;
        $ipara_address['tc_certificate_number'] = NULL;
        $ipara_address['company_name'] = NULL;


        // ÜRÜNLER
        $extra_id = 0;
        foreach ($tr->product_list as $item) {
            if ($item['price'] == 0)
                continue;

            $ipara_products[$extra_id]['title'] = $item['name'];
            $ipara_products[$extra_id]['code'] = $item['id_product'];
            $ipara_products[$extra_id]['quantity'] = $item['quantity'];
            $ipara_products[$extra_id]['price'] = $item['quantity'] * ((int) $item['price']);
            $extra_id++;
        }

        // Ön Ödeme Sepet boşşa prepay_amount
        if (EticTools::getValue('prepay_amount') && EticTools::getValue('prepay_amount') > 0) {
            $ipara_products = array(); // Sepeti Sıfırla
            $ipara_products[$extra_id]['title'] = 'Alışveriş öncesi ön ödeme';
            $ipara_products[$extra_id]['code'] = '1' . $extra_id;
            $ipara_products[$extra_id]['quantity'] = 1;
            $ipara_products[$extra_id]['price'] = EticTools::getValue('prepay_amount');
            $extra_id++;
        }

        $obj = new iParaPayment();
        $obj->public_key = $public_key;
        $obj->private_key = $private_key;
        $obj->mode = $tr->gateway_params->test_mode == "on" ? "T" : "P";
        $obj->order_id = $orderid;
        $obj->installment = $tr->installment;
        $obj->amount = $tr->total_pay;
        $obj->vendor_id = 4;
        $obj->echo = "echo message";
        $obj->products = $ipara_products;
        $obj->shipping_address = $ipara_address;
        $obj->invoice_address = $ipara_address;
        $obj->card = $ipara_card;
        $obj->purchaser = $ipara_purchaser;
        if ($tr->tds) {
            $obj->success_url = $tr->ok_url;
            $obj->failure_url = $tr->fail_url;
        }
        return $obj;
    }

    public function getiParaOptions($tr)
    {
        $public_key = $tr->gateway_params->public;
        $private_key = $tr->gateway_params->private;
        $binNumber = substr($tr->cc_number, 0, 6);
        $transactionDate = date("Y-m-d H:i:s");
        $token = $public_key . ":" . base64_encode(sha1($private_key . $binNumber . $transactionDate, true));
        $data = array("binNumber" => $binNumber);
        $data_string = json_encode($data);

        $ch = curl_init('https://api.ipara.com/rest/payment/bin/lookup');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length:' . strlen($data_string),
            'token:' . $token,
            'transactionDate:' . $transactionDate,
            'version:' . '1.0',
        ));
        $response = curl_exec($ch);
        if (curl_error($ch)) {
            $tr->debug('iPara Webservise erişimde sorun yaşandı: ' . curl_error($ch));
            return (object) array(
                        'result_code' => "Webservise erişimde sorun yaşandı",
                        'supportsInstallment' => "1",
                        'cardThreeDSecureMandatory' => "1",
                        'merchantThreeDSecureMandatory' => "1",
                        'result' => "0",
            );
        }
        return json_decode($response);
    }

}

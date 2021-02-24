<?php

class EticSoft_moka
{

    var $version = 210210;

    function pay($tr)
    {

        global $cart, $link;
	    $orderid = 'ETIC_' . date("dmY") . '_' . $tr->id_cart;
        

        $dealercode = $tr->gateway_params->DealerCode;
        $username = $tr->gateway_params->Username;
        $password = $tr->gateway_params->Password;

        $moka['PaymentDealerAuthentication'] = array(
            'DealerCode' => $dealercode,
            'Username' => $username,
            'Password' => $password,
            'CheckKey' => hash('sha256', $dealercode . 'MK' . $username . 'PD' . $password)
        );

        $moka['PaymentDealerRequest'] = array(
            'CardHolderFullName' => $tr->cc_name,
            'CardNumber' => $tr->cc_number,
			//'CardNumber' => str_replace(' ', '', Etictools::getValue('eticsoft_moka_cc_number')),
            'ExpMonth' => str_pad($tr->cc_expire_month, 2, '0', STR_PAD_LEFT),
            'ExpYear' => "20" . substr($tr->cc_expire_year, -2),
            'CvcNumber' => $tr->cc_cvv,
            'Amount' => $tr->total_pay,
            'Currency' => $tr->currency_code != 'TRY' ? $tr->currency_code : 'TL',
            'InstallmentNumber' => $tr->installment,
            'OtherTrxCode' => (string) $orderid,
            'ClientIP' => $tr->cip,
            'Software' => 'Prestashop',
            'RedirectUrl' => $tr->ok_url,
            "IntegratorId" => 1
        );

        //    print_r($moka); exit;

        // $api_url = "https://service.moka.com/PaymentDealer/DoDirectPayment";
		// $td_url = "https://service.moka.com/PaymentDealer/DoDirectPaymentThreeD";
		
//        $api_url = "https://service.moka.com/PaymentDealer/DoDirectPayment";
//		$td_url = "https://service.testmoka.com/PaymentDealer/DoDirectPaymentThreeD";
	    if($tr->test_mode){
		    $api_url = "https://service.testmoka.com/PaymentDealer/DoDirectPayment";
		    $td_url = "https://service.testmoka.com/PaymentDealer/DoDirectPaymentThreeD";
	    }
	    else{
		    $api_url = "https://service.moka.com/PaymentDealer/DoDirectPayment";
		    $td_url = "https://service.moka.com/PaymentDealer/DoDirectPaymentThreeD";
	    }

        if ($tr->gateway_params->tdmode == 'off') {
            $result = json_decode(Eticsoft_moka::curlPostExt(json_encode($moka), $api_url, true));
	
//	        print_r($moka); exit;
            if (!$result OR $result == NULL) {
                $tr->result_code= 'CURL-LOAD_ERROR';
                $tr->result_message = 'WebServis Error';
				$tr->debug(" Response: ".$result);
				$tr->notify = true;
                return $tr;
            }

            if (isset($result->ResultCode) AND $result->ResultCode == "Success") {
                if ($result->Data->IsSuccessful) {
                    $tr->result_code= '99';
                    $tr->result_message = $result->ResultCode;
                    $tr->result = true;
                    return $tr;
                }
                $tr->result_code= $result->Data->ResultCode;
                $tr->result_message = $result->Data->ResultMessage;
                return $tr;
            }
			$tr->debug(" Response: ".$result);
			$tr->notify = true;
            $tr->result_code= $result->ResultCode;
            $tr->result_message = $result->ResultMessage != '' ? $result->ResultMessage : $this->l('Payment Failed');
            return $tr;
        }
        // 3DSecure
        $result = json_decode(Eticsoft_moka::curlPostExt(json_encode($moka), $td_url, true));
//        print_r($result); exit();
        if (!$result OR $result == NULL) {
            $tr->result_code= 'CURL-LOAD_ERROR';
            $tr->result_message = 'WebServis Error ';
			$tr->debug(" Response: ".$result);
			$tr->notify = true;
			return $tr;
        }

        if (isset($result->ResultCode) AND $result->ResultCode == "Success") {
            if ($result->Data) {
				$tr->debug(" Setting header to redirect 3DS URL, see you");
				$tr->save();
                header("Location:" . $result->Data);
				exit;
            }
            $tr->result_code= $result->Data->ResultCode;
            $tr->result_message = $result->Data->ResultMessage;
        } else {
			$tr->debug(" Response: ".print_r($result, true));
			$tr->notify = true;
            $tr->result_code = 'Unknown response';
            $tr->result_message = 'Payment Failed';
        }
		 return $tr;
    }
	
	
    public static function curlPostExt($data, $url, $json = false)
    {
        $ch = curl_init(); // initialize curl handle
        curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        if ($json)
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 4s
        curl_setopt($ch, CURLOPT_POST, 1); // set POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // add POST fields
        if ($result = curl_exec($ch)) { // run the whole process
            curl_close($ch);
            return $result;
        }
        return false;
    }


    public function tdValidate($tr)
    { 
		$tr->result_code = Etictools::getValue('resultCode');
		$tr->result_message = Etictools::getValue('resultMessage');
		$tr->result = Etictools::getValue('isSuccessful') == 'True' ? true : false;
	    $tr->boid = 'ETIC_' . date("dmY") . '_' . $tr->id_cart;
		return $tr;
    }
}
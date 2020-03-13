<?php

class Eticsoft_Payu
{
	public $version = 180110;

    public function pay($tr)
    {
        $isl = 1;  
		$phone = $tr->customer_mobile ? $tr->customer_mobile : $tr->customer_phone;
		$phone = substr($phone, -11);
		$phone = preg_replace("/[^0-9]/", "", $phone);
		$phone = str_pad($phone, 11, "0", STR_PAD_LEFT);
		if (!$phone OR $phone == NULL OR strlen($phone) < 10 OR substr($phone, 0, 2) == '00')
			$phone = '02120000000';

		$url = "https://secure.payu.com.tr/order/alu.php";
		$secretKey = $tr->gateway_params->payu_key;
		$products_array = array();
		$arParams = array(
			"MERCHANT" => $tr->gateway_params->payu_merchant,
			"ORDER_REF" => 'ETCSFT'.$tr->id_cart . date("ymdhis"),
			"ORDER_DATE" => gmdate('Y-m-d H:i:s'),
			"PRICES_CURRENCY" => "TRY",
			"PAY_METHOD" => "CCVISAMC",
			"SELECTED_INSTALLMENTS_NUMBER" => $tr->installment,
			"CC_NUMBER" => $tr->cc_number,
			"EXP_MONTH" => $tr->cc_expire_month,
			"EXP_YEAR" => "20" . $tr->cc_expire_year,
			"CC_CVV" => $tr->cc_cvv,
			"CC_OWNER" => $tr->cc_name,
			"BACK_REF" => $tr->ok_url,
			"CLIENT_IP" => $tr->cip,
			"BILL_LNAME" => $tr->customer_firstname,
			"BILL_FNAME" => $tr->customer_lastname,
			"BILL_EMAIL" => $tr->customer_email,
			"BILL_PHONE" => $phone,
			"BILL_COUNTRYCODE" => "TR",
			"BILL_ZIPCODE" => "", //optional
			"BILL_ADDRESS" => $tr->customer_address,
			"BILL_ADDRESS2" => "",
			"BILL_CITY" => $tr->customer_city,
			"BILL_STATE" => 'TR',
			"BILL_FAX" => "", //optional
			"DELIVERY_LNAME" => $tr->customer_firstname, //optional
			"DELIVERY_FNAME" => $tr->customer_lastname, //optional
			"DELIVERY_EMAIL" => $tr->customer_email, //optional
			"DELIVERY_PHONE" => $tr->customer_mobile, //optional
			"DELIVERY_COMPANY" => "Company Name", //optional
			"DELIVERY_ADDRESS" => $tr->customer_address, //optional
			"DELIVERY_ADDRESS2" => "",
			"DELIVERY_ZIPCODE" => "", //optional
			"DELIVERY_CITY" => $tr->customer_city,
			"DELIVERY_STATE" => "",
			"DELIVERY_COUNTRYCODE" => "TR", //optional
		);

		$i = 0;
		foreach ($tr->product_list as $p) {
			$arParams["ORDER_PNAME[" . $i . "]"] = $p['name'];
			$arParams["ORDER_PCODE[" . $i . "]"] = $p['id_product'];
			$arParams["ORDER_PINFO[" . $i . "]"] = $p['name'];
			$arParams["ORDER_PRICE[" . $i . "]"] = $p['price'];
			$arParams["ORDER_QTY[" . $i . "]"] = $p['quantity'];
			$i++;
		}

		$shipping = $tr->getShippingPrice();
		if ($shipping > 0) {
			$arParams["ORDER_PNAME[" . $i . "]"] = 'Kargo Gönderimi';
			$arParams["ORDER_PCODE[" . $i . "]"] = 'KARGO';
			$arParams["ORDER_PINFO[" . $i . "]"] = 'Gönderi ücreti';
			$arParams["ORDER_PRICE[" . $i . "]"] = $shipping;
			$arParams["ORDER_QTY[" . $i . "]"] = 1;
			$i++;
		}

		if ($tr->getDiscounts() > 0)
			$arParams["DISCOUNT"] = $dicounts;




		ksort($arParams);
		$hashString = "";
		foreach ($arParams as $key => $val) {
			$hashString .= strlen($val) . $val;
		}

		$arParams["ORDER_HASH"] = hash_hmac("md5", $hashString, $secretKey);
		//end HASH calculation

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arParams));
		$response = curl_exec($ch);
		$replace_from = array($tr->cc_number, $tr->cc_cvv);	
		
		$arParams['CC_NUMBER'] = EticTools::maskCcNo($arParams['CC_NUMBER']);
		unset($arParams['CC_CVV']);

		$tr->debug("Requested ". json_encode($arParams));
		$tr->debug("Response " . $response);
		
		if (curl_errno($ch)) { // CURL HATASI
			$tr->result_code = "APICURL " . curl_errno($ch) . curl_error($ch);
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi";
			$tr->debug("Curl Error AC " . curl_errno($ch) . curl_error($ch) . ' ' . $response);
			return $tr;
		}
		
		if(!$parsedXML = simplexml_load_string($response)){ // OKUMA HATASI
			$tr->result_code = "APIXMLLOAD";
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
			return $tr;
		}
		
		if (($parsedXML->RETURN_CODE == "3DS_ENROLLED") && (!empty($parsedXML->URL_3DS))) {
			$tr->save();
			header("Location:" . $parsedXML->URL_3DS);
			die();
		}
		
		$tr->result = $parsedXML->STATUS == "SUCCESS" ? true : false;
		$tr->boid = $parsedXML->REFNO;
		$tr->result_code = $parsedXML->RETURN_CODE;
		$tr->result_message = $parsedXML->RETURN_MESSAGE;
		return $tr;

    }
	
    public function tdValidate($tr)
    {
		
		if (!EticTools::getValue('HASH')) {
			$tr->result = false;
			$tr->result_code = 'INTHV';
			$tr->result_code = 'NO POST[HASH] HERE';
			$tr->notify = true;
			$tr->debug(' POST was :'.print_r($_POST, true));
			return $tr;
		}
			 
		//begin HASH verification
		$arParams = $_POST;
		unset($arParams['HASH']);
	 
		$hashString = "";
		foreach ($arParams as $val) 
			$hashString .= strlen($val) . $val;
	 
		$secretKey = $tr->gateway_params->payu_key;
		$expectedHash = hash_hmac("md5", $hashString, $secretKey);
		if ($expectedHash != $_POST["HASH"]) {
			$tr->result = false;
			$tr->result_code = 'INTHV';
			$tr->result_code = 'HASH MISSMATCHED';
			$tr->notify = true;
			$tr->debug('HASH MISSMATCHED POST was :'.print_r($_POST, true));
			return $tr;
		}
		$payuTranReference = $_POST['REFNO'];
		$amount = $_POST['AMOUNT'];
		$currency = $_POST['CURRENCY'];
		$installments_no = $_POST['INSTALLMENTS_NO'];

		if($_POST['STATUS'] == "SUCCESS") 
			$tr->result = $_POST['STATUS'] == "SUCCESS" ? true : false;
		$tr->result_code	= $_POST['RETURN_CODE'];
		$tr->result_message 		= $_POST['RETURN_MESSAGE'];
		return $tr;
	}

}

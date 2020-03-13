<?php

class EticSoft_paytrek
{

    var $version = 191115;

    function pay($tr)
    {
		$tr->result = false;
		return $this->directCharge($tr);
    }
	
	
	function directCharge($tr) {

		$url = 'https://secure.paytrek.com.tr/api/v2/direct_charge/';
		if($tr->test_mode)
			$url = 'https://sandbox.paytrek.com/api/v2/direct_charge/';

		$sale_items = array();		
		foreach ($tr->product_list as $item) {
            if ($item['price'] == 0)
                continue;
			$sale_items []= array(
				"name" => $item['name'],
				"photo" => "",
				"quantity" =>  $item['quantity'],
				"unit_price" => number_format($item['price'], 2, '.', ''),
			);
		}
		
			if($tr->customer_phone)
			{
				$phone = $tr->customer_phone;
			}else{
				$phone = $tr->customer_mobile;
			}
			
		$array = array(
			"amount" => number_format($tr->total_pay, 2, '.', ''),
			"order_id" => $tr->id_transaction,
			"secure_option" => $tr->gateway_params->tdmode != 'off' ? true : false,
			"return_url" => $tr->ok_url,
			"cancel_url" => $tr->fail_url,
			"installment" => $tr->installment,
			"pre_auth" => false,
			"number" => $tr->cc_number,
			"cvc" => $tr->cc_cvv,
			"expiration" => str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT).'/20' . substr($tr->cc_expire_year, -2),
			"card_holder_name" => $tr->cc_name,
			"currency" => $tr->currency_code,
			"customer_first_name" => $tr->customer_firstname,
			"customer_last_name" => $tr->customer_lastname,
			"customer_email" => $tr->customer_email,
			"customer_ip_address" => $tr->cip,
			"billing_country" => 'TR',
			"billing_city" => $tr->customer_city,
			"billing_zipcode" => '07000',
			"billing_address" => $tr->customer_address,
			"billing_phone" => $phone,
			"items" => $sale_items,
			"sale_data" => array(
				"merchant_name" => $tr->shop_name,
				"vendor_id" => 1005,
			)
		);
  
 
  
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  "Content-Type: application/json",
		  "Authorization: Basic ".base64_encode($tr->gateway_params->api_key.':'.$tr->gateway_params->secret_key)
		));
        try {
			$response = curl_exec($ch);
			$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		}
		catch (Exception $e) {
            $tr->debug("Curl error".curl_errno($ch) . curl_error($ch) . $e->getMessage(), true);
            $tr->result_code = curl_error($ch);
            $tr->result_message = $e->getMessage();
            return $tr;
		}
		
		if(curl_error($ch)){
            $tr->debug("Curl error".curl_errno($ch) . curl_error($ch));
            $tr->result_code = curl_error($ch);
            $tr->result_message = $e->getMessage();
            return $tr;
		}
		
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		$charge_response = json_decode($response);
		
		if(!$charge_response){
		//	print_r($response); exit;
			$tr->debug('ChargeSale JSON Hatası ' . $response_code.' R'.base64_decode($response));
			$tr->result_code = 'CS03-'.$response_code;
			$tr->result_message = 'ChargeSale Auth Hatası '.$response_code;
			return $tr;				
		}
		
		if(!isset($charge_response->error))
			$charge_response->error = false;
		
		if((int)$response_code >= 400 ){
			$tr->debug('ChargeSale  Hatası ' . $response_code.' R'.$charge_response->error);
			$tr->result_code = 'CS01-'.$response_code;
			$tr->result_message = $charge_response->error;
			$tr->result = false;
			return $tr;				
		}
		
		if(isset($charge_response->forward_url)){
			$tr->debug('3D Secure Redirection ' . $response_code.' R'. base64_decode($response), true);
			header("Location:".$charge_response->forward_url);
		}
		
		if(isset($charge_response->succeeded) AND $charge_response->succeeded){
			$tr->boid = $charge_response->sale_token;
			$tr->result = true;
			if($tr->gateway_fee == 0 AND isset($charge_response->card_fee)){
				$tr->gateway_fee = $charge_response->card_fee;
				$installment = New EticInstallment($tr->family, $tr->installment);
				$installment->fee = $charge_response->card_fee_rate;
				$installment->save();
			}
			return $tr;
		}	
//			print_r($charge_response); exit;
		$tr->debug('Failed ApiPay ' . $response_code.' '.$charge_response->error);
		$tr->result_code = $charge_response->error;
		$tr->result_message = $charge_response->error_message;
		return $tr;
		
	}
	

	function create_paytrek_sale($tr) {
		$tr->payrek_sale_response = false;
		$tr->paytrek_status_code = 600;
		
		$url = 'https://secure.paytrek.com.tr/api/v2/sale/';
		if($tr->test_mode)
			$url = 'https://sandbox.paytrek.com/api/v2/sale/';

		$sale_items = array();		
		foreach ($tr->product_list as $item) {
            if ($item['price'] == 0)
                continue;
			$sale_items []= array(
				"name" => $item['name'],
				"photo" => "",
				"quantity" =>  $item['quantity'],
				"unit_price" => $item['price']
			);
		}
		
		$sale_body = array(
			"order_id" => (string)$tr->id_transaction,
			"secure_option" => $tr->gateway_params->tdmode == 'on' ? "Yes" : "No",
			"return_url" => $tr->ok_url,
			"cancel_url" => $tr->fail_url,
			"installment" => $tr->installment,
			"amount" => number_format($tr->total_pay, 2, '.', ''),
			"currency" => "/api/v1/currency/".$tr->currency_code."/",
			"customer_first_name" => $tr->customer_firstname,
			"customer_last_name" => $tr->customer_lastname,
			"customer_email" => $tr->customer_email,
			"billing_country" => 'TR',
			"billing_state" => "TR",
			"billing_city" => $tr->customer_city,
			"billing_zipcode" => '',
			"billing_address" => $tr->customer_address,
			"items" => $sale_items,
			"customer_ip_address" => $tr->cip
		);

				
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sale_body));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Authorization: Basic ".$tr->gateway_params->api_key.'"',
		"Content-Type:application/json"
		));
		
		$tr->debug('CreateSale request to '.$url.' params ' .base64_decode($sale_body));

        try {
			$response = curl_exec($ch);
			$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
		}
		catch (Exception $e) {
            $tr->debug("PayTrek Lib::Pay error".curl_errno($ch) . curl_error($ch) . $e->getMessage(), true);
            $tr->result_code = curl_error($ch);
            $tr->result_message = $e->getMessage();
            $tr->result = false;
            return $tr;
		}
		
				
		$tr->payrek_sale_response = $response ? json_decode($response) : $response;
		$tr->paytrek_status_code = $response_code;
	}	
	
	
	public function get_paytrek_sale($tr, $token){
		$url = 'https://secure.paytrek.com.tr/api/v2/sale/';
		if($tr->test_mode)
			$url = 'https://sandbox.paytrek.com/api/v2/sale/';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url.$token."/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, $tr->gateway_params->api_key.':'.$tr->gateway_params->secret_key);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Language: en-us",
			"Content-Type: application/json",
		));

		$response = curl_exec($ch);
		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return (object)array(
			'response' => $response ? json_decode($response) : $response,
			'status_code' => $response_code
		);		
	}
	
    public function tdValidate($tr)
    {
        $tr->result = false;
        if (!isset($_GET['token']) OR ! $_GET['token']) {
            $tr->result_message = "Eksik Parametre " . Etictools::getValue('errorMessage');
            $tr->result_code = 0;
            return $tr;
        }
		$token = $_GET['token'];
		
	
		$url = 'https://secure.paytrek.com.tr/api/v2/sale/';
		if($tr->test_mode)
			$url = 'https://sandbox.paytrek.com/api/v2/sale/';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url.$token."/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, $tr->gateway_params->api_key.':'.$tr->gateway_params->secret_key);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Language: en-us",
			"Content-Type: application/json",
		));
        try {
			$response = curl_exec($ch);
			$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
		}
		catch (Exception $e) {
            $tr->debug("GetSale error".curl_errno($ch) . curl_error($ch) . $e->getMessage(), true);
            $tr->result_code = curl_error($ch);
            $tr->result_message = $e->getMessage();
            return $tr;
		}
		
		$sale_info = json_decode($response);
			
		if(!$sale_info){
			$tr->debug('ChargeSale JSON Hatası ' . $response_code.' R'. base64_encode($response));
			$tr->result_code = 'TD02-'.$response_code;
			$tr->result_message = 'TD ChargeSale Auth Hatası '.$response_code;
			return $tr;				
		}
		

		if($sale_info->status == 'Declined'){
			$tr->result_code = 'TD03';
			$tr->result_message = '3D şifresi onaylanmadı';
			return $tr;
		}
		if($sale_info->status == 'Paid'){
			$tr->result = true;
			if($tr->gateway_fee == 0 AND isset($charge_response->card_fee)){
				$tr->gateway_fee = $charge_response->card_fee;
				$installment = New EticInstallment($tr->family, $tr->installment);
				$installment->fee = $charge_response->card_fee_rate;
				$installment->save();
			}
			return $tr;
		}
		$tr->result_code = $sale_info->status;
		$tr->result_message = '3D şifresi onaylanmadı';
		return $tr;
    }
}
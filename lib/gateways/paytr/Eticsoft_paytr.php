<?php


class EticSoft_paytr
{

	var $version = 200610;

	function pay($tr)
	{
        
		$tr->boid = $tr->id_cart;

		if ($tr->gateway_params->tdmode == 'on')
			$tr->tds = "0";
		if ($tr->gateway_params->tdmode == 'off')
			$tr->tds = "1";
		if ($tr->gateway_params->tdmode == 'auto') {
			$paytr = $this->getPayTrOptions($tr);
			if (isset($paytr["allow_non3d"])) 
				$tr->tds = $paytr["allow_non3d"] == "N" ? "0" : "1"; 
			else 
				$tr->tds = "0"; 
		}

		$merchant_id = $tr->gateway_params->merchant_id;
		$merchant_key = $tr->gateway_params->merchant_key;
		$merchant_salt = $tr->gateway_params->merchant_salt;

		$merchant_ok_url=$tr->ok_url;
		$merchant_fail_url=$tr->fail_url;
		$products = array();

		foreach ($tr->product_list as $key => $product) {
			$item = array($product["name"],$product["price"],$product["quantity"]); 
			array_push($products,$item);
		} 

		$user_basket = htmlentities(json_encode($products)); 

		srand(time());
		$merchant_oid = rand();

		$test_mode = $tr->gateway_params->test_mode == "on" ? "1" : "0";

        //3d'siz işlem
		$non_3d=$tr->tds; 

        //non3d işlemde, başarısız işlemi test etmek için 1 gönderilir (test_mode ve non_3d değerleri 1 ise dikkate alınır!)
		$non3d_test_failed="0"; 

		$user_ip = $tr->cip;

		$email = $tr->customer_email;

        // 100.99 TL ödeme
		$payment_amount = number_format($tr->total_pay,2,".","");
		$cc_name = $tr->cc_name;
		$cvc = $tr->cc_cvv;
		$cc_number = $tr->cc_number;
		$cc_month = $tr->cc_expire_month;
		$cc_year = $tr->cc_expire_year;

		$currency="TL";

		$bin = $this->getPayTrOptions($tr);   

		$payment_type = "card";


        $card_type = isset($bin["brand"]) ? $bin["brand"] : null;       // Alabileceği değerler; advantage, axess, combo, bonus, cardfinans, maximum, paraf, world
        $installment_count = $tr->installment == 1 ? 0 : $tr->installment;

        $post_url = "https://www.paytr.com/odeme";

        $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $payment_type . $installment_count. $currency. $test_mode. $non_3d;
        $token = base64_encode(hash_hmac('sha256',$hash_str.$merchant_salt,$merchant_key,true));

        $tr->result_code = '3D-R';
        $tr->result_message = '3D formu oluşturuldu.';
        $tr->result = false;
        $tr->tds = true;
        $tr->boid = $merchant_oid;
        //$tr->amount = $tr->total_pay;
        $tr->save();
        try {
        	$form = "";
            $form .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
            $form .= "<html>";
            $form .= "<body>";
            $form .= '<form action="'.$post_url.'" method="post" id="three_d_form"> ';
            $form .= '<input type="hidden" name="cc_owner" value="'.$cc_name.'"><br>';
            $form .= '<input type="hidden" name="card_number" value="'.$cc_number.'"><br>';
            $form .= '<input type="hidden" name="expiry_month" value="'.$cc_month.'" ><br>';
            $form .= '<input type="hidden" name="expiry_year" value="'.$cc_year.'"><br>';
            $form .= '<input type="hidden" name="cvv" value="'.$cvc.'"><br>';
            $form .= '<input type="hidden" name="merchant_id" value="'.$merchant_id.'">';
            $form .= '<input type="hidden" name="user_ip" value="'.$user_ip.'">';
            $form .= '<input type="hidden" name="merchant_oid" value="'.$merchant_oid.'">';
            $form .= '<input type="hidden" name="email" value="'.$email.'">';
            $form .= '<input type="hidden" name="payment_type" value="'.$payment_type.'">';
            $form .= '<input type="hidden" name="payment_amount" value="'.$payment_amount.'">';
            $form .= '<input type="hidden" name="currency" value="'.$currency.'">';
            $form .= '<input type="hidden" name="test_mode" value="'.$test_mode.'">';
            $form .= '<input type="hidden" name="non_3d" value="'.$non_3d.'">';
            $form .= '<input type="hidden" name="merchant_ok_url" value="'.$merchant_ok_url.'">';
            $form .= '<input type="hidden" name="merchant_fail_url" value="'.$merchant_fail_url.'">';
            $form .= '<input type="hidden" name="user_name" value="'.$tr->customer_firstname." ".$tr->customer_lastname.'">';
            $form .= '<input type="hidden" name="user_address" value="'.$tr->customer_address.'">';
            $form .= '<input type="hidden" name="user_phone" value="'.$tr->customer_phone.'">';
            $form .= '<input type="hidden" name="user_basket" value="'.$user_basket.'">';
            $form .= '<input type="hidden" name="debug_on" value="1">';
            $form .= '<input type="hidden" name="paytr_token" value="'.$token.'">';
            $form .= '<input type="hidden" name="non3d_test_failed" value="'.$non3d_test_failed.'">';
            $form .= '<input type="hidden" name="installment_count" value="'.$installment_count.'">';
        	if ($card_type != null)
        		$form .= '<input type="hidden" name="card_type" value="'.$card_type.'">'; 
        	$form .= '</form>'; 
        	$form .= "</body>";
        	$form .= "<script>document.getElementById(\"three_d_form\").submit();</script>";
        	$form .= "</html>";
        	$tr->tds_echo = $form;
        } catch (Exception $e) {
        	$tr->result_code = 'paytr-LIB-ERROR';
        	$tr->result_message = $e->getMessage();
        	$tr->debug($tr->result_code . ' ' . $tr->result_message);
        	$tr->result = false;
        }
        return $tr; 
    }

    public function tdValidate($tr)
    { 
        
    	if ($_GET["sprtdvalidate"] == "success") {
    		$tr->result = true;
    	}
    	else {
    		$tr->result_message = isset($_POST["fail_message"]) ? $_POST["fail_message"] : "";
    		$tr->result = false;
    		$tr->result_code = ""; 
    	} 
    	return $tr;
    }

    public function getPayTrOptions($tr)
    {


    	$merchant_id    = $tr->gateway_params->merchant_id;
    	$merchant_key   = $tr->gateway_params->merchant_key;
    	$merchant_salt  = $tr->gateway_params->merchant_salt;


    	$bin_number = substr($tr->cc_number, 0, 6);

    	$hash_str = $bin_number . $merchant_id . $merchant_salt;
    	$paytr_token=base64_encode(hash_hmac('sha256', $hash_str, $merchant_key, true));
    	$post_vals=array(
    		'merchant_id'=>$merchant_id,
    		'bin_number'=>$bin_number,
    		'paytr_token'=>$paytr_token
    	); 

    	$ch=curl_init();
    	curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/bin-detail");
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_POST, 1) ;
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    	$result = @curl_exec($ch);


    	if(curl_errno($ch))
    		die("PAYTR BIN detail request timeout. err:".curl_error($ch));

    	curl_close($ch);

    	$response=json_decode($result,1);  
    	return $response;

    }

}

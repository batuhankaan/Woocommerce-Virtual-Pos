<?php
require_once("src/loader.php");

class EticSoft_turkpos
{

	var $version = 201110;

	function pay($tr)
	{   

		if ($tr->gateway_params->tdmode == '3D') 
			$tr->tds = true;
		if ($tr->gateway_params->tdmode == 'off')
			$tr->tds = false;




		$CLIENT_CODE = $tr->gateway_params->client_code;
		$CLIENT_USERNAME = $tr->gateway_params->client_username;
		$CLIENT_PASSWORD = $tr->gateway_params->client_password;
		$GUID = $tr->gateway_params->guid;
		$MODE = $tr->gateway_params->test_mode == "on" ? "TEST" : "PROD";
		$rate = 0;
		$bin = new param\Bin($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE);
		$bin->send($tr->cc_number);
		$bin_response = $bin->parse();
		$posId = $bin_response["posId"];  

		$cc = new param\GetInstallmentPlanForUser($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE);
		$cc->send();
		$response = $cc->parse();  

		$prerate = str_pad($tr->installment, 2, '0', STR_PAD_LEFT); 


		foreach ($response as $key => $resp) {
			if ($resp[0]["SanalPOS_ID"] == $posId) { 
				$rate = $resp[0]["MO_$prerate"]; 
			} 
		}  

		if ($rate == -2) {
			$tr->result_code = '-1';
			$tr->result_message = "Kartınız ".$tr->installment." taksit desteklemiyor !"; 
			$tr->result = false;
			return $tr;
		}   

		// set new rates
		$rate_edit = (100 + $rate); 
		$t_cart = $tr->total_pay * 100 / $rate_edit;  
		$t_amount = $t_cart + ($t_cart * $rate / 100);  

		$order_id = $tr->id_order;
		$cc_holder       = $tr->cc_name;
		$cc_number       = $tr->cc_number;
		$cc_month       = str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT);
		$cc_year      = "20".str_pad(substr($tr->cc_expire_year, -2) ,2 ,"0", STR_PAD_LEFT);
		$cc_cvv      = $tr->cc_cvv; 
		$amount        = number_format($t_cart, 2, ',',"");
		$total_amount = number_format($t_amount, 2, ',',""); 
		 
		$ClientIp     = $tr->cip;
		$phone          =  $tr->customer_phone;
		$installment = $tr->installment;  
		$tr->boid = $tr->id_cart;   


		if ($tr->gateway_params->tdmode == 'auto') {
			try {
				$saleObj = new param\Sale($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE); 
				$saleObj->send( $posId, $cc_holder, $cc_number, $cc_month,  $cc_year, $cc_cvv, $phone, $tr->fail_url, $tr->ok_url,
					$order_id, $tr->shop_name, $installment, $amount, $total_amount, "", $ClientIp, $_SERVER['HTTP_REFERER'], "", "", "", "", ""
				); 

				$paramResponse = $saleObj->parse();   

				if ($paramResponse["Sonuc"] > 0 && $paramResponse["UCD_URL"] != "NONSECURE") {
					$tr->result_code = '3D-R';
					$tr->result_message = '3D formu oluşturuldu.';
					$tr->result = false;
					$tr->tds = true;
					$tr->save();
					$tr->boid = $paramResponse['Islem_ID'];
					$tr->result_message = $paramResponse["Sonuc_Str"];
					$tr->result = true; 
					$form = "";
					$form .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
					$form .= "<html>";
					$form .= "<body>";
					$form .= "<form action=\"" . $paramResponse["UCD_URL"] . "\" method=\"post\" id=\"three_d_form\"/>"; 
					$form .= "<noscript>";
					$form .= "<br/>";
					$form .= "<br/>";
					$form .= "<center>";
					$form .= "<h1>3D Secure Yönlendirme İşlemi</h1>";
					$form .= "<h2>Javascript internet tarayıcınızda kapatılmış veya desteklenmiyor.<br/></h2>";
					$form .= "<h3>Lütfen banka 3D Secure sayfasına yönlenmek için tıklayınız.</h3>";
					$form .= "<input type=\"submit\" value=\"3D Secure Sayfasına Yönlen\">";
					$form .= "</center>";
					$form .= "</noscript>";
					$form .= "</form>";
					$form .= "</body>";
					$form .= "<script>document.getElementById(\"three_d_form\").submit();</script>";
					$form .= "</html>"; 
					$tr->tds_echo = $form; 
				}
				else if ($paramResponse["Sonuc"] > 0 && $paramResponse["Islem_ID"] > 0 && $paramResponse["UCD_URL"] == "NONSECURE") {
					$tr->boid = $paramResponse['Islem_ID'];
					$tr->result_message = $paramResponse["Sonuc_Str"];
					$tr->result = true;  
				}
				else {
					$tr->result_code = $paramResponse['Sonuc'];
					$tr->result_message = $paramResponse["Sonuc_Str"];
					$tr->result = false; 
				}

				
			} catch (Exception $e) { 
				$tr->result_code = 'TURKPOS-LIB-ERROR';
				$tr->result_message = $e->getMessage();
				$tr->debug($tr->result_code . ' ' . $tr->result_message);
				$tr->result = false;
			} 
			
			return $tr;
		}

		if ($tr->tds) {
			$tr->result_code = '3D-R';
			$tr->result_message = '3D formu oluşturuldu.';
			$tr->result = false;
			$tr->tds = true;
			$tr->save();
			try {
				$saleObj = new param\Sale3d($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE); 
				$saleObj->send( $posId, $cc_holder, $cc_number, $cc_month,  $cc_year, $cc_cvv, $phone, $tr->fail_url, $tr->ok_url,
					$order_id, $tr->shop_name, $installment, $amount, $total_amount, "", $ClientIp, $_SERVER['HTTP_REFERER'], "", "", "", "", ""
				); 
				
				$paramResponse = $saleObj->parse();    
				$tr->boid = $paramResponse['Islem_ID'];
				$tr->result_message = $paramResponse["Sonuc_Str"];
				$tr->result = (string) $paramResponse['Sonuc'] > 0 ? true : false; 
				$form = "";
				$form .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
				$form .= "<html>";
				$form .= "<body>";
				$form .= "<form action=\"" . $paramResponse["UCD_URL"] . "\" method=\"post\" id=\"three_d_form\"/>"; 
				$form .= "<noscript>";
				$form .= "<br/>";
				$form .= "<br/>";
				$form .= "<center>";
				$form .= "<h1>3D Secure Yönlendirme İşlemi</h1>";
				$form .= "<h2>Javascript internet tarayıcınızda kapatılmış veya desteklenmiyor.<br/></h2>";
				$form .= "<h3>Lütfen banka 3D Secure sayfasına yönlenmek için tıklayınız.</h3>";
				$form .= "<input type=\"submit\" value=\"3D Secure Sayfasına Yönlen\">";
				$form .= "</center>";
				$form .= "</noscript>";
				$form .= "</form>";
				$form .= "</body>";
				$form .= "<script>document.getElementById(\"three_d_form\").submit();</script>";
				$form .= "</html>"; 
				$tr->tds_echo = $form;
			} catch (Exception $e) {
				$tr->result_code = 'TURKPOS-LIB-ERROR';
				$tr->result_message = $e->getMessage();
				$tr->debug($tr->result_code . ' ' . $tr->result_message);
				$tr->result = false;
			}
			return $tr;
		}
		/* PAY Via API */
		try {
			$saleObj = new param\Sale($CLIENT_CODE, $CLIENT_USERNAME, $CLIENT_PASSWORD, $GUID, $MODE); 
			$saleObj->send( $posId, $cc_holder, $cc_number, $cc_month,  $cc_year, $cc_cvv, $phone, $tr->fail_url, $tr->ok_url,
				$order_id, $tr->shop_name, $installment, $amount, $total_amount, "", $ClientIp, $_SERVER['HTTP_REFERER'], "", "", "", "", ""
			); 

			$paramResponse = $saleObj->parse();   
			$tr->boid = $paramResponse['Islem_ID'];
			$tr->result_message = $paramResponse["Sonuc_Str"];
			$tr->result = (string) $paramResponse['Sonuc'] == "1" ? true : false;

		} catch (Exception $e) {
			$tr->result_code = 'TURKPOS-LIB-ERROR';
			$tr->result_message = $e->getMessage();
			$tr->debug($tr->result_code . ' ' . $tr->result_message);
			$tr->result = false;
		}
		return $tr; 
	}


	public function tdValidate($tr)
	{

		if (!isset($_POST['TURKPOS_RETVAL_Sonuc_Str']) ) {
			$tr->result_message = "Eksik Parametre " . Etictools::getValue('errorMessage');
			$tr->result = false;
			$tr->result_code = 0;
			return $tr;
		}

		$response = $_POST; 

		if ($response["TURKPOS_RETVAL_Dekont_ID"] > 0) {
			$tr->result = true;
			$tr->result_code = $response['TURKPOS_RETVAL_Sonuc'];
			$tr->result_message = $response['TURKPOS_RETVAL_Sonuc_Str'];
			return $tr;
		}
		else {
			$tr->result = false;
			$tr->result_code = $response['TURKPOS_RETVAL_Sonuc'];
			$tr->result_message = $response['TURKPOS_RETVAL_Sonuc_Str'];
			return $tr;
		} 
	}





}

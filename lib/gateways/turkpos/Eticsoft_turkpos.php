<?php

class EticSoft_turkpos
{

	var $version = 210414; 

	function pay($tr)
	{  
		if (!extension_loaded('soap')) {
			$tr->result_message = "Sunucunuzda soap etkin değil !"; 
			$tr->result = false;	
			return $tr;
		} 
		
		$TEST_URL = "https://test-dmz.param.com.tr:4443/turkpos.ws/service_turkpos_test.asmx?wsdl";
		$PROD_URL = "https://dmzws.ew.com.tr/turkpos.ws/service_turkpos_prod.asmx?wsdl";

		if ($tr->gateway_params->tdmode == '3D') 
			$tr->tds = true;
		if ($tr->gateway_params->tdmode == 'off')
			$tr->tds = false;

		$test_mode = $tr->gateway_params->test_mode == "on" ? true : false; 

		$clientCode = $tr->gateway_params->client_code;  
		$clientUsername = $tr->gateway_params->client_username;
		$clientPassword = $tr->gateway_params->client_password;
		$guid = $tr->gateway_params->guid;

		if ($test_mode) {
			$clientCode ="10738";  
			$clientUsername = "Test"; 
			$clientPassword = "Test"; 
			$guid = "0c13d406-873b-403b-9c09-a5766840d98c"; 
		}

		$KK_Sahibi = $tr->cc_name;
		$KK_No =  $tr->cc_number;
		$KK_SK_Ay = str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT);
		$KK_SK_Yil = "20".str_pad(substr($tr->cc_expire_year, -2) ,2 ,"0", STR_PAD_LEFT);
		$KK_CVC = $tr->cc_cvv; 
		$KK_Sahibi_GSM = $tr->customer_phone;
		$Hata_URL = $tr->fail_url;
		$Basarili_URL = $tr->ok_url;
		$Siparis_ID = $tr->id_order.rand();
		$Taksit = $tr->installment;
		$Islem_Tutar = number_format($tr->total_cart, 2, ',',"");
		$Toplam_Tutar = number_format($tr->total_pay, 2, ',',"");  
		$Islem_Hash = "";
		$IPAdr = $tr->cip;
		$Islem_Guvenlik_Tip = $tr->tds == true ? "3D" : "NS";

		$data = $clientCode.$guid.$Taksit.$Islem_Tutar.$Toplam_Tutar.$Siparis_ID.$Hata_URL.$Basarili_URL;

		$url = $test_mode == true ? $TEST_URL : $PROD_URL;

		$mode = array
		(
			'soap_version' => 'SOAP_1_1',
			'trace' => 1,
			'stream_context' => stream_context_create(array(
				'ssl' => array(
					'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
				)
			))
		);


		$client = new SoapClient($url, $mode);

		$data = $clientCode.$guid.$Taksit.$Islem_Tutar.$Toplam_Tutar.$Siparis_ID.$Hata_URL.$Basarili_URL;

		try {
			$data = $client->SHA2B64(array("Data" => $data));

			$Islem_Hash = $data->SHA2B64Result;

		} catch (Exception $e) {
			$tr->result = false;
			$tr->result_code = 0;
			$tr->result_message = $e->getMessage();
			return $tr;
		}

		
		$Pos_Odeme_data = array(
			"G" => array("CLIENT_CODE" => $clientCode, "CLIENT_USERNAME" => $clientUsername, "CLIENT_PASSWORD" => $clientPassword),
			"GUID" => $guid, 
			"KK_Sahibi" => $KK_Sahibi,
			"KK_No" => $KK_No,
			"KK_SK_Ay" => $KK_SK_Ay,
			"KK_SK_Yil" => $KK_SK_Yil,
			"KK_CVC" => $KK_CVC,
			"KK_Sahibi_GSM" => $KK_Sahibi_GSM,
			"Hata_URL" => $Hata_URL,
			"Basarili_URL" => $Basarili_URL,
			"Siparis_ID" => $Siparis_ID,
			"Siparis_Aciklama" => "",
			"Taksit" => $Taksit,
			"Islem_Tutar" => $Islem_Tutar,
			"Toplam_Tutar" => $Toplam_Tutar,
			"Islem_Hash" => $Islem_Hash,
			"Islem_Guvenlik_Tip" => $Islem_Guvenlik_Tip,
			"Islem_ID" => "",
			"IPAdr" => $IPAdr,
			"Ref_URL" => "",
			"Data1" => "",
			"Data2" => "",
			"Data3" => "",
			"Data4" => "",
			"Data5" => "",
			"Data6" => "",
			"Data7" => "",
			"Data8" => "",
			"Data9" => "",
			"Data10" => ""

		);

		try {
			$sale = $client->Pos_Odeme($Pos_Odeme_data); 

			$paramResponse = (array)$sale->Pos_OdemeResult;  

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
			$tr->result = false;
			$tr->result_code = 0;
			$tr->result_message = $e->getMessage();
			return $tr;
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
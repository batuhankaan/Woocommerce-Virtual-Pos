<?php

class EticSoft_paybyme
{

	var $version = 200710;

	function pay($tr)
	{
		define('AES_256_ECB', 'aes-256-ecb');  
		$payment_url = $tr->gateway_params->test_mode == "off" ? "https://pos.payby.me/webpayment/PayWhiteLabel.aspx" : "https://TESTpos.payby.me/webpayment/PayWhiteLabel.aspx"; 
		$request_url = $tr->gateway_params->test_mode == "off" ? "https://pos.payby.me/webpayment/request.aspx" : "https://TESTpos.payby.me/webpayment/request.aspx";
		$username = $tr->gateway_params->username;
		$token = $tr->gateway_params->token;
		$keywordId = $tr->gateway_params->keywordID;
		$clientIp = $tr->cip;
		$countryCode = "TR";
		$languageCode = "Tr";
		$currencyCode = "TRY";
		$notifyPage = get_site_url()."/wp-content/plugins/sanalpospro/lib/gateways/paybyme/notify.php";  
		$redirectPage = urlencode($tr->ok_url);
		$errorPage = urlencode($tr->fail_url);
		srand(time());
		$syncId = rand();
		$assetName = $tr->shop_name;
		$assetPrice = number_format($tr->total_cart,2,".","")*100;
		$whiteLabel = 1;
		$subCompany = $tr->shop_name;
		$cvc = $tr->cc_cvv;
		$cc_number = $tr->cc_number;
		$cc_month = str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT);
		$cc_year = "20" . $tr->cc_expire_year;
		$installmentCount = $tr->installment; 
		$test_mode = $tr->gateway_params->test_mode;
		$installmentFlag = false; 

		$encryption_key = base64_decode("00MOKIkftkzR5uDY1Mz6XqQtd90ttijoSldSwz3uq1Y=");
		$data = "$cc_number|$cc_month|$cc_year|$cvc";
		$encrypted = openssl_encrypt($data, AES_256_ECB, $encryption_key);
		$wp_version = "wordpress-".bloginfo('version');

		$tr->result_code = '3D-R';
		$tr->result_message = '3D formu oluşturuldu.';
		$tr->result = false; 
		$tr->boid = $syncId;
		$tr->tds = true;
		$tr->save();

		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $request_url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$username&token=$token&keywordId=$keywordId&syncId=$syncId&assetName=$assetName&assetPrice=$assetPrice&clientIp=$clientIp&countryCode=$countryCode&languageCode=$languageCode&currencyCode=$currencyCode&notifyPage=$notifyPage&redirectPage=$redirectPage&errorPage=$errorPage&whiteLabel=$whiteLabel&subCompany=$subCompany&source=$wp_version&affiliateId=4B50EB01-681E-4DB4-A1F7-AC1A00BB58DE");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/x-www-form-urlencoded'
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);
			$hash = $this->get_hash($server_output); 

			if ($installmentCount > 1)
			{
				$installment = $this->getPayByMeInstallment($hash, $cc_number); 
				
				foreach ($installment["WebInstallment"] as $key => $install)
				{
					if ($install["installmentCount"] == $installmentCount)
					{
						$installmentFlag = true;
					}

				}

				if (!$installmentFlag)
				{
					$tr->result_code = '';
					$tr->result_message = 'Seçilen taksit desteklenmiyor !';
					$tr->result = false;
					return $tr;
				}
			}

			$post_url = $this->user_redirect($hash, $encrypted, $installmentFlag, $installmentCount,$test_mode);  

			$form = "";
			$form .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
			$form .= "<html>";
			$form .= "<body>";
			$form .= '<form action="' . $post_url . '" method="post" id="three_d_form"> ';
			$form .= '</form>';
			$form .= "</body>";
			$form .= "<script>document.getElementById(\"three_d_form\").submit();</script>";
			$form .= "</html>"; 
			$tr->tds_echo = $form;


		} catch (Exception $e) {
			$tr->result_code = 'Paybyme-LIB-ERROR';
			$tr->result_message = $e->getMessage();
			$tr->debug($tr->result_code . ' ' . $tr->result_message);
			$tr->result = false;
		} 

		return $tr;

	}

	public function tdValidate($tr)
	{

		if ($_GET["sprtdvalidate"] == "success")
		{
			$tr->result = true;
		}
		else
		{
			$tr->result_message = isset($_GET["_errorDesc_"]) ? urldecode($_GET["_errorDesc_"]) : "";
			$tr->result = false;
			$tr->result_code = isset($_GET["_errorCode_"]) ? urldecode($_GET["_errorCode_"]) : "";
		}
		return $tr;
	}

	public function getPayByMeInstallment($hash, $cc_number)
	{

		$bin_number = substr($cc_number, 0, 6);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://pos.payby.me/WebPayment/Functions?action=webinstallment&hash=$hash&cardno=$bin_number&affiliateId=4B50EB01-681E-4DB4-A1F7-AC1A00BB58DE");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);

		if (curl_errno($ch)) die("Paybyme BIN detail request timeout. err:" . curl_error($ch));

		curl_close($ch);

		$response = json_decode($result, 1);
		return $response;

	}

	public function get_hash($result)
	{
		parse_str($result, $params);
		if (!is_null($params) && !is_null($params['ErrorCode'] && $params['ErrorCode'] == '1000'))
		{
			$hash = $params['ErrorDesc'];
		}
		else
		{
			(!is_null($params) && !is_null($params['ErrorCode']) ? die($params['ErrorCode']) : die('An Error Occoured!'));
		}
		return $hash;
	}

	public function user_redirect($hash, $encrypted, $installmentFlag, $installmentCount,$test_mode)
	{ 
		$payment_url = $test_mode == "off" ? "https://pos.payby.me/webpayment/PayWhiteLabel.aspx?hash=" : "https://TESTpos.payby.me/webpayment/PayWhiteLabel.aspx?hash=";
		$payment_url = $payment_url . $hash . "&encrypted=" . urlencode($encrypted);
		if ($installmentFlag) $payment_url .= "&installmentCount=$installmentCount";
		$payment_url .= "&affiliateId=4B50EB01-681E-4DB4-A1F7-AC1A00BB58DE";
		return $payment_url;
	}

}


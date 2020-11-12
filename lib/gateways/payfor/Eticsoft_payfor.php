<?php

class Eticsoft_payfor
{
  var $version = 240319;

  public function pay($tr)
  {
    if ($tr->gateway_params->tdmode != 'off'){
        $tr->tds = true;
        return $this->tdForm($tr);
    }
    if ($tr->gateway_params->tdmode == 'off')
        $tr->tds = false;

    $check_payfor = $tr;
    $oid = 'ETCSFT_' .time().'_'. $tr->id_transaction;

    $exmo = str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT);
		$exyr = substr($tr->cc_expire_year, -2);
		$expdate = $exmo.$exyr;
    $inst = (int) $tr->installment <= 1 ? "0" : $tr->installment;
    $request = "".
     "MbrId=".$tr->gateway_params->MbrId."&".                                   //Kurum Kodu
     "MerchantID=".$tr->gateway_params->MerchantID."&".                         //Language_MerchantID
     "UserCode=".$tr->gateway_params->UserCode."&".                             //Kullanici Kodu
     "UserPass=".$tr->gateway_params->UserPass."&".                             //Kullanici Sifre
     "OrderId=".$oid."&".                                                       //Siparis Numarasi
     "SecureType=NonSecure&".                                                   //Language_SecureType
     "TxnType=Auth&".                                                           //Islem Tipi
     "PurchAmount=".$tr->total_pay."&".                                         //Tutar
     "InstallmentCount=".$inst."&".                                             //Taksit Sayisi
     "Currency=". $tr->currency_number ."&".                                    //Para Birimi
     "Pan=".$tr->cc_number."&".                                                 //Kredi Kart Numarasi
     "Expiry=".$expdate."&".                                                    //Son Kullanma Tarihi (MMYY)
     "Cvv2=".$tr->cc_cvv."&".                                                   //Guvenlik Kodu (Cvv)
     "MOTO=0&".                                                                 //Language_MOTO
     "Lang=TR&".                                                                //Language_Lang
    $url = "https://vpos.qnbfinansbank.com/Gateway/Default.aspx";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
    curl_setopt($ch, CURLOPT_SSLVERSION, 6);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);


    if (EticConfig::get("POSPRO_DEBUG_MOD") == 'on') {
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				$verbose = fopen(dirname(__FILE__) . '/../../log/debug.php', 'a+');
				fwrite($verbose, "\n" . date("Y-m-d H:i:s") . ' TR ' . $tr->id_transaction . "\n");
				curl_setopt($ch, CURLOPT_STDERR, $verbose);
		}

    $result = curl_exec($ch); // run the whole process

    $resultkv = explode(";;", $result);
    $sbval = substr($resultkv[46], 10, 6);

    if ($sbval == "Failed") {
      $tr->notify = true;
      $tr->result_code = $resultkv[43];
      $tr->result_message = "Lütfen girmiş olduğunuz bilgileri kontrol ediniz.";
      $tr->debug(" Curl Error \n tr = " . $tr->id_transaction . "\n AC" . $resultkv[43] . ' ' . $result . "\n");
      return $tr;
    }

			if (curl_errno($ch)) {
				$tr->notify = true;
				$tr->result_code = "CURL " . curl_errno($ch)." / ". curl_error($ch);
				$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
				$tr->debug(" Curl Error \n tr = " . $tr->id_transaction . "\n AC" . curl_errno($ch) . curl_error($ch) . ' ' . $result . "\n");
				return $tr;
			}else {
        $tr->result = true;
      }
			curl_close($ch);

    $tr->boid = $tr->id_cart;

    if ($tr->gateway_params->tdmode != 'on') {
        if ($check_payfor->result == '0') {
            $tr->debug('WebServis Hatası ' . $check_payfor->errorMessage);
            $tr->result_code = 'REST-' . $check_payfor->errorCode;
            $tr->result_message = 'WebServis Hatası ' . $check_payfor->errorMessage;
            $tr->result = false;
            return $tr;
        }
    }
      return $tr;
  }


  // // 3D Secure ile Odeme Methodu
  public function tdForm($tr)
  {
      $oid = 'ETCSFT_' .time().'_'. $tr->id_transaction;
      $MbrId= $tr->gateway_params->MbrId;                                       //Kurum Kodu
      $MerchantID= $tr->gateway_params->MerchantID;                             //Language_MerchantID
      $MerchantPass= $tr->gateway_params->MerchantPass;                         //Language_MerchantPass
      $UserCode= $tr->gateway_params->UserCode;                                 //Kullanici Kodu
      $UserPass= $tr->gateway_params->UserPass;                                 //Kullanici Sifre
      $SecureType="3DPay";                                                      //Language_SecureType
      $TxnType="Auth";                                                          //Islem Tipi
      $InstallmentCount= (int) $tr->installment <= 1 ? "0" : $tr->installment;                                      //Taksit Sayisi
      $Currency= $tr->currency_number;                                          //Para Birimi
      $OkUrl= $tr->ok_url;                                                      //Language_OkUrl
      $FailUrl= $tr->fail_url;                                                  //Language_FailUrl
      $OrderId= $oid;                                                           //Siparis Numarasi
      $OrgOrderId= $oid;                                                        //Orijinal Islem Siparis Numarasi
      $PurchAmount= $tr->total_pay;                                             //Tutar
      $Lang="TR";                                                               //Language_Lang
      $exmo = str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT);
      $exyr = substr($tr->cc_expire_year, -2);
      $expdate = $exmo.$exyr;
      $rnd = microtime();
      $hashstr = $MbrId . $OrderId . $PurchAmount . $OkUrl . $FailUrl . $TxnType . $InstallmentCount . $rnd . $MerchantPass;
      $hash = base64_encode(pack('H*',sha1($hashstr)));
      $url = "https://vpos.qnbfinansbank.com/Gateway/Default.aspx";
          $form = '';
          $timestamp = date("Y-m-d H:i:s");
// var_dump($hashstr);
// exit;

          $form .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
          $form .= "<html>";
          $form .= "<body>";
          $form .= "<form action=\"" . $url . "\" method=\"post\" id=\"three_d_form\"/>";
          $form .= "<input type=\"hidden\" name=\"MbrId\" value=\"" . $MbrId . "\">";
          $form .= "<input type=\"hidden\" name=\"MerchantID\" value=\"" . $MerchantID . "\">";
          $form .= "<input type=\"hidden\" name=\"UserCode\" value=\"" . $UserCode . "\">";
          $form .= "<input type=\"hidden\" name=\"UserPass\" value=\"" . $UserPass . "\">";
          $form .= "<input type=\"hidden\" name=\"SecureType\" value=\"" . $SecureType . "\">";
          $form .= "<input type=\"hidden\" name=\"TxnType\" value=\"" . $TxnType . "\">";
          $form .= "<input type=\"hidden\" name=\"InstallmentCount\" value=\"" . $InstallmentCount . "\">";
          $form .= "<input type=\"hidden\" name=\"Currency\" value=\"" . $Currency . "\">";
          $form .= "<input type=\"hidden\" name=\"OkUrl\" value=\"" . $OkUrl . "\">";
          $form .= "<input type=\"hidden\" name=\"FailUrl\" value=\"" . $FailUrl . "\">";
          $form .= "<input type=\"hidden\" name=\"OrderId\" value=\"" . $OrderId . "\">";
          $form .= "<input type=\"hidden\" name=\"PurchAmount\" value=\"" . $PurchAmount . "\">";
          $form .= "<input type=\"hidden\" name=\"Lang\" value=\"" . $Lang . "\">";
          $form .= "<input type=\"hidden\" name=\"Pan\" value=\"" . $tr->cc_number . "\">";
          $form .= "<input type=\"hidden\" name=\"Cvv2\" value=\"" . $tr->cc_cvv . "\">";
          $form .= "<input type=\"hidden\" name=\"Expiry\" value=\"" . $expdate . "\">";
          $form .= "<input type=\"hidden\" name=\"Rnd\" value=\"" . $rnd . "\">";
          $form .= "<input type=\"hidden\" name=\"Hash\" value=\"" . $hash . "\">";
          $form .= "<input type=\"submit\" value=\"Öde\" style=\"display:none;\"/>";
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
          $form .= "<button class=\"btn btn-success\" type=\"submit\">Ödemeyi Tamamla</button>";
          $form .= "</form>";
          $form .= "</body>";
          $form .= "</html>";
          if (EticConfig::get('POSPRO_ORDER_AUTOFORM') == 'on')
      			$form .= '<script>document.getElementById("three_d_form").submit();</script>';
          $tr->debug("3DS form created. Eticsoft_payfor::tdForm");
          $tr->result = false;
          $tr->tds = true;
      		$tr->tds_echo = $form;
      		return $tr;
  }

  public function TdValidate($tr) {

    $tr->result = false;
    $post_log = "";
		foreach ($_POST as $k => $v)
			$post_log .= $k . ':' . $v . "\n";

    $tr->result_code =  isset($_POST['ProcReturnCode']) ? $_POST['ProcReturnCode'] : '-1';
    $tr->result_message = isset($_POST['ErrMsg']) ? $_POST['ErrMsg'] : '-1';


		if (!isset($_POST['TxnResult'])) {
			$tr->result_code = "-1";
			$tr->result_message = "TxnResult parametresi yok";
			$tr->notify = true;
			$tr->debug("No TxnResult here !");
			$tr->debug("Received POST: " . $post_log);
			return $tr;
		}

		$tr->debug(" 3D POST Received  |\n " . json_encode($_POST) . "\n");

		if ($tr->gateway_params->tdmode == '3D')
			return $this->td($tr);
		return $this->tdPay($tr);
  }
  
  
  public function tdPay($tr){
	$tr->result = false;
    $tr->result_code =  isset($_POST['ProcReturnCode']) ? $_POST['ProcReturnCode'] : '-1';
    $tr->result_message = isset($_POST['ErrMsg']) ? $_POST['ErrMsg'] : '-1';
	
	if(($_POST["3DStatus"] != "1"))
		return $tr;

	if($_POST["ProcReturnCode"] != "00")
		return $tr;
	$tr->result = true;
	return $tr;
  }
  
  public function td($tr){

    if(!isset($_POST["RequestGuid"]))	 {
      $tr->result_code =  '-1';
      $tr->result_message = 'Eksik parametre (RequestGuid)';
      return $tr;
    }
    if(!isset($_POST["Eci"]))	 {
      $tr->result_code =  '-1';
      $tr->result_message = 'Eksik parametre (Eci)';
      return $tr;
    }
    if(!isset($_POST["PayerTxnId"]))	 {
      $tr->result_code =  '-1';
      $tr->result_message = 'Eksik parametre (PayerTxnId)';
      return $tr;
    }
    if(!isset($_POST["PayerAuthenticationCode"]))	 {
      $tr->result_code =  '-1';
      $tr->result_message = 'Eksik parametre (PayerAuthenticationCode)';
      return $tr;
    }
    if($_POST["3DStatus"] != "1")	 {
      $tr->result_code =  isset($_POST['ProcReturnCode']) ? $_POST['ProcReturnCode'] : '-1';
      $tr->result_message = isset($_POST['ErrMsg']) ? $_POST['ErrMsg'] : 'Kullanıcı doğrulaması yapılamadı';
      return $tr;
    }

      $requestGuid = $_POST["RequestGuid"];
      $userCode = $tr->gateway_params->UserCode;
      $userPass = $tr->gateway_params->UserPass;
      $orderidval = $_POST["OrderId"];
      $payersecuritylevelval = $_POST["Eci"];
      $payertxnidval = $_POST["PayerTxnId"];
      $payerauthenticationcodeval = $_POST["PayerAuthenticationCode"];
      $data = "RequestGuid=".$requestGuid."&".
          "OrderId=".$orderidval."&".
          "UserCode=".$userCode."&".
          "UserPass=".$userPass."&".
          "SecureType=3DModelPayment";

      $url = "https://vpos.qnbfinansbank.com/Gateway/Default.aspx";

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,$url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
      curl_setopt($ch, CURLOPT_SSLVERSION, 6);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 90);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      $result = curl_exec($ch);

      if (curl_errno($ch)) {
        $tr->notify = true;
				$tr->result_code = "3DCURL" . curl_errno($ch) . curl_error($ch);
				$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
				$tr->debug(" Curl Error \n tr = " . $tr->id_transaction . "\n AC" . curl_errno($ch) . curl_error($ch) . ' ' . $result . "\n");
				return $tr;
      }
      curl_close($ch);
      $resultValues = explode(";;", $result);

      if(!$resultValues OR !is_array( $resultValues))  {
        $tr->notify = true;
				$tr->result_code = "3DVPARSE";
				$tr->result_message = "Cevap okunamadı";
      }
      $result_array = array();
      foreach($resultValues as $part)
      {
        list($key,$value)= explode("=", $part);
        $result_array[$key] = $value;
      }
      if ($_POST["TxnResult"] == 'Success')
				$tr->result = true;

  		return $tr;

  }

  public function checkHash($tr){
    if (!isset($_POST["ResponseHash"], $_POST["Hash"]))
			return false;
  }
}
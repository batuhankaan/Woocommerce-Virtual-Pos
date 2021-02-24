<?php
define('POSNET_MODULES_DIR', dirname(__FILE__) . '/ykb');
class EticSoft_POSNET
{

	var $version = 210205;

	public function pay($tr)
	{
		if ($tr->gateway_params->tdmode != 'off') {
			$tr->tds = true;
			return $this->tdForm($tr);
		}
		require_once(POSNET_MODULES_DIR . '/PosnetXML/posnet.php');

		$params = $tr->gateway_params;
		if ($tr->test_mode)
			$hostname = 'https://setmpos.ykb.com/PosnetWebService/XML';
		else
			$hostname = 'https://posnet.yapikredi.com.tr/PosnetWebService/XML';

		if($tr->gateway == 'albaraka'){
			if ($tr->test_mode)
				$hostname = 'http://epostest.albarakaturk.com.tr/EPosWebService/XML';
			else
				$hostname = 'https://epos.albarakaturk.com.tr/EPosWebService/XML';
		}

		//print_r(file_get_contents("http://epostest.albarakaturk.com.tr/EPosWebService/XML")); exit;

		$ccno = $tr->cc_number;
		$expdate = $tr->cc_expire_year . str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT) ;
		$cvc = $tr->cc_cvv;
		$amount = (int) ($tr->total_pay * 100);
		$instnumber = $tr->installment;
		$mid = $params->mid;
		$tid = $params->tid;
		$username = $params->usr;
		$password = $params->pas;
		$multpoint = '00';
		$currencycode = $tr->currency_code == 'TRY' ? 'TL' : $tr->currency_code;
		$orderid = substr(str_pad('ETICSOFT_', 24-strlen($tr->id_cart), "0", STR_PAD_RIGHT).$tr->id_cart, 0, 24);
		$tr->boid = $orderid;

		if ((int) $instnumber == 1)
			$instnumber = "00";

		$posnet = new Posnet();
		//$posnet->SetDebugLevel(1);

		$posnet->SetURL($hostname);
		$posnet->SetMid($mid);
		$posnet->SetTid($tid);
		$posnet->SetUsername($username);
		$posnet->SetPassword($password);


		$posnet->DoSaleTran(
                $ccno, $expdate, // Ex : 0703 - Format : YYMM
                $cvc, $orderid, $amount, // Ex : 1500->15.00 YTL
                $currencycode, // Ex : YT
                $instnumber, // Ex : 05
                $multpoint
            );

		$return_xml = $posnet->GetResponseXMLData();
		//var_dump($posnet);
		//exit;
		if (!$xml_cevap = simplexml_load_string($return_xml)) {
			$tr->notify = true;
			$tr->result_code = "APIXMLLOAD";
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
			$tr->debug ("simplexml_load_string error \n tr = " . $tr->id_transaction . "\n Response" . $return_xml . "\n");
			return $tr;
		}

		if ($xml_cevap->respCode == "0100") {
			$tr->notify = true;
			$tr->result_code = "0100";
			$tr->result_message = "Gecici olarak bankaya ulasilamadi. Tekrar deneyiniz.";
			return $tr;
		}

		if ($xml_cevap->approved == "1") {
			$mesaj = 'Ödeme Başarılı';
			$tr->result = true;
			$tr->result_code = $xml_cevap->respCode;
			$tr->result_message = $mesaj;
			return $tr;
		}

		$mesaj = $xml_cevap->respText . " " . $xml_cevap->respCode;
		$sppresponse = 'fail';

		if ($mesaj == "")
			$mesaj = "Banka Onay Vermedi! Bilgilerinizi Gozden Geciriniz ";
		$request_data = str_replace($tr->cc_number, Etictools::maskCcNo($tr->cc_number), $posnet->GetRequestXMLData());
		$request_data = str_replace($tr->cc_cvv, 'XXX', $request_data);
		$tr->result_code = $xml_cevap->respCode;
		$tr->result_message = $xml_cevap->respText;
		$tr->debug("Response" . $return_xml . $request_data);
		return $tr;
	}

	public function setDefines($tr)
	{

		$params = $tr->gateway_params;

		define('MID', $params->mid);
		define('TID', $params->tid);
		define('POSNETID', $params->posnetid);
		define('ENCKEY', $params->enckey);
		define('USERNAME', $params->usr);
		define('PASSWORD', $params->pas);

		if ($tr->test_mode) {
			define('OOS_TDS_SERVICE_URL', 'https://setmpos.ykb.com/3DSWebService/YKBPaymentService');
            //Posnet XML Servisinin web adresi
			define('XML_SERVICE_URL', 'https://setmpos.ykb.com/PosnetWebService/XML');
		} else {
			define('OOS_TDS_SERVICE_URL', 'https://posnet.yapikredi.com.tr/3DSWebService/YKBPaymentService');
			//Posnet XML Servisinin web adresi
			define('XML_SERVICE_URL', 'https://posnet.yapikredi.com.tr/PosnetWebService/XML');
		}


		define('USEMCRYPTLIBRARY', true);
	}

	public function tdForm($tr)
	{
		$this->setDefines($tr);
		require_once(dirname(__FILE__) . '/ykb/PosnetOOS/posnet_oos.php');


        //    $POST;
		if ((floatval(phpversion()) >= 5) && ((ini_get('register_long_arrays') == '0') || (ini_get('register_long_arrays') == ''))) {
			$POST = & $_POST;
		} else {
			$POST = & $HTTP_POST_VARS;
		}
		$posnetOOS = new PosnetOOS;
		$mid = MID;
		$tid = TID;
		$posnetid = POSNETID;
		$xmlServiceURL = XML_SERVICE_URL;
		$xid = substr(str_pad('ETICSOFT_', 20-strlen($tr->id_transaction), "0", STR_PAD_RIGHT).$tr->id_transaction, 0, 20);
		$instnumber = $tr->installment;
		$amount = (int) ($tr->total_pay * 100);
		$currencycode = $tr->currency_code == "TRY" ? "TL" : $tr->currency_code;
		$custName = $tr->id_customer;
		$trantype = 'Sale';
		$return_url = $tr->ok_url;

		if ($tr->gateway_params->tdmode != '3doos') {
			$ccdataisexist = true;
			$ccno = $tr->cc_number;
			$expdate = $tr->cc_expire_year . str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT) ;
			$cvc = $tr->cc_cvv;
		} else
		$ccdataisexist = false;
		$posnetOOS->SetPosnetID($posnetid);
		$posnetOOS->SetMid($mid);
		$posnetOOS->SetTid($tid);
		$posnetOOS->SetURL($xmlServiceURL);
		$posnetOOS->SetUsername(USERNAME);
		$posnetOOS->SetPassword(PASSWORD);
		$posnetOOS->SetKey(ENCKEY);

		if ($ccdataisexist) {
			if (!$posnetOOS->CreateTranRequestDatas($custName, $amount, $currencycode, $instnumber, $xid, $trantype, $ccno, $expdate, $cvc
			)) {
				$tr->debug("PosnetDatalari olusturulamadi");
				$tr->debug("Data1 = " . $posnetOOS->GetData1());
				$tr->debug("Data2 = " . $posnetOOS->GetData2());
				$tr->debug("XML Response Data = " . $posnetOOS->GetResponseXMLData());
				$tr->debug("Error Code : " . $posnetOOS->GetResponseCode());
				$tr->debug("Error Text : " . $posnetOOS->GetResponseText());
				$tr->result_code = $posnetOOS->GetResponseCode();
				$tr->result_message = $posnetOOS->GetResponseText();
				return $tr;
			}
		} else {
            //Kart Bilgilerinin OOS sisteminde girilmesi isteniyor ise
			if (!$posnetOOS->CreateTranRequestDatas($custName, $amount, $currencycode, $instnumber, $xid, $trantype
			)) {
				$tr->debug("PosnetData'lari olusturulamadi");
				$tr->debug("Data1 = " . $posnetOOS->GetData1());
				$tr->debug("Data2 = " . $posnetOOS->GetData2());
				$tr->debug("XML Response Data = " . $posnetOOS->GetResponseXMLData());
				$tr->debug("Error Code : " . $posnetOOS->GetResponseCode());
				$tr->debug("Error Text : " . $posnetOOS->GetResponseText());
				$tr->result_code = $posnetOOS->GetResponseCode();
				$tr->result_message = $posnetOOS->GetResponseText();
				return $tr;
			}
		}

		$data1 = $posnetOOS->GetData1();
		$data2 = $posnetOOS->GetData2();
		$posnetsign = $posnetOOS->GetSign();

		$form = '<form action="'.OOS_TDS_SERVICE_URL.'" method="post" id="three_d_form"/>
		<input name="posnetData" type="hidden" id="posnetData" value="' . $data1 . '">
		<input name="posnetData2" type="hidden" id="posnetData2" value="' . $data2 . '">
		<input name="mid" type="hidden" id="mid" value="' . $mid . '">
		<input name="posnetID" type="hidden" id="posnetID" value="' . $posnetid . '">
		<input name="digest" type="hidden" id="sign" value="' . $posnetsign . '">
		<input name="vftCode" type="hidden" id="vftCode" value="">
		<input name="merchantReturnURL" type="hidden" id="merchantReturnURL" value="' . $return_url . '">
		<input name="lang" type="hidden" id="lang" value="' . $tr->iso_lang . '">
		<input type="hidden" name="currencyCode" value="' . $currencycode . '">
		<input name="url" type="hidden" id="url" value="">
		<input name="openANewWindow" type="hidden" id="openANewWindow" value="0">
		</form>';
        //if (Configuration::get('POSPRO_ORDER_AUTOFORM') == 'on')
		$form .= '<script>document.getElementById("three_d_form").submit();</script>';
		$tr->tds = true;
		$tr->tds_echo = $form;
		$tr->boid = $xid;
		return $tr;
	}

	public function tdValidate($tr)
	{

		$this->setDefines($tr);
		require_once(dirname(__FILE__) . '/ykb/PosnetOOS/posnet_oos.php');

		$POST;
		if ((floatval(phpversion()) >= 5) && ((ini_get('register_long_arrays') == '0') || (ini_get('register_long_arrays') == '')))
		{
			$POST =& $_POST;
		}
		else
		{
			$POST =& $HTTP_POST_VARS;
		}

		$posnetOOS = new PosnetOOS();
          //$posnetOOS->SetDebugLevel(1);

		$merchantPacket = $POST['MerchantPacket'];
		$bankaPacket = $POST['BankPacket'];
		$sign = $POST['Sign'];


		$posnetOOS->SetMid(MID);
		$posnetOOS->SetTid(TID);


        //XML Servisi için
		$posnetOOS->SetURL(XML_SERVICE_URL);
		$posnetOOS->SetUsername($tr->gateway_params->usr);
		$posnetOOS->SetPassword($tr->gateway_params->pas);
		$posnetOOS->SetKey(ENCKEY);
		
		$session = new Session();
		$session->merchantNo = MID;//firman�n merchant numaras�
		$session->terminalNo = TID;// firman�n terminal numaras�
		$session->cardNo =	$tr->cc_number;
		$session->cvvNo  =	$tr->cc_cvv;
		$session->expiredate = $tr->cc_expire_year . str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT) ;
		$session->amount = (int)($tr->total_pay * 100); // normal tutar * 100
		$session->xid = substr(str_pad('ETICSOFT_', 20-strlen($tr->id_transaction), "0", STR_PAD_RIGHT).$tr->id_transaction, 0, 20);// i�lemin id si
		$session->currency = $tr->currency_code == 'TRY' ? 'TL' : $tr->currency_code;
        //print_r($session);
		//print_r($tr);
		// exit;


		$mac = $posnetOOS->getMacFor3DSTransaction($session); 
		$mac = iconv('ISO-8859-1', 'UTF-8//IGNORE', $mac);

		if (array_key_exists("WPAmount", $POST))
			$posnetOOS->SetPointAmount($POST['WPAmount']);

		$resolved = $posnetOOS->CheckAndResolveMerchantData($merchantPacket, $bankaPacket, $sign, $mac);  
		
		
		if ($tr->gateway_params->test_mode != "off"){
			if ($resolved && ((int) ($posnetOOS->GetTDSMDStatus() == 1 || $posnetOOS->GetTDSMDStatus() == 9)) && $posnetOOS->ConnectAndDoTDSTransaction($merchantPacket, $bankaPacket, $sign, $mac)) {
				$tr->result = true;
				$tr->result_code = $posnetOOS->GetAuthcode();
				$tr->result_message = $posnetOOS->GetApprovedCode();
			} else {
				$tr->result_code = $posnetOOS->GetTDSMDStatus().' '.$posnetOOS->GetAuthcode();
				$tr->result_message = $posnetOOS->GetResponseCode().' '.$posnetOOS->GetResponseText().' '.$posnetOOS->GetLastErrorMessage();
			}
		}else{
			if ($resolved && (int) $posnetOOS->GetTDSMDStatus() == 1 && $posnetOOS->ConnectAndDoTDSTransaction($merchantPacket, $bankaPacket, $sign, $mac)) {
				$tr->result = true;
				$tr->result_code = $posnetOOS->GetAuthcode();
				$tr->result_message = $posnetOOS->GetApprovedCode();
			} else {
				$tr->result_code = $posnetOOS->GetTDSMDStatus().' '.$posnetOOS->GetAuthcode();
				$tr->result_message = $posnetOOS->GetResponseCode().' '.$posnetOOS->GetResponseText().' '.$posnetOOS->GetLastErrorMessage();
			}
		}
		
		return $tr;
	}

	public function hashString($originalString)
	{
		return base64_encode(hash('sha256', $originalString, true));
	}
}
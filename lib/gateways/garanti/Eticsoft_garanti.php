<?php

class EticSoft_garanti
{
var $version = 200819;
/* To-dos 
Check hash */ 

    public function pay($tr)
    {
        if ($tr->gateway_params->tdmode != 'off') {
            $tr->tds = true;
            return $this->tdForm($tr);
        }
		$dateorderid = date("Ymdhis");
        $randorderid = rand(10, 99);
        $tr->boid = 'ETICSOFT' . $dateorderid . $randorderid;
        $tss = $tr->installment;
        if ($tss == "1")
            $tss = "";

        $strMode = "PROD";
        $strVersion = "v0.01";
        $strTerminalID = $tr->gateway_params->tid;
        $strTerminalID_ = str_pad($tr->gateway_params->tid, 9, "0", STR_PAD_LEFT); //TerminalID başına 000 ile 9 digit yapılmalı
        $strProvUserID = $tr->gateway_params->usr;
        $strProvisionPassword = $tr->gateway_params->pas; //SanalPos şifreniz
        $strMerchantID = $tr->gateway_params->mid; //MerchantID (Uye işyeri no)
        $strIPAddress = $tr->cip;
        $strEmailAddress = $tr->customer_email;
        $strOrderID = $tr->boid;
        $strInstallmentCnt = $tss; //Taksit Sayısı. Boş gönderilirse taksit yapılmaz
        $strNumber = $tr->cc_number;
        $strExpireDate = str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT) . substr($tr->cc_expire_year, -2);
        $strCVV2 = $tr->cc_cvv;
        $strAmount = $tr->total_pay * 100; //İşlem Tutarı
        $strType = "sales";
        $strCurrencyCode = $tr->currency_number;
        $strCardholderPresentCode = "0";
        $strMotoInd = "N";

        $strHostAddress = "https://sanalposprov.garanti.com.tr/VPServlet";
        $SecurityData = strtoupper(sha1($strProvisionPassword . $strTerminalID_));
        $HashData = strtoupper(sha1($strOrderID . $strTerminalID . $strNumber . $strAmount . $SecurityData));

        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><GVPSRequest><Mode>$strMode</Mode><Version>$strVersion</Version><Terminal><ProvUserID>PROVAUT</ProvUserID><HashData>$HashData</HashData><UserID>$strProvUserID</UserID><ID>$strTerminalID</ID><MerchantID>$strMerchantID</MerchantID></Terminal><Customer><IPAddress>$strIPAddress</IPAddress><EmailAddress>$strEmailAddress</EmailAddress></Customer><Card><Number>$strNumber</Number><ExpireDate>$strExpireDate</ExpireDate><CVV2>$strCVV2</CVV2></Card><Order><OrderID>$strOrderID</OrderID><GroupID></GroupID><Description></Description></Order><Transaction><Type>$strType</Type><InstallmentCnt>$strInstallmentCnt</InstallmentCnt><Amount>$strAmount</Amount><CurrencyCode>$strCurrencyCode</CurrencyCode><CardholderPresentCode>$strCardholderPresentCode</CardholderPresentCode><MotoInd>$strMotoInd</MotoInd><Description></Description><OriginalRetrefNum></OriginalRetrefNum></Transaction></GVPSRequest>";

 //       $tr->debug('GARANTI_API SENT EXDATE' . $strExpireDate);
        $tr->debug('GARANTI_API SENT XML' . str_replace(array($strNumber, $strCVV2, $strExpireDate), 'XXX', $xml));


        $request = $xml;
        $url = $strHostAddress;
        $ch = curl_init(); // initialize curl handle

        curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // times out after 4s
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request); // add POST fields

        // if (EticConfig::get("POSPRO_DEBUG_MOD") == 'on') {
            // curl_setopt($ch, CURLOPT_VERBOSE, true);
            // $verbose = fopen(dirname(__FILE__) . '/../log/debug.php', 'a+');
            // fwrite($verbose, "\n" . date("Y-m-d H:i:s") . ' TR ' . $tr->id_transaction . "\n");
            // curl_setopt($ch, CURLOPT_STDERR, $verbose);
        // }

        $result = curl_exec($ch); // run the whole process

        if (curl_errno($ch)) { // CURL HATASI
            $tr->notify = true;
            $tr->result = false;
            $tr->result_code = "APICURL" . curl_errno($ch) . curl_error($ch);
            $tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
            $tr->debug('GARANTI_API Curl Error' . curl_errno($ch) . curl_error($ch) . ' ' . $result);
            return $tr;
        }

        if (!$r_xml = simplexml_load_string(EticSoft_garanti::fixEncoding($result))) {
            $tr->notify = true;
            $tr->result = false;
            $tr->result_code = "APIXMLLOAD";
            $tr->result_message = "XML yüklenirken bir sorun meydana geldi.";
            $tr->debug('GARANTI_API simplexml_load_string error Response' . $result);
            return $tr;
        }
        $xml_r = $r_xml->Transaction->Response;

        $ErrMsg = $r_xml->ErrMsg . ' ' . $r_xml->ErrorMsg;
        $ErrMsg .= ' ' . $xml_r->ErrMsg . ' ' . $xml_r->ErrorMsg . ' ' . $xml_r->SysErrMsg;

        if (isset($r_xml->Extra->HOSTMSG))
            $ErrMsg .= $r_xml->Extra->HOSTMSG;

        $tr->result = (string) $xml_r->Code == "00" ? true : false;
        $tr->result_code = $r_xml->ProcReturnCode . '_' . $xml_r->Code;
        $tr->result_message = $ErrMsg;
        $tr->debug("GARANTI_API Everything seems ok => Response " . $result);
        return $tr;
	}
    
	public function tdForm($tr) {
        $strMode = ($tr->test_mode) ? "TEST" : "PROD";
        $strType = "sales";
        $strCurrencyCode = $tr->currency_number;
        $strAmount = (int) ceil((float) $tr->total_pay * 100); //İşlem Tutarı
        $strInstallmentCount = (int) $tr->installment == 1 ? "" : $tr->installment; //Taksit Sayısı. Boş gönderilirse taksit yapılmaz
        $strTerminalUserID = $tr->gateway_params->usr;
        $strOrderID = time() . $tr->id_cart;
        $strCustomeripaddress = $tr->cip;
        $strcustomeremailaddress = $tr->customer_email;
        $strTerminalID = $tr->gateway_params->tid;
        $strTerminalID_ = str_pad($strTerminalID, 9, "0", STR_PAD_LEFT); //Başına 0 eklenerek 9 digite tamamlanmalıdır.
        $strTerminalMerchantID = $tr->gateway_params->mid; //Üye İşyeri Numarası
        $strStoreKey = $tr->gateway_params->sec; //3D Secure şifreniz
        $strProvisionPassword = $tr->gateway_params->pas; //Terminal UserID şifresi
        $strSuccessURL = $tr->test_mode == 'on' ? 'https://eticaret.garanti.com.tr/destek/postback.aspx' : $tr->ok_url;
        $strErrorURL = $tr->test_mode == 'on' ? 'https://eticaret.garanti.com.tr/destek/postback.aspx' : $tr->fail_url;
        $SecurityData = strtoupper(sha1($strProvisionPassword . $strTerminalID_));
        $HashData = strtoupper(sha1($strTerminalID . $strOrderID . $strAmount . $strSuccessURL . $strErrorURL . $strType . $strInstallmentCount . $strStoreKey . $SecurityData));

        $action = "https://sanalposprov.garanti.com.tr/servlet/gt3dengine";
        if ($tr->test_mode == 'on')
            $action = "https://sanalposprovtest.garanti.com.tr/servlet/gt3dengine";


        $dtype = $tr->gateway_params->tdmode;

        $form = ' <form action="'.$action.'" method="post" id="three_d_form">
        <input type="hidden" name="secure3dsecuritylevel" value="' . $dtype . '"/>';
        if ($dtype == '3D' OR $dtype == '3D_PAY' OR $dtype == '3D_FULL' OR $dtype == '3D_HALF')
            $form .= '   
		<input name="cardnumber" value="' . $tr->cc_number . '" type="hidden" />
		<input name="cardexpiredatemonth" value="' . $tr->cc_expire_month . '" type="hidden" />
		<input name="cardexpiredateyear" value="' . $tr->cc_expire_year . '" type="hidden" />
		<input name="cardcvv2" value="' . $tr->cc_cvv . '" type="hidden" />';
        else
            $form .= '
        <input type="hidden" name="companyname" value="' . $tr->shop_name . '" />
        <input type="hidden" name="customeremailaddress" value="' . $strcustomeremailaddress . '" />
        <input type="hidden" name="lang" value="' . $tr->iso_lang . '" />
        <input type="hidden" name="txntimestamp" value="' . time() . '" />';

        $form .= '
        <input type="hidden" name="mode" value="' . $strMode . '" />
        <input type="hidden" name="apiversion" value="v0.01" />
        <input type="hidden" name="terminalprovuserid" value="PROVAUT" />
        <input type="hidden" name="terminaluserid" value="' . $strTerminalUserID . '" />
        <input type="hidden" name="terminalmerchantid" value="' . $strTerminalMerchantID . '" />
        <input type="hidden" name="txntype" value="sales" />
        <input type="hidden" name="txnamount" value="' . $strAmount . '" />
        <input type="hidden" name="txncurrencycode" value="' . $strCurrencyCode . '" />
        <input type="hidden" name="txninstallmentcount" value="' . $strInstallmentCount . '" />
        <input type="hidden" name="orderid" value="' . $strOrderID . '" />
        <input type="hidden" name="terminalid" value="' . $strTerminalID . '" />
        <input type="hidden" name="successurl" value="' . $strSuccessURL . '" />
        <input type="hidden" name="errorurl" value="' . $strErrorURL . '" />
        <input type="hidden" name="customeripaddress" value="' . $strCustomeripaddress . '" />
        <input type="hidden" name="secure3dhash" value="' . $HashData . '" />
		</form>
        ';
		$replace_from = array($tr->cc_number, $tr->cc_cvv);
		$tr->debug('3D form generated'. str_replace($replace_from, "XXX", $form));
        //if (Configuration::get('POSPRO_ORDER_AUTOFORM') == 'on')
            $form .= '<script>document.getElementById("three_d_form").submit();</script>';
		

        $tr->tds = true;
        $tr->tds_echo = $form;
        return $tr;
	}
    public function tdValidate($tr)
    {
        $post_log = "";
        foreach ($_POST as $k => $v)
            $post_log .= $k . ':' . $v . "\n";
        $tr->result = false;
        if (!isset($_POST['mdstatus'])) {
            $tr->result_code = "-1";
            $tr->result_message = "MD MdStatus veya POST parametresi yok";
            $tr->notify = true;
            $tr->debug("No MDStatus here !");
            $tr->debug("Received POST: " . $post_log);
            return $tr;
        }
        if (!$this->checkHash($tr)) {
            $tr->debug("Hash validation failed.");
            $tr->debug("Received POST: " . $post_log);
            $tr->notify = true;
            $tr->result_code = "-1";
            $tr->result_message = "Invalid Hash Signature";
            return $tr;
        }

        $tr->debug(" 3D POST Received  |\n " . json_encode($_POST) . "\n");
        if ($tr->gateway_params->tdmode == '3D')
            return $this->td($tr);
        return $this->tdOos($tr);
    }

    public function tdOos($tr)
    {
        $tr->result_message = "Odeme Onaylanmadı";
        if ($_POST['response'] == "Approved") {
            $tr->result = true;
            $tr->result_message = "Odeme Başarılı";
        }
        $tr->result_code = $_POST['mdstatus'];
        return $tr;
    }

    public function td($tr)
    {
        $strMDStatus = $_POST["mdstatus"];
        $data_string = $this->statusMessage();
        if ($strMDStatus == "1" || $strMDStatus == "2" || $strMDStatus == "3" || $strMDStatus == "4") {
//					$strMode = $_POST['mode'];
//					$strVersion = $_POST['apiversion'];
            $strTerminalID = $_POST['clientid'];
            $strTerminalID_ = str_pad($strTerminalID, 9, "0", STR_PAD_LEFT); //Başına 0 eklenerek 9 digite tamamlanmalıdır.
            $strProvisionPassword = $tr->gateway_params->pas; //Terminal UserID şifresi
            $strProvUserID = $_POST['terminalprovuserid'];
            $strUserID = $_POST['terminaluserid'];
            $strMerchantID = $_POST['terminalmerchantid'];
            $strIPAddress = $_POST['customeripaddress'];
            $strEmailAddress = $_POST['customeremailaddress'];
            $strOrderID = $_POST['orderid'];
//					$strNumber = ""; //Kart bilgilerinin boş gitmesi gerekiyor
//					$strExpireDate = ""; //Kart bilgilerinin boş gitmesi gerekiyor
//					$strCVV2 = ""; //Kart bilgilerinin boş gitmesi gerekiyor
            $strAmount = $_POST['txnamount'];
            $strCurrencyCode = $_POST['txncurrencycode'];
            $strCardholderPresentCode = "13"; //3D Model işlemde bu değer 13 olmalı
            $strType = $_POST['txntype'];
            $strMotoInd = "N";
            $strAuthenticationCode = $_POST['cavv'];
            $strSecurityLevel = $_POST['eci'];
            $strTxnID = $_POST['xid'];
            $strMD = $_POST['md'];
            $SecurityData = strtoupper(sha1($strProvisionPassword . $strTerminalID_));
            $HashData = strtoupper(sha1($strOrderID . $strTerminalID . $strAmount . $SecurityData)); //Daha kısıtlı bilgileri HASH ediyoruz.
            $strHostAddress = "https://sanalposprov.garanti.com.tr/VPServlet"; //Provizyon için xml'in post edileceği adres
            $strInstallmentCount = $_POST['txninstallmentcount'];

//Provizyona Post edilecek XML Şablonu
            $strXML = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><GVPSRequest><Mode>PROD</Mode><Version>v0.01</Version><ChannelCode></ChannelCode><Terminal><ProvUserID>$strProvUserID</ProvUserID><HashData>$HashData</HashData><UserID>$strUserID</UserID><ID>$strTerminalID</ID><MerchantID>$strMerchantID</MerchantID></Terminal><Customer><IPAddress>$strIPAddress</IPAddress><EmailAddress>$strEmailAddress</EmailAddress></Customer><Card><Number></Number><ExpireDate></ExpireDate></Card><Order><OrderID>$strOrderID</OrderID><GroupID></GroupID><Description></Description></Order><Transaction><Type>$strType</Type><InstallmentCnt>$strInstallmentCount</InstallmentCnt><Amount>$strAmount</Amount><CurrencyCode>$strCurrencyCode</CurrencyCode><CardholderPresentCode>$strCardholderPresentCode</CardholderPresentCode><MotoInd>$strMotoInd</MotoInd><Secure3D><AuthenticationCode>$strAuthenticationCode</AuthenticationCode><SecurityLevel>$strSecurityLevel</SecurityLevel><TxnID>$strTxnID</TxnID><Md>$strMD</Md></Secure3D></Transaction></GVPSRequest>";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $strHostAddress);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . $strXML);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            if (EticConfig::get("POSPRO_DEBUG_MOD") == 'on') {
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                $verbose = fopen(dirname(__FILE__) . '/../log/debug.php', 'a+');
                fwrite($verbose, "\n" . date("Y-m-d H:i:s") . ' TR ' . $tr->id_transaction . "\n");
                curl_setopt($ch, CURLOPT_STDERR, $verbose);
            }

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $tr->notify = true;
                $tr->result_code = "3DCURL" . curl_errno($ch) . curl_error($ch);
                $tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
                $tr->debug(" Curl Error \n tr = " . $tr->id_transaction . "\n AC" . curl_errno($ch) . curl_error($ch) . ' ' . $result . "\n");
                return $tr;
            }
            curl_close($ch);

            if ($r_xml = simplexml_load_string($result)) {
                $xml_r = $r_xml->Transaction->Response;
                $tr->result = ((string) $xml_r->Code == "00") ? true : false;
                $tr->result_code = (string) $xml_r->Code;
                $tr->result_message = $xml_r->Message . ' ' . $xml_r->ErrorMsg;
                if (isset($r_xml->Extra->HOSTMSG))
                    $tr->result_message .= $r_xml->Extra->HOSTMSG;
                return $tr;
            }
            $tr->notify = true;
            $tr->result_code = "3DXML";
            $tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
            $tr->debug("\n XML Load Error \n tr = " . $tr->id_transaction . "\n AC" . $result . "\n");
            return $tr;
        }
        $tr->result_code = $_POST['mdstatus'];
        $tr->result_message = $data_string . ' ' . $_POST['mderrormessage'] . $_POST['mdErrorMsg'];
        return $tr;
    }

    private function checkHash($tr)
    {
		return true;
        if (!isset($_POST["hashparams"], $_POST["hashparamsval"], $_POST["hash"]))
            return false;
        $storekey = $tr->gateway_params->sec;  // Isyeri anahtari
        $hashparams = $_POST["hashparams"];
        $hashparamsval = $_POST["hashparamsval"];
        $hashparam = $_POST["hash"];
        $paramsval = "";
        $index1 = 0;
        $index2 = 0;

        while ($index1 < strlen($hashparams)) {
            $index2 = strpos($hashparams, ":", $index1);
            $vl = $_POST[substr($hashparams, $index1, $index2 - $index1)];
            if ($vl == null)
                $vl = "";
            $paramsval = $paramsval . $vl;
            $index1 = $index2 + 1;
        }
        $hashval = $paramsval . $storekey;
        $hash = base64_encode(pack('H*', sha1($hashval)));
        if ($paramsval != $hashparamsval || $hashparam != $hash)
            return false;
        return true;
    }

    public function statusMessage()
    {
        $strMDStatus = $_POST["mdstatus"];
        if ($strMDStatus == "1")
            return "Garanti3D Tam Doğrulama";
        if ($strMDStatus == "2")
            return "Garanti3D Kart Sahibi veya bankası sisteme kayıtlı değil";
        if ($strMDStatus == "3")
            return "Garanti3D Kartın bankası sisteme kayıtlı değil";
        if ($strMDStatus == "4")
            return "Garanti3D Doğrulama denemesi, kart sahibi sisteme daha sonra kayıt olmayı seçmiş";
        if ($strMDStatus == "5")
            return "Garanti3D Doğrulama yapılamıyor";
        if ($strMDStatus == "7")
            return "Garanti3D Sistem Hatası";
        if ($strMDStatus == "8")
            return "Garanti3D Bilinmeyen Kart No";
        if ($strMDStatus == "0")
            return "Garanti3D Doğrulama Başarısız, 3-D Secure imzası geçersiz.";
        return 'Undefined MdStatus';
    }
	
	public static function fixEncoding($in_str){
		$from = array("/Ğ/","/Ü/","/Ş/","/İ/","/Ö/","/Ç/","/ğ/","/ü/","/ş/","/ı/","/ö/","/ç/", '^');
		$to   = array("G","U","S","I","O","C","g","u","s","i","o","c",'');
		$in_str = str_replace($from,$to,$in_str);
		$cur_encoding = mb_detect_encoding($in_str) ;
		if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8"))
			return $in_str;
		return utf8_encode($in_str);
	} 
}

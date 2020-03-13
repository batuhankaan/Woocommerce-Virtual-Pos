<?php

class EticSoft_vpos724
{
var $version = 200919;

    public function pay($tr)
    {

        if ($tr->gateway_params->tdmode != 'off') {
            $tr->tds = true;
            return $this->tdForm($tr);
        }
		
		$prod_url = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx';
		$test_url = 'https://onlineodemetest.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx';
		$params = $tr->gateway_params;

		$PostUrl = $tr->test_mode ? $test_url : $prod_url;
		$IsyeriNo = $params->MerchantId;
		$TerminalNo = $params->TerminalNo;
		$IsyeriSifre = $params->Password;
		$gectar = '20' . $tr->cc_expire_year . $tr->cc_expire_month;
		$KartCvv = $tr->cc_cvv;
		$Tutar = (float)$tr->total_pay;
		$dateorderid = date("Ymdhis");
		$randorderid = rand(10, 99);
		$SiparID = 'ETICSOFT' . $dateorderid . $randorderid;
		$IslemTipi = 'Sale';
		$TutarKodu = $tr->currency_number;
		$KartNo = $tr->cc_number;
		$Taksit = $tr->installment;

		$tr->boid = $SiparID;

		$PosXML = 'prmstr=<?xml version="1.0" encoding="utf-8"?>
		<VposRequest>
		<MerchantId>' . $IsyeriNo . '</MerchantId>
		<Password>' . $IsyeriSifre . '</Password>
		<TerminalNo>' . $TerminalNo . '</TerminalNo>
		<TransactionType>' . $IslemTipi . '</TransactionType>
		<TransactionId>' . $SiparID . '</TransactionId>
		<CurrencyAmount>' . $Tutar . '</CurrencyAmount>
		<CurrencyCode>' . $TutarKodu . '</CurrencyCode>
		<Pan>' . $KartNo . '</Pan>
		<Cvv>' . $KartCvv . '</Cvv>
		<Expiry>' . $gectar . '</Expiry>
		<ClientIp>' .Etictools::getIp(). '</ClientIp>
		<TransactionDeviceSource>0</TransactionDeviceSource>
		<NumberOfInstallments>'.$Taksit.'</NumberOfInstallments>
		</VposRequest>';

		$tr->debug("Sent XML ". str_replace(array($KartNo, $gectar, $KartCvv), 'XXX', $PosXML));
		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $PostUrl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $PosXML);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 59);
		$result = curl_exec($ch);
		
		if (curl_errno($ch)) { // CURL HATASI
			$tr->notify = true;
			$tr->result_code = "APICURL" . curl_errno($ch) . curl_error($ch);
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
			$tr->debug("Curl Error AC " . curl_errno($ch) . curl_error($ch) . ' ' . $result . "\n");
			return $tr;
		}
		curl_close($ch);
		$tr->debug("RESPONSE XML\n" . $result);
		
		if (!$xml_response = simplexml_load_string($result)) {
			$tr->notify = true;
			$tr->result_code = "APIXMLLOAD";
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
			$tr->debug("simplexml_load_string error");
			return $tr;
		}

		$ProcReturnCode = $xml_response->ResultCode;
		$Response = $xml_response->ResultDetail;

		$tr->result = ($ProcReturnCode == "0000") ? true : false;
		$tr->result_code = $ProcReturnCode;
		$tr->result_message = $Response;
		return $tr;
    }

    public function tdForm($tr)
    {
        $mpiServiceUrl = $tr->test_mode ? "https://3dsecuretest.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx" : "https://3dsecure.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx";
		$tr->boid = "ETIC_VPOS".time();
		// print_r($tr);
		// exit;

		$IsyeriNo		= $tr->gateway_params->MerchantId;
		$IsyeriSifre	= $tr->gateway_params->Password;
		$KartNo			= $tr->cc_number;
		$KartYil		= str_pad(substr($tr->cc_expire_year, -2) ,2 ,"0", STR_PAD_LEFT).str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT);
		$KartCvv		= $tr->cc_cvv;
		$SessionInfo	= time();
		$Tutar			= number_format((float)$tr->total_pay,2,'.','');
		$SiparID		= $tr->boid;
		$Taksit			= $tr->installment == 1 ? "" : (int)$tr->installment;
		$kartTipi = substr($tr->cc_number, 0, 1) == '4' ? '100' : '200';
		$paraKodu = $tr->currency_number; //$tr->currency_number;
		$SuccessURL = urlencode($tr->ok_url);
		$FailureURL = urlencode($tr->fail_url);
		$postfields = "&pan=$KartNo&ExpiryDate=$KartYil&PurchaseAmount=$Tutar&Currency=$paraKodu&BrandName=$kartTipi&VerifyEnrollmentRequestId=$SiparID&SessionInfo=$SessionInfo&MerchantId=$IsyeriNo&MerchantPassword=$IsyeriSifre&SuccessURL=$SuccessURL&FailureURL=$FailureURL&InstallmentCount=$Taksit";
		// print_r($postfields);
		// exit;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$mpiServiceUrl);
		curl_setopt($ch,CURLOPT_POST,1);	
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type"=>"application/x-www-form-urlencoded"));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postfields);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);	
		
		
		
		// İşlem isteği MPI'a gönderiliyor
		$resultXml = curl_exec($ch);	
		
        if (curl_errno($ch)) { // CURL HATASI
            $tr->result_code = "APICURL " . curl_errno($ch) . curl_error($ch);
            $tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi";
            $tr->debug("Curl Error AC " . curl_errno($ch) . curl_error($ch) . ' ' . $resultXml);
			return $tr;
		}
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($resultXml, 0, $header_size);
		$body = substr($resultXml, $header_size);

		$replace_from = array($KartNo, $KartCvv);	

		$tr->debug("Request " . str_replace($replace_from, "XXX", $postfields));
		$tr->debug("Response " . $resultXml);
		
		curl_close($ch);		

		if(!$result = $this->sonucuOku2($body) OR !isset($result['Status'])){ // OKUMA HATASI
			$tr->result_code = "APIXMLLOAD";
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
			return $tr;
		}
		
		if($result['Status'] != "Y"){ // ÖDEME HATASI
			$tr->result_code = $result['ErrorCode'];
			$tr->result_message = $result['ErrorMessage'];
			return $tr;
		}
		
		// parse_str($postfields, $fields_sent);
		// print_r("<textarea cols=60 rows=25>".$mpiServiceUrl."</textarea>");
		// print_r("<textarea cols=60 rows=25>".$header."</textarea>");
		// print_r("<textarea cols=60 rows=25>".$body."</textarea>");
		// print_r("<textarea cols=60 rows=25>".print_r($fields_sent, true)."</textarea>");
		// print_r("<textarea cols=60 rows=25>".print_r($this->sonucuOku2($body), true)."</textarea>");
		// exit; 
		
        $return = "<form action=\"" . $result['ACSUrl'] . "\" method=\"post\" id=\"three_d_form\"/>";

        $return .= ' 
        <input type = "hidden" name = "PaReq" value = "' . $result['PaReq'] . '"/>
        <input type = "hidden" name = "TermUrl" value = "' . $result['TermUrl'] . '"/>
        <input type = "hidden" name = "MD" value = "' . $result['MerchantData'] . '"/>
		<button class="btn btn-success" type="submit">Ödemeyi Tamamla</button>
        </form>';
        if (EticConfig::get('POSPRO_ORDER_AUTOFORM') == 'on')
            $return .= '<script>document.getElementById("three_d_form").submit();</script>';
        $tr->debug("3DS form created. EticSoft_VPOS::tdForm");
        $tr->tds = true;
        $tr->tds_echo = $return;
        return $tr;
		
    }
	
	private function sonucuOku2($xml){
		$result = array(
			'Status' => 'N',
			'PaReq' => '',
			'ACSUrl' => '',
			'TermUrl' => 'https://3dsecure.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx',
			'MerchantData' => '',
			'ErrorCode' => '',
			'ErrorMessage' => ''
		);
		if(!$parse = simplexml_load_string($xml))
			return $result;
		$result['Status'] = (string)$parse->Message->VERes->Status;
		$result['PaReq'] = (string)$parse->Message->VERes->PaReq;
		$result['ACSUrl'] = (string)$parse->Message->VERes->ACSUrl;
		$result['TermUrl'] = (string)$parse->Message->VERes->TermUrl;
		$result['MerchantData'] = (string)$parse->Message->VERes->MD;
		$result['ErrorCode'] = (string)$parse->MessageErrorCode;
		$result['ErrorMessage'] = (string)$parse->ErrorMessage;	
		if(!$result['ErrorMessage'] AND $result['ErrorCode'])
			$result['ErrorMessage'] = $this->getErrorMessage($result['ErrorCode']);
		return $result;
	}
	
	private function SonucuOku($result)
	{
		if(!$resultDocument = new DOMDocument())
			return false;
		$resultDocument->loadXML($result);		
		//Status Bilgisi okunuyor
		$statusNode = $resultDocument->getElementsByTagName("Status")->item(0);				
		$status = "";	
		if( $statusNode != null )
			$status = $statusNode->nodeValue;							
		
		//PAReq Bilgisi okunuyor
		$PAReqNode = $resultDocument->getElementsByTagName("PaReq")->item(0);			
		$PaReq = "";
		if( $PAReqNode != null )
			$PaReq = $PAReqNode->nodeValue;
		
		//ACSUrl Bilgisi okunuyor
		$ACSUrlNode = $resultDocument->getElementsByTagName("ACSUrl")->item(0);
		$ACSUrl = "";
		if( $ACSUrlNode != null )
			$ACSUrl = $ACSUrlNode->nodeValue;
			
		//Term Url Bilgisi okunuyor
		$TermUrlNode = $resultDocument->getElementsByTagName("TermUrl")->item(0);
		$TermUrl = "";
		if( $TermUrlNode != null )
			$TermUrl = $TermUrlNode->nodeValue;
		
		//MD Bilgisi okunuyor
		$MDNode = $resultDocument->getElementsByTagName("MD")->item(0);
		$MD = "";
		if( $MDNode != null )
			$MD = $MDNode->nodeValue;
			
		//ErrorCode Bilgisi okunuyor
		$ErrorCodeNode = $resultDocument->getElementsByTagName("MessageErrorCode")->item(0);
		$ErrorCode = "";
		if( $ErrorCodeNode != null )
			$ErrorCode = $ErrorCodeNode->nodeValue;
			
		//ErrorMessage Açıklama Bilgisi okunuyor
		$ErrorMessageNode = $resultDocument->getElementsByTagName("ErrorMessage")->item(0);
		$ErrorMessage = "";
		if( $ErrorMessageNode != null )
			$ErrorMessage = $ErrorMessageNode->nodeValue;

		// Sonuç dizisi oluşturuluyor
		$result = array
		(
			"Status"=>$status,
			"PaReq"=>$PaReq,
			"ACSUrl"=>$ACSUrl,
			"TermUrl"=>$TermUrl,
			"MerchantData"=>$MD,
			"ErrorCode"=>$ErrorCode,
			"ErrorMessage"=>$ErrorMessage,
		);
		return $result;	
	}

    public function tdValidate($tr)
    {
        $post_log = "";
        foreach ($_POST as $k => $v){
			if($k == 'Pan')
				$v = EticTools::maskCcNo($v);
            $post_log .= $k . ':' . $v . "\n";
		}
		
		
        $tr->debug("Received POST: " . $post_log);
			
        $tr->result = false;
        if (!isset($_POST['Status'])) {
            $tr->result_code = "-1";
            $tr->result_message = "MD MdStatus veya POST parametresi yok";
            $tr->notify = true;
            $tr->debug("No MDStatus here !");
            return $tr;
        }
        if ($_POST['Status'] == "N") {
            $tr->result_code = $_POST['ErrorCode'];
            $tr->result_message = $_POST['ErrorMessage'];
            return $tr;
        }
		
		
		$PostUrl = $tr->test_mode ? 'https://onlineodemetest.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx' : 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx';

		$PosXML ='prmstr=<?xml version="1.0" encoding="utf-8"?>
		<VposRequest>
		<MerchantId>'.$tr->gateway_params->MerchantId.'</MerchantId>
		<Password>'.$tr->gateway_params->Password.'</Password>
		<TerminalNo>'.$tr->gateway_params->TerminalNo.'</TerminalNo>
		<TransactionType>Sale</TransactionType>
		<CurrencyAmount>'.number_format($_POST["PurchAmount"]/100, 2, '.', '') .'</CurrencyAmount>
		<CurrencyCode>'.$_POST["PurchCurrency"].'</CurrencyCode>';
		if((int)$_POST["InstallmentCount"] >= 1 )
			$PosXML .= '<NumberOfInstallments>'.(int)$_POST["InstallmentCount"].'</NumberOfInstallments>';
		$PosXML .=  '<Pan>'.$_POST["Pan"].'</Pan>
		<Expiry>20'.$_POST["Expiry"].'</Expiry>
		<CardHoldersName>'.$tr->cc_name.'</CardHoldersName>
		<ECI>'.$_POST["Eci"].'</ECI>
		<CAVV>'.$_POST["Cavv"].'</CAVV> 
		<MpiTransactionId>'.$_POST["VerifyEnrollmentRequestId"].'</MpiTransactionId>
		<ClientIp>'.Etictools::getIp().'</ClientIp>
		<TransactionDeviceSource>0</TransactionDeviceSource>
		</VposRequest>';

		// echo '<h1>Xml formatı </h1>';
		// echo $PostUrl."<br>";
		// echo '<textarea rows="15" cols="60">'.$PosXML.'</textarea>';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$PostUrl);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$PosXML);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);	
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			$response = curl_exec($ch);

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);

		//$replace_from = array($KartNo, $KartCvv);
		
		parse_str($postfields, $fields_sent);
		// print_r("<textarea cols=60 rows=25>".print_r($_POST, true)."</textarea>");
		// print_r("<textarea cols=60 rows=25>".$PostUrl."</textarea>");
		// print_r("<textarea cols=60 rows=25>".$header."</textarea>");
		// print_r("<textarea cols=60 rows=25>".$body."</textarea>");
		// print_r("<textarea cols=60 rows=25>".$PosXML."</textarea>");
		// exit; 
		
	   // echo '<h1>Sonuç değerleri</h1>';
	   // echo '<textarea rows="15" cols="60">'.$response.'</textarea>';
		$tr->debug("POST " . print_r($_POST, true));
		$tr->debug("Request " . str_replace($_POST["Pan"], EticTools::maskCcNo($_POST["Pan"]), $PosXML));
		$tr->debug("Response " . $response);
                                               
        if (curl_errno($ch)) { // CURL HATASI
			$tr->notify = true;
            $tr->result = false;
            $tr->result_code = "APICURL " . curl_errno($ch) . curl_error($ch).' '.$PostUrl;
            $tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi";
            $tr->debug("Curl Error AC " . curl_errno($ch) . curl_error($ch));
			return $tr;
		}
		
		
		curl_close($ch);		

		if(!$result = simplexml_load_string($body)){ // XML OKUMA HATASI
			$tr->notify = true;
			$tr->result = false;
			$tr->result_code = "APIXMLLOAD";
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
            $tr->debug("APIXMLLOAD ERROR ");
			return $tr;
		}
			$tr->result_code = $result->ResultCode;
			$tr->result_message = $result->ResultDetail;
			if ((string)$tr->result_message == null)
				$tr->result_message = $this->getErrorMessage((string)$result->ResultCode);
            $tr->debug("$tr->result_code $tr->result_message");
		if($result->ResultCode != '0'){
			$tr->result = false;
			return $tr;
		}
		$tr->result = true;
		return $tr;

    }

	
	public function getErrorMessage($errorno) {
		$msgs = array('1' => 'Manual onay icin bankayi arayiniz', 
		'2' => 'Sahte onay, Bankanizla teyit ediniz.', 
		'3' => 'Gecersiz isyeri ya da servis saglayici', 
		'4' => 'Calinti Kart', 
		'5' => 'Genel Red', 
		'6' => 'Hata ( sadece dosya guncelleme donus degerlerinde )', 
		'7' => 'Calinti Kart, Ozel durum.', 
		'8' => 'Sahte Onay, fakat VPOS sisteminde kullanilmamalidir, bankanizla teyit ediniz.', 
		'11' => 'Sahte Onay (VIP), fakat VPOS sisteminde kullanilmamalidir, bankanizla teyit ediniz.', 
		'12' => 'Gecersiz Transaction.', 
		'13' => 'Gecersiz Tutar', 
		'14' => 'Gecersiz hesap numarasi', 
		'15' => 'Boyle bir issuer yok', 
		'19' => 'Tekrar girin, tekrar deneyin', 
		'21' => 'Transaction geri alinamiyor', 
		'25' => 'Kayit dosyasi bulunamiyor', 
		'28' => 'Orjinal reddedildi', 
		'29' => 'Orjinal bulunamadi', 
		'30' => 'Format hatasi (switch uretti)', 
		'32' => 'Referral (Genel)', 
		'33' => 'Suresi Gecmis Kart, Karta El Koy', 
		'34' => 'Fraud suphesi, Karta El Koy', 
		'36' => 'Kisitli card, Karta El Koy', 
		'37' => 'Calinti Kart.Issuer kartin iadesini istiyor', 
		'38' => 'Izin verilebilen PIN giris sayisi asildi, Karta El Koy.', 
		'41' => 'Kayip Kart, Karta El Koy', 
		'43' => 'Calinti Kart, Karta El Koy', 
		'51' => 'Limit Yetersiz.', 
		'52' => 'No checking account', 
		'53' => 'No savings account', 
		'54' => 'Kartin Suresi Gecmis', 
		'55' => 'PIN Yanlis', 
		'56' => 'Kart kaydi yok', 
		'57' => 'Kart sahibine acik olmayan islem', 
		'58' => 'Terminale acik olmayan islem', 
		'61' => 'Iptal miktarinin limiti asildi', 
		'62' => 'Sinirli Card', 
		'63' => 'Guvenlik ihlali', 
		'65' => 'Aktivite limit asildi', 
		'75' => 'Izin verilebilir PIN girme sayisi asildi', 
		'76' => 'Anahtar eslestirme hatasi', 
		'77' => 'Uyumsuz veri', 
		'80' => 'Gecersiz Tarih', 
		'81' => 'Sifreleme Hatasi', 
		'82' => 'CVV Hatasi veya girilen CVV gecersiz', 
		'83' => 'PIN dogrulanamiyor', 
		'85' => 'Reddedildi(Genel)', 
		'91' => 'Issuer veya switch islem yapamiyor.', 
		'92' => 'Timeout oldu, Reversal deneniyor', 
		'93' => 'Cakisma, tamamlanamiyor(taksit, sadakat)', 
		'96' => 'System arizasi', 
		'98' => 'Cift Islem Gonderme', 
		'99' => 'Basarisiz Islem.', 
		'1001' => 'Sistem hatasi.', 
		'1006' => 'Bu transactionId ile daha önce basarili bir islem gerçeklestirilmis', 
		'1007' => 'Referans transaction alinamadi', 
		'1044' => 'Debit kartlarla taksitli islem yapilamaz', 
		'1046' => 'Iade isleminde tutar hatali.', 
		'1047' => 'Islem tutari geçersiz.', 
		'1049' => 'Geçersiz tutar.', 
		'1050' => 'CVV hatali.', 
		'1051' => 'Kredi karti numarasi hatali.', 
		'1052' => 'Kredi karti son kullanma tarihi hatali.', 
		'1054' => 'Islem numarasi hatali.', 
		'1059' => 'Yeniden iade denemesi.', 
		'1060' => 'Hatali taksit sayisi.', 
		'1061' => 'Ayni siparis numarasiyla daha önceden basarili islem yapilmis', 
		'1065' => 'Ön provizyon daha önceden kapatilmis', 
		'1073' => 'Terminal üzerinde aktif olarak bir batch bulunamadi', 
		'1074' => 'Islem henüz sonlanmamis yada referans islem henüz tamamlanmamis.', 
		'1075' => 'Sadakat puan tutari hatali', 
		'1076' => 'Sadakat puan kodu hatali', 
		'1077' => 'Para kodu hatali', 
		'1078' => 'Geçersiz siparis numarasi', 
		'1079' => 'Geçersiz siparis açiklamasi', 
		'1080' => 'Sadakat tutari ve para tutari gönderilmemis.', 
		'1081' => 'Maximum puan satışında taksitli işlem gönderilemez', 
		'1082' => 'Geçersiz islem tipi', 
		'1083' => 'Referans islem daha önceden iptal edilmis.', 
		'1084' => 'Geçersiz poas kart numarasi', 
		'1085' => 'Bu poas kart numarasi daha önceden kayit edilmis', 
		'1086' => 'Poas kart numarasiyla eslesen herhangibir kredi karti bulunamadi', 
		'1087' => 'Yabanci para birimiyle taksitli provizyon kapama islemi yapilamaz', 
		'1088' => 'Önprovizyon iptal edilmis', 
		'1089' => 'Referans islem yapilmak istenen islem için uygun degil', 
		'1090' => 'Bölüm numarasi bulunamiyor', 
		'1091' => 'Recurring islemin toplam taksit sayisi hatali', 
		'1092' => 'Recurring islemin tekrarlama araligi hatali', 
		'1093' => 'Sadece Satis (Sale) islemi recurring olarak isaretlenebilir', 
		'1095' => 'Lütfen geçerli bir email adresi giriniz', 
		'1096' => 'Lütfen geçerli bir IP adresi giriniz', 
		'1097' => 'Lütfen geçerli bir CAVV degeri giriniz', 
		'1098' => 'Lütfen geçerli bir ECI degeri giriniz', 
		'1099' => 'Lütfen geçerli bir Kart Sahibi ismi giriniz', 
		'1100' => 'Lütfen geçerli bir brand girisi yapin.', 
		'1101' => 'Referans transaction reverse edilmis.', 
		'1102' => 'Recurring islem araligi geçersiz.', 
		'1103' => 'Taksit sayisi girilmeli', 
		'2011' => 'Uygun Terminal Bulunamadi', 
		'2200' => 'Is yerinin islem için gerekli hakki yok.', 
		'2202' => 'Islem iptal edilemez. ( Batch Kapali )', 
		'5001' => 'Is yeri sifresi yanlis.', 
		'5002' => 'Is yeri aktif degil.', 
		'6000' => 'Merchant IsActive Field Is Invalid', 
		'6001' => 'Merchant ContactAddressLine1 Length Is Invalid', 
		'6002' => 'Merchant ContactAddressLine2 Length Is Invalid', 
		'6003' => 'Merchant ContactCityLength Is Invalid', 
		'6004' => 'Merchant ContactEmail Must Be Valid Email', 
		'6005' => 'Merchant ContactEmail Length Is Invalid', 
		'6006' => 'Merchant ContactName Length Is Invalid', 
		'6007' => 'Merchant ContactPhone Length Is Invalid', 
		'6008' => 'Merchant HostMerchantId Length Is Invalid', 
		'6009' => 'Merchant HostMerchantId Is Empty', 
		'6010' => 'Merchant MerchantName Length Is Invalid', 
		'6011' => 'Merchant Password Length Is Invalid', 
		'6012' => 'TerminalInfo HostTerminalId Is Invalid', 
		'6013' => 'TerminalInfo HostTerminalId Length Is Invalid', 
		'6014' => 'TerminalInfo HostTerminalId Is Empty', 
		'6015' => 'TerminalInfo TerminalName Is Invalid', 
		'6016' => 'Merchant DivisionDescription Is Invalid', 
		'6017' => 'Merchant DivisionNumber Is Invalid', 
		'6018' => 'Merchant Not Found', 
		'6019' => 'InvalidRequest', 
		'6020' => 'Division Is Already Exist', 
		'6021' => 'Division Can Not Be Found', 
		'6022' => 'Transaction Type Exist In Merchant Permission', 
		'6023' => 'Merchant Permission Exist In Merchant', 
		'6024' => 'Currency Code Exist In Merchant Currency Codes Permission', 
		'6025' => 'Terminal Exist In MerchantTerminals', 
		'6026' => 'Terminal Can Not Be Found In MerchantTerminals', 
		'6027' => 'Invalid login attempti. Please check ClientId and ClientPassword fields', 
		'6028' => 'Merchant is already exist. you should try to Update method', 
		'7777' => 'Banka tarafinda gün sonu yapildigindan islem gerçeklestirilemedi', 
		'9000' => 'Host iletişimi esnasında bir hata oluştu', 
		'9001' => 'İşlem Yükleme Limit Aşıldı', 
		'GK' => 'Yurtdisi kredi kart yetkisi kapali', 
		'SF' => 'Hata detayi icin HOSTMSG alanina bakin.', 
		'YK' => 'Kart kara listede');
		if(isset($msgs[$errorno]))
			return $msgs[$errorno];
		return 'Bilinmeyen hata';
	}

}
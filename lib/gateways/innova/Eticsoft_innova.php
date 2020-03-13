<?php
class EticSoft_innova
{
var $version = 171026;
	/*
	* (!) Taksit alanı yok XML'de. 
	*/
    public function pay($tr)
    {
        if ($tr->gateway_params->tdmode == '3D') {
            $tr->tds = true;
            return $this->tdForm($tr);
        }
		$action = $tr->test_mode ? 'http://sanalpos.innova.com.tr/ISBANK/VposWeb/v3/Vposreq.aspx' : 'https://trx.payflex.com.tr/VposWeb/v3/Vposreq.aspx';
		$params = $tr->gateway_params;

		$IsyeriNo     = $params->MerchantId;
		$IsyeriSifre  = $params->MerchantPassword;
		$KartNo       = $tr->cc_number;
		$KartAy       = str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT);
		$KartYil      = "20".str_pad(substr($tr->cc_expire_year, -2) ,2 ,"0", STR_PAD_LEFT);
		$KartCvv      = $tr->cc_cvv;
		$Tutar        = $tr->total_pay;
		$SiparID      = $tr->id_cart;
		$IslemTipi    = 'Sale';
		$TutarKodu    = $tr->currency_number;
		$ClientIp     = $tr->cip;

		$PosXML = 'prmstr=<VposRequest><MerchantId>'.$IsyeriNo.'</MerchantId><Password>'.$IsyeriSifre.'</Password><TransactionType>'.$IslemTipi.'</TransactionType><TransactionId>'.$SiparID.'</TransactionId><CurrencyAmount>'.$Tutar.'</CurrencyAmount><CurrencyCode>'.$TutarKodu.'</CurrencyCode><Pan>'.$KartNo.'</Pan><Cvv>'.$KartCvv.'</Cvv><Expiry>'.$KartYil.$KartAy.'</Expiry><TransactionDeviceSource>0</TransactionDeviceSource><ClientIp>'.$ClientIp.'</ClientIp></VposRequest>';
		$ch = curl_init();
						
		// echo '<h1>Vpos Request</h1>';
		// echo $action."<br>";
		// echo '<textarea rows="15" cols="60">'.$PosXML.'</textarea>';
		// exit;
		curl_setopt($ch, CURLOPT_URL,$action);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$PosXML);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 59);
		//curl_setopt ($ch, CURLOPT_CAINFO, "C:/wamp64/www/cacert.pem");
		
        if (EticConfig::get("POSPRO_DEBUG_MOD") == 'on') {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            $verbose = fopen(dirname(__FILE__) . '/../../log/debug.php', 'a+');
            fwrite($verbose, "\n" . date("Y-m-d H:i:s") . ' TR ' . $tr->id_transaction . "\n");
            curl_setopt($ch, CURLOPT_STDERR, $verbose);
        }

        $result = curl_exec($ch); // run the whole process

        if (curl_errno($ch)) { // CURL HATASI
            $tr->notify = true;
            $tr->result = false;
            $tr->boid = $tr->id_cart;
            $tr->result_code = "APICURL" . curl_errno($ch) . curl_error($ch);
            $tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
            $tr->debug("EticSoft_INNOVA::pay => Curl Error AC " . curl_errno($ch) . curl_error($ch) . ' ' . $result);
			return $tr;
		}
		$replace_from = array($KartNo, $KartCvv);
		$tr->debug("EticSoft_INNOVA::Pay => URL " . $action);
		$tr->debug("EticSoft_INNOVA::Pay => Request " . str_replace($replace_from, "XXX", $PosXML));
		$tr->debug("EticSoft_INNOVA::Pay => Response " . $result);
		
		if ($r_xml = simplexml_load_string($result)) {
			$tr->result = ((string)$r_xml->ResultCode == "0") ? true : false;
			$tr->boid = (string)$r_xml->TransactionId;
			$tr->result_code = (string)$r_xml->ResultCode;
			$tr->result_message = (string)$r_xml->ResultDetail;
			if ($tr->result_message == null)
				$tr->result_message = $this->getErrorMessage((string)$r_xml->ResultCode);
			return $tr;
        }
		// XML parse error 
		$tr->notify = true;
		$tr->result = false;
		$tr->boid = $tr->id_cart;
		$tr->result_code = "APIXMLLOAD";
		$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
		$tr->debug("EticSoft_INNOVA::Run => simplexml_load_string error \n Response " . $result);
		return $tr;
    }

    public function tdForm($tr)
    {
        $mpiServiceUrl = $tr->test_mode ? "http://sanalpos.innova.com.tr/ISBANK/MpiWeb/Enrollment.aspx" : "https://mpi.vpos.isbank.com.tr/Enrollment.aspx";
		$tr->boid = "ETIC_INNO".time();

		$IsyeriNo		= $tr->gateway_params->MerchantId;
		$IsyeriSifre	= $tr->gateway_params->MerchantPassword;
		$KartNo			= $tr->cc_number;
		$KartYil		= str_pad(substr($tr->cc_expire_year, -2) ,2 ,"0", STR_PAD_LEFT).str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT);
		$KartCvv		= $tr->cc_cvv;
		$SessionInfo	= time();
		$Tutar			= (int)$tr->total_pay*100;
		$SiparID		= $tr->boid;
		$Taksit			= $tr->installment == 1 ? "" : (int)$tr->installment;
		$kartTipi = substr($tr->cc_number, 0, 1) == '4' ? '100' : '200';
		$paraKodu = $tr->currency_number;
		$SuccessURL = $tr->ok_url;
		$FailureURL = $tr->fail_url;
		$postfields = "&pan=$KartNo&ExpiryDate=$KartYil&PurchaseAmount=$Tutar&Currency=$paraKodu&BrandName=$kartTipi&VerifyEnrollmentRequestId=$SiparID&SessionInfo=$SessionInfo&MerchantId=$IsyeriNo&MerchantPassword=$IsyeriSifre&SuccessURL=$SuccessURL&FailureURL=$FailureURL&InstallmentCount=$Taksit";
		
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
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($resultXml, 0, $header_size);
		$body = substr($resultXml, $header_size);

		$replace_from = array($KartNo, $KartCvv);	

        if (curl_errno($ch)) { // CURL HATASI
            $tr->result = false;
            $tr->result_code = "APICURL " . curl_errno($ch) . curl_error($ch);
            $tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi";
            $tr->debug("EticSoft_INNOVA::tdForm => Curl Error AC " . curl_errno($ch) . curl_error($ch) . ' ' . $resultXml);
			return $tr;
		}
		$tr->debug("EticSoft_INNOVA::tdForm Request " . str_replace($replace_from, "XXX", $postfields));
		$tr->debug("EticSoft_INNOVA::tdForm Response " . $resultXml);
		
		curl_close($ch);		

		if(!$result = $this->sonucuOku2($body) OR !isset($result['Status'])){ // OKUMA HATASI
			$tr->result = false;
			$tr->result_code = "APIXMLLOAD";
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
			return $tr;
		}
		
		if($result['Status'] != "Y"){ // ÖDEME HATASI
			$tr->result = false;
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
        </form>';
        if (Configuration::get('POSPRO_ORDER_AUTOFORM') == 'on')
            $return .= '<script>document.getElementById("three_d_form").submit();</script>';

        $tr->debug("3DS form created. EticSoft_INNOVA::tdForm");
        $tr->tds = true;
        $tr->tds_echo = $return;
        return $tr;
		
    }
	
	private function sonucuOku2($xml){
		$result = array(
			'Status' => 'N',
			'PaReq' => '',
			'ACSUrl' => '',
			'TermUrl' => 'https://mpi.vpos.isbank.com.tr/MPI_PARes.aspx',
			'MerchantData' => '',
			'ErrorCode' => '',
			'ErrorMessage' => ''
		);
		if(!$parse = simplexml_load_string($xml))
			return $result;
		$result['Status'] = (string)$parse->VERes->Status;
		$result['PaReq'] = (string)$parse->VERes->PAReq;
		$result['ACSUrl'] = (string)$parse->VERes->ACSUrl;
		$result['TermUrl'] = (string)$parse->VERes->TermUrl;
		$result['MerchantData'] = (string)$parse->VERes->MD;
		$result['ErrorCode'] = (string)$parse->ResultDetail->ErrorCode;
		$result['ErrorMessage'] = (string)$parse->ResultDetail->ErrorMessage;	
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
        foreach ($_POST as $k => $v)
            $post_log .= $k . ':' . $v . "\n";
        $tr->result = false;
        if (!isset($_POST['Status'])) {
            $tr->result_code = "-1";
            $tr->result_message = "MD MdStatus veya POST parametresi yok";
            $tr->notify = true;
            $tr->debug("No MDStatus here !");
            $tr->debug("Received POST: " . $post_log);
            return $tr;
        }
        if ($_POST['Status'] == "N") {
            $tr->result_code = $_POST['ErrorCode'];
            $tr->result_message = $_POST['ErrorMessage'];
            return $tr;
        }
		
		
		$PostUrl = $tr->test_mode ? 'https://sanalpos.innova.com.tr/ISBANK/VposWeb/v3/Vposreq.aspx' : 'https://trx.vpos.isbank.com.tr/v3/Vposreq.aspx?';
		$IsyeriNo = $tr->gateway_params->MerchantId;
		$IsyeriSifre = $tr->gateway_params->MerchantPassword;
		$VerifyEnrollmentRequestId = $_POST["VerifyEnrollmentRequestId"];
		$Pan = $_POST["Pan"];
		$sontarih = $_POST["Expiry"];
		$Expiry = "20".$sontarih;
		$tutar = $_POST["PurchAmount"];
		$PurchAmount = $tutar / 100;
		$PurchCurrency = $_POST["PurchCurrency"];
		$InstallmentCount = $_POST["InstallmentCount"];
		$Cavv = $_POST["Cavv"];
		$Eci = $_POST["Eci"];
		$Xid = $_POST["Xid"];

		$PosXML ='prmstr=<?xml version="1.0" encoding="utf-8"?>
		<VposRequest>
		  <MerchantId>'.$IsyeriNo.'</MerchantId>
		  <Password>'.$IsyeriSifre.'</Password>
		  <BankId>1</BankId>
		  <TransactionType>Sale</TransactionType>
		  <TransactionId>'.$VerifyEnrollmentRequestId.'</TransactionId>
		  <CurrencyAmount>'.$PurchAmount.'</CurrencyAmount>
		  <CurrencyCode>'.$PurchCurrency.'</CurrencyCode>
		  <InstallmentCount>2</InstallmentCount>
		  <Pan>'.$Pan.'</Pan>
		  <Cvv>123</Cvv>
		  <Expiry>'.$Expiry.'</Expiry>
		  <Eci>'.$Eci.'</Eci>
		  <Cavv>'.$Cavv.'</Cavv>
		  <Xid>'.$Xid.'</Xid>
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
		
		//parse_str($postfields, $fields_sent);
		// print_r("<textarea cols=60 rows=25>".$PostUrl."</textarea>");
		// print_r("<textarea cols=60 rows=25>".$header."</textarea>");
		// print_r("<textarea cols=60 rows=25>".$body."</textarea>");
		// print_r("<textarea cols=60 rows=25>".$PosXML."</textarea>");
		// print_r("<textarea cols=60 rows=25>".print_r($this->sonucuOku($body), true)."</textarea>");
		// exit; 
		
	   // echo '<h1>Sonuç değerleri</h1>';
	   // echo '<textarea rows="15" cols="60">'.$response.'</textarea>';
		$tr->debug("POST " . print_r($_POST, true));
		$tr->debug("Request " . $PosXML);
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
		'6011' => 'Merchant MerchantPassword Length Is Invalid', 
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

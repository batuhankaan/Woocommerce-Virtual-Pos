<?php

class Eticsoft_Kvtpos 
{

    var $version = 191210;


    public function pay($tr)
    {
        if ($tr->gateway_params->tdmode != 'off') {
			$tr->tds = true;
			return $this->tdForm($tr);
        }

        $name = $tr->gateway_params->usr;
        $password = $tr->gateway_params->pas;
        $merchantid = $tr->gateway_params->mid;
        $clientid = $tr->gateway_params->cid;
        $dateorderid = date("Ymdhis");
        $randorderid = rand(10, 99);
        $oid = $tr->id_cart . '_' . $dateorderid . $randorderid;

        $ccno = $tr->cc_number;
        $exmo = str_pad($tr->cc_expire_month, 2, '0', STR_PAD_LEFT);
        $exyr = $tr->cc_expire_year;
        $taksit = $tr->installment;
        $amount = (int) ((float) $tr->total_pay * 100);
        if ($taksit < 2)
            $taksit = '0';

        $cardtype = substr($ccno, 1) == "4" ? "Visa" : "MasterCard";

        $OkUrl = $tr->ok_url;
        $FailUrl = $tr->fail_url;
        $HashedPassword = base64_encode(sha1($password, "ISO-8859-9"));
        $HashData = base64_encode(sha1($merchantid . $oid . $amount . $OkUrl . $FailUrl . $name . $HashedPassword, "ISO-8859-9"));

        $url = "https://boa.kuveytturk.com.tr/BOA.Integration.WCFService/BOA.Integration.VirtualPos/VirtualPosService.svc/Basic";

        $post_string = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
			   <s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
			   <NonThreeDPayment xmlns="http://boa.net/BOA.Integration.VirtualPos/Service">
			   <message>
			   <APIVersion></APIVersion>
			   <MerchantId>' . $merchantid . '</MerchantId>
			   <HashData>' . $HashData . '</HashData>
			   <TerminalID>VP001169</TerminalID>
			   <OkUrl>' . $OkUrl . '</OkUrl>
			   <FailUrl>' . $FailUrl . '</FailUrl>
			   <UserName>' . $name . '</UserName>
			   <SubMerchantId>0</SubMerchantId>
			   <CustomerId>' . $clientid . '</CustomerId>
			   <CardNumber>' . $ccno . '</CardNumber>
			<CardExpireDateYear>' . $exyr . '</CardExpireDateYear>
			<CardExpireDateMonth>' . $exmo . '</CardExpireDateMonth>
			   <CardCVV2>' . $tr->cc_cvv . '</CardCVV2>
			   <CardType>' . $cardtype . '</CardType>
			   <CardHolderName>' . $tr->cc_name . '</CardHolderName>
			   <BatchID>0</BatchID>
			   <TransactionType>Sale</TransactionType>
			   <InstallmentCount>' . $taksit . '</InstallmentCount>
			   <Amount>' . $amount . '</Amount>
			   <DisplayAmount>' . $amount . '</DisplayAmount>
			   <CurrencyCode>0' . $tr->currency_number . '</CurrencyCode>
			   <MerchantOrderId>' . $oid . '</MerchantOrderId>
			   <ThreeDSecureLevel>0</ThreeDSecureLevel>
			   <FECAmount>0</FECAmount>
			   <QeryId>0</QeryId>
			   <DebtId>0</DebtId>
			   <SurchargeAmount>0</SurchargeAmount>
			   <SGKDebtAmount>0</SGKDebtAmount>
			   <TransactionSecurity>1</TransactionSecurity>
			   <KuveytTurkVPosAdditionalData/>
			   <PaymentId xsi:nil="true"/>
			   </message>
			   </NonThreeDPayment>
			   </s:Body></s:Envelope>';
        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"http://boa.net/BOA.Integration.VirtualPos/Service/IVirtualPosService/NonThreeDPayment\"",
            "Content-length: " . strlen($post_string),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (EticConfig::get("POSPRO_DEBUG_MOD") == 'on') {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            $verbose = fopen(dirname(__FILE__) . '/../../log/debug.php', 'a+');
            fwrite($verbose, "\n" . date("Y-m-d H:i:s") . ' TR ' . $tr->id_transaction . "\n");
            curl_setopt($ch, CURLOPT_STDERR, $verbose);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) { // CURL HATASI
            $tr->notify = true;
            $tr->result = false;
            $tr->boid = $tr->id_cart;
            $tr->result_code = "APICURL" . curl_errno($ch) . curl_error($ch);
            $tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
            $tr->debug("::pay => Curl Error AC " . curl_errno($ch) . curl_error($ch) . ' ' . $response);
            return $tr;
        }

        $ErrMsg = EticTools::getTagValue("ResponseMessage", $response);
        if (!$ErrMsg OR (string) $ErrMsg == "")
            $ErrMsg = EticTools::getTagValue("ErrorMessage", $response);

        $tr->result_code = EticTools::getTagValue("ResponseCode", $response);
        $tr->result_message = $ErrMsg;
        $tr->result = ($tr->result_code == "00") ? true : false;
        return $tr;
    }

    public function tdForm($tr)
    {
        $APIVersion = "1.0.0";
        $Type = "Sale";    
		$expire_month = str_pad($tr->cc_expire_month, 2, '0', STR_PAD_LEFT);
        $CurrencyCode = "0".$tr->currency_number; //TL islemleri için
        $MerchantOrderId = "ETICSOFT_".$tr->id_cart;// Siparis Numarasi
		$Amount = (int) ((float) $tr->total_pay * 100); //Islem Tutari // örnegin 1.00TL için 100 kati yani 100 yazilmali
        $CustomerId = $tr->gateway_params->cid;//Müsteri Numarasi
        $MerchantId = $tr->gateway_params->mid;; //Magaza Kodu
        $OkUrl = $tr->ok_url; //Basarili sonuç alinirsa, yönledirelecek sayfa
        $FailUrl = $tr->fail_url;//Basarisiz sonuç alinirsa, yönledirelecek sayfa
        $UserName= $tr->gateway_params->usr; // Web Yönetim ekranalrindan olusturulan api rollü kullanici
		$Password= $tr->gateway_params->pas;// Web Yönetim ekranalrindan olusturulan api rollü kullanici sifresi
		$HashedPassword = base64_encode(sha1($Password,"ISO-8859-9")); //md5($Password);	
        $HashData = base64_encode(sha1($MerchantId.$MerchantOrderId.$Amount.$OkUrl.$FailUrl.$UserName.$HashedPassword , "ISO-8859-9"));
		$TransactionSecurity=3;
		$xml= '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">'
				.'<APIVersion>1.0.0</APIVersion>'
				.'<OkUrl><![CDATA['.$OkUrl.']]></OkUrl>'
				.'<FailUrl><![CDATA['.$FailUrl.']]></FailUrl>'
				.'<HashData>'.$HashData.'</HashData>'
				.'<MerchantId>'.$MerchantId.'</MerchantId>'
				.'<CustomerId>'.$CustomerId.'</CustomerId>'
				.'<UserName>'.$UserName.'</UserName>'
				.'<CardNumber>'.$tr->cc_number.'</CardNumber>'
				.'<CardExpireDateYear>'.$tr->cc_expire_year.'</CardExpireDateYear>'
				.'<CardExpireDateMonth>'.$expire_month.'</CardExpireDateMonth>'
				.'<CardCVV2>'.$tr->cc_cvv.'</CardCVV2>'
				.'<CardHolderName>'.$tr->cc_name.'</CardHolderName>'
				.'<CardType>Visa</CardType>'
				.'<BatchID>0</BatchID>'
				.'<TransactionType>'.$Type.'</TransactionType>'
				.'<InstallmentCount>0</InstallmentCount>'
				.'<Amount>'.$Amount.'</Amount>'
				.'<DisplayAmount>'.$Amount.'</DisplayAmount>'
				.'<CurrencyCode>'.$CurrencyCode.'</CurrencyCode>'
				.'<MerchantOrderId>'.$MerchantOrderId.'</MerchantOrderId>'
				.'<TransactionSecurity>3</TransactionSecurity>'
				.'<TransactionSide>Sale</TransactionSide>'
				.'</KuveytTurkVPosMessage>';
				
		$tr->debug("tdform sent xml ".base64_encode($xml));
		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: '. strlen($xml)) );
		curl_setopt($ch, CURLOPT_POST, true); //POST Metodu kullanarak verileri gönder  
		curl_setopt($ch, CURLOPT_HEADER, false); //Serverdan gelen Header bilgilerini önemseme.  
		curl_setopt($ch, CURLOPT_URL,'https://boa.kuveytturk.com.tr/sanalposservice/Home/ThreeDModelPayGate'); //Baglanacagi URL  
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Transfer sonuçlarini al.

		if (EticConfig::get("POSPRO_DEBUG_MOD") == 'on') {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			$verbose = fopen(dirname(__FILE__) . '/../../log/debug.php', 'a+');
			fwrite($verbose, "\n" . date("Y-m-d H:i:s") . ' TR ' . $tr->id_transaction . "\n");
			curl_setopt($ch, CURLOPT_STDERR, $verbose);
		}

		$response = curl_exec($ch);
		if (curl_errno($ch)) { // CURL HATASI
			$tr->notify = true;
			$tr->result = false;
			$tr->boid = $tr->id_cart;
			$tr->result_code = "APICURL" . curl_errno($ch) . curl_error($ch);
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
			$tr->debug("pay => Curl Error AC " . curl_errno($ch) . curl_error($ch) . ' ' . $response);
			return $tr;
		}
				$tr->debug("tdform reived content ".base64_encode($response)); 

        $tr->tds = true;
        $tr->tds_echo = $response;
        return $tr;
	}

    public function tdValidate($tr)
    {
        $tr->result = false;
		$tr->debug('TDS validate start. POST '.print_r($_POST, true));
		
		$authresponse = urldecode($_POST["AuthenticationResponse"]);
		
		if(!$rxml = simplexml_load_string($authresponse)){
			$tr->notify = true;
			$tr->result_code = "SIMPLEXML_ERROR";
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
			$tr->debug("SIMPLEXML " .base64_encode($authresponse));
			return $tr;			
		}
		$tr->result_code = $rxml->ResponseCode;
		$tr->result_message = $rxml->ResponseMessage;
		if((string)$rxml->ResponseCode != '00')
			return $tr;

	    $MerchantOrderId = $rxml->VPosMessage->MerchantOrderId;
	    $Amount = $rxml->VPosMessage->Amount; //Islem Tutari
	    $MD = $rxml->MD;
		
		$Type = "Sale";
        $CurrencyCode = "0".$tr->currency_number; //TL islemleri için
        $CustomerId = $tr->gateway_params->cid;//Müsteri Numarasi
        $MerchantId = $tr->gateway_params->mid; //Magaza Kodu
        $UserName = $tr->gateway_params->usr; // Web Yönetim ekranalrindan olusturulan api rollü kullanici
		$Password = $tr->gateway_params->pas;// Web Yönetim ekranalrindan olusturulan api rollü kullanici sifresi
		$HashedPassword = base64_encode(sha1($Password,"ISO-8859-9")); //md5($Password);
	    $HashData = base64_encode(sha1($MerchantId.$MerchantOrderId.$Amount.$UserName.$HashedPassword , "ISO-8859-9"));
		$TransactionSecurity=3;
				     
		$xml='<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
		<APIVersion>1.0.0</APIVersion>
		<HashData>'.$HashData.'</HashData>
		<MerchantId>'.$MerchantId.'</MerchantId>
		<CustomerId>'.$CustomerId.'</CustomerId>
		<UserName>'.$UserName.'</UserName>
		<TransactionType>Sale</TransactionType>
		<InstallmentCount>0</InstallmentCount>
		<Amount>'.$Amount.'</Amount>
		<MerchantOrderId>'.$MerchantOrderId.'</MerchantOrderId>
		<TransactionSecurity>3</TransactionSecurity>
		<KuveytTurkVPosAdditionalData>
		<AdditionalData>
			<Key>MD</Key>
			<Data>'.$MD.'</Data>
		</AdditionalData>
		</KuveytTurkVPosAdditionalData>
		</KuveytTurkVPosMessage>';
		
		$tr->debug('TDS XML to sent '.base64_encode($xml));
		
			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: '. strlen($xml)) );
			curl_setopt($ch, CURLOPT_POST, true); //POST Metodu kullanarak verileri gönder  
			curl_setopt($ch, CURLOPT_HEADER, false); //Serverdan gelen Header bilgilerini önemseme.  
			curl_setopt($ch, CURLOPT_URL,'https://boa.kuveytturk.com.tr/sanalposservice/Home/ThreeDModelProvisionGate'); //Baglanacagi URL  
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);	 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Transfer sonuçlarini al.
			
			if (EticConfig::get("POSPRO_DEBUG_MOD") == 'on') {
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				$verbose = fopen(dirname(__FILE__) . '/../../log/debug.php', 'a+');
				fwrite($verbose, "\n" . date("Y-m-d H:i:s") . ' TR ' . $tr->id_transaction . "\n");
				curl_setopt($ch, CURLOPT_STDERR, $verbose);
			}

			$response = curl_exec($ch);

			if (curl_errno($ch)) { // CURL HATASI
				$tr->notify = true;
				$tr->result = false;
				$tr->boid = $tr->id_cart;
				$tr->result_code = "APICURL" . curl_errno($ch) . curl_error($ch);
				$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
				$tr->debug("Curl Error AC " . curl_errno($ch) . curl_error($ch) . ' ' . $response);
				return $tr;
			}
			
			$tr->debug("Response". $response, true);
			curl_close($ch);
			
			if(!$rxml = simplexml_load_string($response)){
				$tr->notify = true;
				$tr->result_code = "SIMPLEXML_ERROR";
				$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
				$tr->debug("SIMPLEXML " .base64_encode($response));
				return $tr;			
			}			
			
			$tr->result_code = $rxml->ResponseCode;
			$tr->result_message = $rxml->ResponseMessage;
			if((string)$rxml->ResponseCode == '00')
				$tr->result = true;
			return $tr;
    }
}

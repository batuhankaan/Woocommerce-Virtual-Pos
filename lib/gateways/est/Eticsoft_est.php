<?php

class EticSoft_est
{

	var $version = 200818;
	
	public function pay($tr)
	{

		if ($tr->gateway_params->tdmode != 'off') {
			$tr->tds = true;
			return $this->tdForm($tr);
		}
		$params = $tr->gateway_params;
		$name = $params->usr;
		$password = $params->pas;
		$clientid = $params->cid == "" ? $params->mid : $params->cid;  //Sanal pos magaza numarasi
		$email = $tr->customer_email;   //Email
		$oid = 'ETICSOFT' . $tr->id_cart;
		$type = "Auth";
		$ccno = $tr->cc_number;
		$ccname = $tr->cc_name;
		$exmo = str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT);
		$exyr = substr($tr->cc_expire_year, -2);
		$expdate = $exmo . '/' . $exyr;
		$cv2 = $tr->cc_cvv;
		$tutar = $tr->total_pay;
		$taksit = $tr->installment;
		$lip = $tr->cip;
		$extra = '';
		if ($taksit < 2)
			$taksit = '';
		if (isset($tr->gateway_params->ex1) && isset($tr->gateway_params->va1) && $tr->gateway_params->ex1 != NULL)
			$extra .= '<' . $tr->gateway_params->ex1 . '>' . $tr->gateway_params->va1 . '</' . $tr->gateway_params->ex1 . '>';
		if (isset($tr->gateway_params->ex2) && isset($tr->gateway_params->va2) && $tr->gateway_params->ex2 != NULL)
			$extra .= '<' . $tr->gateway_params->ex2 . '>' . $tr->gateway_params->va2 . '</' . $tr->gateway_params->ex2 . '>';
		
		$burl['akbank'] = "https://www.sanalakpos.com/servlet/cc5ApiServer";
		$burl['finansbank'] = "https://www.fbwebpos.com/fim/api";
		$burl['hsbc'] = "https://vpostest.advantage.com.tr/servlet/cc5ApiServer";
		$burl['isbankasi'] = "https://sanalpos.isbank.com.tr/servlet/cc5ApiServer";
		$burl['garanti'] = "https://ccpos.garanti.com.tr/servlet/cc5ApiServer";
		$burl['halkbank'] = "https://sanalpos.halkbank.com.tr/fim/api";
		$burl['anadolubank'] = "https://anadolusanalpos.est.com.tr/servlet/cc5ApiServer";
		$burl['denizbank'] = "https://spos.denizbank.com/MPI/Est3DGate.aspx";
		$burl['teb'] = "https://sanalpos.teb.com.tr/servlet/cc5ApiServer";
		$burl['fortis'] = "https://fortissanalpos.est.com.tr/servlet/cc5ApiServer";
		$burl['citibank'] = "https://csanalpos.est.com.tr/servlet/cc5ApiServer";
		$burl['kuveytturk'] = "https://netpos.kuveytturk.com.tr/servlet/cc5ApiServer";
		$burl['ingbank'] = "https://sanalpos.ingbank.com.tr/servlet/cc5ApiServer";
		$burl['ziraat'] = "https://sanalpos2.ziraatbank.com.tr/fim/cc5ApiServer";
		$burl['turkiyefinans'] = "https://sanalpos.turkiyefinans.com.tr/fim/api";
		
		$url = $burl[$tr->gateway];

		$request = "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
<CC5Request>
<Name>$name</Name>
<Password>$password</Password>
<ClientId>$clientid</ClientId>
<IPAddress>$lip</IPAddress>
<Email>$email</Email>
<Mode>P</Mode>
<OrderId>$oid</OrderId>
<GroupId></GroupId>
<TransId></TransId>
<UserId></UserId>
<Type>$type</Type>
<Number>{CCNO}</Number>
<Expires>{CCTAR}</Expires>
<Cvv2Val>{CV2}</Cvv2Val>
<Total>$tutar</Total>
<Currency>$tr->currency_number</Currency>
<Taksit>$taksit</Taksit>
<BillTo>
<Name>$ccname</Name>
<Street1></Street1>
<Street2></Street2>
<Street3></Street3>
<City></City>
<StateProv></StateProv>
<PostalCode></PostalCode>
<Country></Country>
<Company></Company>
<TelVoice></TelVoice>
</BillTo>
<ShipTo>
<Name>$ccname</Name>
<Street1></Street1>
<Street2></Street2>
<Street3></Street3>
<City></City>
<StateProv></StateProv>
<PostalCode></PostalCode>
<Country></Country>
</ShipTo>
<Extra>$extra</Extra>
</CC5Request>
";

		$tr->debug("EticSoft_EST_API - Sent XML:" . str_replace(array("\n", "\t"), "", $request));
		$request = str_replace("{CCNO}", $ccno, $request);
		$request = str_replace("{CCTAR}", "$expdate", $request);
		$request = str_replace("{CV2}", "$cv2", $request);


		$ch = curl_init(); // initialize curl handle

		curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
		curl_setopt($ch, CURLOPT_TIMEOUT, 60); // times out after 4s
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request); // add POST fields

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
			$tr->boid = $oid;
			$tr->result_code = "APICURL" . curl_errno($ch) . curl_error($ch);
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
			$tr->debug("EST_API::Run => Curl Error AC " . curl_errno($ch) . curl_error($ch) . ' ' . $result);
		} else {
			if ($r_xml = simplexml_load_string($result)) {
				$tr->result = ($r_xml->ProcReturnCode == "00") ? true : false;
				$tr->boid = $r_xml->OrderId;
				$tr->result_code = $r_xml->ProcReturnCode;
				$tr->result_message = $r_xml->ErrMsg;
				if (isset($r_xml->Extra->HOSTMSG))
					$tr->result_message .= $r_xml->Extra->HOSTMSG;
				$tr->debug("EticSoft_EST_API::Run => Response " . $result);
			}
			else {
				$tr->notify = true;
				$tr->result = false;
				$tr->boid = $oid;
				$tr->result_code = "APIXMLLOAD";
				$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
				$tr->debug("EticSoft_EST_API::Run => simplexml_load_string error \n Response " . $result);
			}
		}
		return $tr;
	}

	public function tdForm($tr)
	{
		$params = $tr->gateway_params;
		$urls = array(
			'akbank' => 'https://www.sanalakpos.com/servlet/est3Dgate',
			'finansbank' => 'https://www.fbwebpos.com/fim/est3Dgate',
			'hsbc' => 'https://www.cpi.hsbc.com/servlet',
			'isbankasi' => 'https://spos.isbank.com.tr/servlet/est3dgate',
			'garanti' => 'https://ccpos.garanti.com.tr/servlet/gar3Dgate',
			'halkbank' => 'https://sanalpos.halkbank.com.tr/fim/est3Dgate',
			'anadolubank' => 'https://anadolusanalpos.est.com.tr/servlet/est3dgate',
			'denizbank' => 'https://spos.denizbank.com/MPI/3DGate.aspx',
			'teb' => 'https://sanalpos.teb.com.tr/servlet/est3Dgate',
			'fortis' => 'https://fortissanalpos.est.com.tr/servlet/est3Dgate',
			'citibank' => 'https://csanalpos.est.com.tr/servlet/est3Dgate',
			'kuveytturk' => 'https://netpos.kuveytturk.com.tr/servlet/est3Dgate',
			'ingbank' => 'https://sanalpos.ingbank.com.tr/fim/est3Dgate',
			'ziraat' => 'https://sanalpos2.ziraatbank.com.tr/fim/est3Dgate',
			'turkiyefinans' => 'https://sanalpos.turkiyefinans.com.tr/fim/est3Dgate'
		);
		
		if ($tr->gateway_params->tdmode == '3D_PAY')
			$storetype = "3d_pay"; // Store Tipi
		else
			$storetype = "3d";   // Store Tipi

		$clientId = $params->cid;  // Magazaya Isyeri numarasi
		if (!$clientId OR $clientId == "")
			$clientId = $params->mid;

		$storekey = $params->key;  // Isyeri anahtari
		$islemtipi = "Auth";  // Islem tipi
		$currency = $tr->currency_number;  // TL
		$lang = $tr->iso_lang;   // Dil
		$oid = 'ETICSOFT' . $tr->id_cart;
		$tr->boid = $oid;
		$amount = $tr->total_pay; // Toplam Ücret
		$taksit = (int) $tr->installment <= 1 ? "" : $tr->installment;   // Taksit sayisi
		$okUrl = $tr->ok_url;
		$failUrl = $tr->fail_url;
		$rnd = microtime();

		$action = $tr->test_mode ? "https://entegrasyon.asseco-see.com.tr/fim/est3Dgate" : $urls[$tr->gateway];

		$hashstr = $clientId . $oid . $amount . $okUrl . $failUrl . $islemtipi . $taksit . $rnd . $storekey; //güvenlik amaçli hashli deger
		$hash = base64_encode(pack('H*', sha1($hashstr)));
		
		//print_r($hash);
		//exit;
		
		$return = "<form action=\"" . $action . "\" method=\"post\" id=\"three_d_form\">";
		if ($storetype != '3d_pay_hosting') {
			$return .= '
				<input type="hidden" name="cc_name" value="' . $tr->cc_name . '"/>
                <input type="hidden" name="pan" value="' . $tr->cc_number . '"/>
                <input type="hidden" name="cv2"  value="' . $tr->cc_cvv . '"/>
                <input type="hidden" name="Ecom_Payment_Card_ExpDate_Year"  value="' . substr($tr->cc_expire_year, -2) . '"/>
                <input type="hidden" name="Ecom_Payment_Card_ExpDate_Month"  value="' . str_pad($tr->cc_expire_month, 2, "0", STR_PAD_LEFT) . '"/>
                <input type="hidden" name="cardType"  value="' . (substr($tr->cc_number, 0, 1) == '4' ? '1' : '2' ) . '"/>';
		}
		$return .= ' 
        <input type = "hidden" name = "clientid" value = "' . $clientId . '"/>
        <input type = "hidden" name = "oid" value = "' . $oid . '"/>
        <input type = "hidden" name = "amount" value = "' . $amount . '"/>
        <input type = "hidden" name = "okUrl" value = "' . $okUrl . '">
        <input type = "hidden" name = "failUrl" value = "' . $failUrl . '"/>
        <input type = "hidden" name = "storetype" value = "' . $storetype . '"/>
        <input type = "hidden" name = "rnd" value = "' . $rnd . '"/>
        <input type = "hidden" name = "hash" value = "' . $hash . '"/>
        <input type = "hidden" name = "firmaadi" value = "3D"/>
        <input type = "hidden" name = "islemtipi" value = "Auth"/>
        <input type = "hidden" name = "taksit" value = "' . $taksit . '"/>
        <input type = "hidden" name = "lang" value = "' . $lang . '"/>
        <input type = "hidden" name = "currency" value = "' . $currency . '"/>
        <input type = "hidden" name = "description" value = "' . $clientId . '"/>
        <input type = "hidden" name = "refreshtime" value = "3" >
        <input type = "hidden" name = "BillToName" value = "' . $tr->cc_name . '"/>
        <input type = "hidden" name = "BillToAddress1" value = "0">';
		if($tr->gateway_params->ex1 != "" && $tr->gateway_params->va1)
			$return .= "\n".'<input type = "hidden" name = "'.$tr->gateway_params->ex1.'" value = "'. $tr->gateway_params->va1 .'"/>';
		$return .= '
		</form>';
		
		// print_r ($return);
		// exit;
		
		if (EticConfig::get('POSPRO_ORDER_AUTOFORM') == 'on')
			$return .= '<script>document.getElementById("three_d_form").submit();</script>';
		
		$tr->debug("3DS form created. EticSoft_EST::tdForm");
		$tr->tds = true;
		$tr->tds_echo = $return;
		return $tr;
	}

	public function tdValidate($tr)
	{
		$post_log = "";
		foreach ($_POST as $k => $v)
			$post_log .= $k . ':' . $v . "\n";
		$tr->result = false;
		if (!isset($_POST['mdStatus'])) {
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
		return $this->tdPay($tr);
	}

	public function td($tr)
	{
		$burl = array();
		$burl['akbank'] = "https://www.sanalakpos.com/servlet/cc5ApiServer";
		$burl['finansbank'] = "https://www.fbwebpos.com/fim/est3Dgate";
		$burl['hsbc'] = "https://vpostest.advantage.com.tr/servlet/cc5ApiServer";
		$burl['isbankasi'] = "https://sanalpos.isbank.com.tr/servlet/cc5ApiServer";
		$burl['garanti'] = "https://ccpos.garanti.com.tr/servlet/cc5ApiServer";
		$burl['halkbank'] = "https://sanalpos.halkbank.com.tr/servlet/cc5ApiServer";
		$burl['anadolubank'] = "https://anadolusanalpos.est.com.tr/servlet/cc5ApiServer";
		$burl['denizbank'] = "https://spos.denizbank.com/MPI/3DGate.aspx";
		$burl['teb'] = "https://sanalpos.teb.com.tr/servlet/cc5ApiServer";
		$burl['fortisbank'] = "https://fortissanalpos.est.com.tr/servlet/cc5ApiServer";
		$burl['citibank'] = "https://csanalpos.est.com.tr/servlet/cc5ApiServer";
		$burl['kuveytturk'] = "https://netpos.kuveytturk.com.tr/servlet/cc5ApiServer";
		$burl['ingbank'] = "https://sanalpos.ingbank.com.tr/fim/api";
		$burl['ziraat'] = "https://sanalpos2.ziraatbank.com.tr/fim/cc5ApiServer";
		$burl['turkiyefinans'] = 'https://sanalpos.turkiyefinans.com.tr/fim/est3Dgate';
		
		$mdStatus = (int) $_POST['mdStatus'];
		$params = $tr->gateway_params;

		$extra = "";
		if (isset($tr->gateway_params->ex1) && $tr->gateway_params->ex1 != NULL)
			$extra .= '<' . $tr->gateway_params->ex1 . '>' . $tr->gateway_params->va1 . '</' . $tr->gateway_params->ex1 . '>';
		if (isset($tr->gateway_params->ex2) && $tr->gateway_params->ex2 != NULL)
			$extra .= '<' . $tr->gateway_params->ex2 . '>' . $tr->gateway_params->va2 . '</' . $tr->gateway_params->ex2 . '>';

		if ($mdStatus == 1 || $mdStatus == 2 || $mdStatus == 3 || $mdStatus == 4) {
			// XML request sablonu
			$request = "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>" .
				"<CC5Request>" .
				"<Name>" . $params->usr . "</Name>" .
				"<Password>" . $params->pas . "</Password>" .
				"<ClientId>" . $_POST["clientid"] . "</ClientId>" .
				"<IPAddress>" . $tr->cip . "</IPAddress>" .
				"<Email>" . $tr->customer_email . "</Email><Mode>P</Mode>" .
				"<OrderId>" . $_POST['oid'] . "</OrderId>" .
				"<GroupId></GroupId><TransId></TransId><UserId></UserId><Type>Auth</Type>" .
				"<Number>" . $_POST['md'] . "</Number>" .
				"<Expires></Expires><Cvv2Val></Cvv2Val>" .
				"<Total>" . $_POST["amount"] . "</Total>" .
				"<Currency>" . $tr->currency_number . "</Currency>" .
				"<Taksit>" . $_POST['taksit'] . "</Taksit>" .
				"<PayerTxnId>" . $_POST['xid'] . "</PayerTxnId>" .
				"<PayerSecurityLevel>" . $_POST['eci'] . "</PayerSecurityLevel>" .
				"<PayerAuthenticationCode>" . $_POST['cavv'] . "</PayerAuthenticationCode>" .
				"<CardholderPresentCode>13</CardholderPresentCode>" .
				"<BillTo><Name></Name><Street1></Street1><Street2></Street2><Street3></Street3><City></City><StateProv></StateProv><PostalCode></PostalCode><Country></Country><Company></Company><TelVoice></TelVoice></BillTo>" .
				"<ShipTo><Name></Name><Street1></Street1><Street2></Street2><Street3></Street3><City></City><StateProv></StateProv><PostalCode></PostalCode><Country></Country></ShipTo>"
				. "<Extra>" . $extra . "</Extra>" .
				"</CC5Request>";
				
			$url = $tr->test_mode ? "https://entegrasyon.asseco-see.com.tr/fim/est3Dgate" : $burl[$tr->gateway];

			$ch = curl_init(); // initialize curl handle

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
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
			if (curl_errno($ch)) {
				$tr->notify = true;
				$tr->result_code = "3DCURL" . curl_errno($ch) . curl_error($ch);
				$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
				$tr->debug(" Curl Error \n tr = " . $tr->id_transaction . "\n AC" . curl_errno($ch) . curl_error($ch) . ' ' . $result . "\n");
				return $tr;
			}
			
			curl_close($ch);
			
			if ($return_xml = simplexml_load_string($result)) {
				if ((string) $return_xml->Response == 'Approved')
					$tr->result = true;
				$tr->result_code = (string) $return_xml->ProcReturnCode;
				$tr->result_message = (string) $return_xml->ErrMsg;
				$tr->debug("3D Curl Result  \n " . $result . "\n");
				return $tr;
			}
			$tr->notify = true;
			$tr->result_code = "3DXML";
			$tr->result_message = "Banka ile iletişim sırasında bir sorun meydana geldi.";
			$tr->debug("\n XML Load Error \n tr = " . $tr->id_transaction . "\n AC" . $result . "\n");
			return $tr;
		}
		$tr->result_code = "MD " . $_POST["mdStatus"];
		$tr->result_message = EticTools::getValue("ErrMsg") . ' ' . EticTools::getValue("mdErrorMsg");
		return $tr;
	}

	public function tdPay($tr)
	{
		$mdStatus = (int) $_POST['mdStatus'];
		if ($mdStatus == 1 || $mdStatus == 2 || $mdStatus == 3 || $mdStatus == 4) {
			if ($_POST["Response"] == 'Approved')
				$tr->result = true;
		}
		$tr->result_code = $_POST["mdStatus"];
		$tr->result_message = EticTools::getValue("ErrMsg") . ' ' . EticTools::getValue("mdErrorMsg");

		return $tr;
	}

	private function checkHash($tr)
	{
		if (!isset($_POST["HASHPARAMS"], $_POST["HASHPARAMSVAL"], $_POST["HASH"]))
			return false;
		$params = $tr->gateway_params;
		$storekey = $params->key;  // Isyeri anahtari

		$hashparams = $_POST["HASHPARAMS"];
		$hashparamsval = $_POST["HASHPARAMSVAL"];
		$hashparam = $_POST["HASH"];
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
}
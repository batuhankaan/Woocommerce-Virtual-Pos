<?php
date_default_timezone_set('Europe/Istanbul');

class EticsoftMasterpass
{

    public static $tlv = array(
        'client_id' => array('t' => 'FF01', 'l' => '08'),
        'timezone' => array('t' => 'FF02', 'l' => '01', 'numeric' => true),
        'date_time' => array('t' => 'FF03', 'l' => '14'),
        'msisdn' => array('t' => 'FF04', 'l' => '12'),
        'req_ref_no' => array('t' => 'FF05', 'l' => '17'),
        'user_id' => array('t' => 'FF06', 'l' => '11'),
        'msisdn_validated' => array('t' => 'FF07', 'l' => '01', 'numeric' => true),
        'validation_type' => array('t' => 'FF08', 'l' => '01', 'numeric' => true),
        'merchant_type' => array('t' => 'FF09', 'l' => '01'),
        'vpos_currency_code' => array('t' => 'FF0A', 'l' => '06'),
    );
	
    public $encryption_key;
    public $mac_key;
    public $client_id;
    public $timezone = '+3';
    public $date_time = '';
    public $msisdn; // merchant phone (mobile)
    public $req_ref_no = ''; // must be unique 
    public $user_id; // merchant client id
    public $msisdn_validated = '00'; // merchant client phone validated 
    public $validation_type = '00'; // 00 : NONE, 01 OTP, 02 MPIN, 03 MPIN AND OTP, 04 = SECURE 3D 
    public $vpos_currency_code = '545259'; // hexadecimal ISO currency code
    public $errors = false;
    private $encrypt_method = 'aes-128-cbc';
	public $debug_mode = true;
    public $debug_table = '';
	private $refresh_period = 86400; // 60*60*24 seconds
	public $ui = array();
	public $module_ui;
	public $stage = 'PROD';
	public $client_ip;
	public $backend_urls = array(
        'TEST' => 'https://test.masterpassturkiye.com/',
        'UAT' => 'https://uatmmi.masterpassturkiye.com/',
        'PROD' => 'https://prod.masterpassturkiye.com/'
	);
	public $backend_url;
	
    function __construct($tr = false, $ref = false)
    {
		
        if ($tr) {
			$this->tr = $tr;
            //	$this->vpos_currency_code = $this->strToHex($tr->currency_code);
            $this->req_ref_no = $tr->id_cart . date("His");
			if($ref)
				$this->req_ref_no = $ref;
				
            $this->user_id = $tr->id_customer;
            $this->setMsisdn($tr);
			$this->module_ui = New EticUiWoo();
			if(isset($tr->gateway_params->tdmode))
				$this->validation_type = $tr->gateway_params->tdmode == 'off' ? '00' : '04';
        }			
        $this->stage = EticConfig::get('MASTERPASS_STAGE');
		$this->backend_url = $this->backend_urls[EticConfig::get('MASTERPASS_STAGE')];
        $this->client_id = EticConfig::get('MASTERPASS_CLIENT_ID');
		$this->setKeys();
        $this->user_id = str_pad($this->user_id, 11, '0', STR_PAD_LEFT);
        $this->req_ref_no = str_pad($this->req_ref_no, 17, '0', STR_PAD_LEFT); // for fixed length
        $this->date_time = date("YmdHis");

        $this->timezone = str_replace(array('+', '-'), array('0', '8'), $this->timezone); // ?!?
		$this->client_ip = EticTools::getIp();
        return $this;
    }
	
	public function setKeys(){
		if((int)EticConfig::get('MASTERPASS_LAST_KEYGEN') + $this->refresh_period < time() )
			$this->generateKey();
		$this->encryption_key = EticConfig::get('MASTERPASS_ENC_KEY');
        $this->mac_key = EticConfig::get('MASTERPASS_MAC_KEY');
	}

    public function generateKey()
    {

        $data = array(
            'GenerateKeyRequest' => array(
                'transaction_header' => array(
                    'client_id' => $this->client_id,
                    'request_datetime' => date("Y-m-d") . 'T' . date('H:i:s'),
                    'request_reference_no' => date("Ymd") . '1',
                    'send_sms' => 'Y',
                    'send_sms_language' => 'eng',
                ),
                'transaction_body' => array(
                    'additional_fields' => array()
                )
            )
        );
		if(EticConfig::get('MASTERPASS_STAGE') == 'TEST')
			$url = $this->backend_url.'MMIUIMasterPass_V2/MerchantServices/MPGGenerateKeyService.asmx?wsdl';
		if(EticConfig::get('MASTERPASS_STAGE') == 'UAT')
			$url = $this->backend_url.'MMIUIMasterPass_V2_LB/MerchantServices/MPGGenerateKeyService.asmx?wsdl';
		if(EticConfig::get('MASTERPASS_STAGE') == 'PROD')
			$url = $this->backend_url.'MMIUIMasterPass_V2_LB/MerchantServices/MPGGenerateKeyService.asmx?wsdl';
		
        $options = array(
            'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 15,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
        );
        try {
            $client = new SoapClient($url, $options);
        } catch (Exception $e) {
            die("WSDL Connection Error ".$e->getMessage());
        }

        try {

            $result = $client->GenerateKey($data);
        } catch (Exception $e) {
            die("WSDL Function Error ".$e->getMessage());
        }
        EticConfig::set('MASTERPASS_ENC_KEY', $result->GenerateKeyResult->transaction_body->encryption_key);
        EticConfig::set('MASTERPASS_MAC_KEY', $result->GenerateKeyResult->transaction_body->mac_key);
        EticConfig::set('MASTERPASS_LAST_KEYGEN', time());
    }

	public function void($tr)
    {
        return false;
    }
	
	

    public function generateToken($action = 'checkmp')
    {
        $this->debug_table = '<table class="table" border="1px" >
		<td>Varname</td><td>Org. Value</td><td>Tag</td><td>Len.</td><td>Val.</td></tr><tr>';
        $data = $this->getTlvData($action);
        $this->debug_table .= '</table>';
        if ($mod = (strlen($data) % 32)) {
            $data = $data . '8';
            $pad_zero = (32 - $mod) - 1;
            if ($pad_zero > 0)
                $data = str_pad($data . '0', strlen($data) + $pad_zero, "0", STR_PAD_RIGHT);
        }
        return $this->encryptData($data);
    }
	
	function getTlvData($action){
		if($action == 'linkCardToClient')
			return $this->getTlvData('checkmp');
		if($action == 'setInternal')
			return $this->getTlvData('checkmp');
		if($action == 'purchase')
			return $this->getTlvData('registerpurchase');
		if($action == 'register')
			return $this->getTlvData('registerpurchase');
	
		if($action == 'checkmp'){
			return $this->getTlv('client_id')
			. $this->getTlv('timezone')
			. $this->getTlv('date_time')
			. $this->getTlv('msisdn')
			. $this->getTlv('req_ref_no')
			. $this->getTlv('user_id')
			. $this->getTlv('msisdn_validated')
			. $this->getTlv('validation_type');
		}
		if($action == 'directpurchase'){
			$first = $this->getTlv('client_id')
			. $this->getTlv('timezone')
			. $this->getTlv('date_time')
			. $this->getTlv('req_ref_no')
			. $this->getTlv('user_id')
			. $this->getTlv('msisdn_validated')
			. $this->getTlv('validation_type');
			$gw_tags = EticsoftMasterTools::setMasterParams($this->tr);
			$second = '';
			foreach($gw_tags as $k => $tag){
				$tag['mp_key'] = $k;
				$second .= $this->getCustomTlv($tag);
			}
			return $first.$second;
			
		}
		if($action == 'otp'){
			return $this->getTlv('client_id')
			. $this->getTlv('timezone')
			. $this->getTlv('date_time')
			. $this->getTlv('msisdn')
			. $this->getTlv('req_ref_no')
			. $this->getTlv('user_id')
			. $this->getTlv('msisdn_validated')
			. $this->getTlv('validation_type');			
		}

		if($action == 'registerpurchase'){
			$first = $this->getTlvData('checkmp');
			$gw_tags = EticsoftMasterTools::setMasterParams($this->tr);
			$second = '';
			foreach($gw_tags as $k => $tag){
				$tag['mp_key'] = $k;
				$second .= $this->getCustomTlv($tag);
			}
			return $first.$second;
			//$this->getTagMatch();
		}
			
	}

    private function encryptData($data)
    {
        $data_pack = pack("H*", $data);
        $key = pack("H*", $this->encryption_key);
        $iv = pack("H*", str_pad('0', 32, '0'));
        $enc_data = openssl_encrypt($data_pack, $this->encrypt_method, $key, OPENSSL_RAW_DATA, $iv);
        $enc_data = strtoupper(substr(implode("", unpack("H*", $enc_data)), 0, strlen($data)));
        $mac = strtoupper(hash_hmac('sha1', $enc_data, $this->mac_key));
        return $enc_data . $mac;
    }
	
	public function setbankParams($gateway){
		
	}
	
	
	private function tdsMatch($lib = 'est'){
		$array = array(
			'est' => array(
				'off' => false,
                '3d' => '00',
                '3dpay' => '01',
			),
		);	
	}

    private function decryptData($data)
    {
        $data = substr($data, 0, -40);
        $datao = pack("H*", $data);
        //$data = strtolower($data);
        $key = pack("H*", $this->encryption_key);
        $iv = pack("H*", str_pad('0', 32, '0'));
        echo "<br>Data Wo macd:<br/>" . strtoupper(implode("", unpack("H*", $data))) . "</br>";
        $return = openssl_decrypt($data, $this->encrypt_method, $key, OPENSSL_RAW_DATA, $iv);
        while ($msg = openssl_error_string())
            echo '<br/>' . $msg . "<br />\n";
        return $return;
    }

    private function findbyTagName($tagname)
    {
        foreach (EticsoftMasterpass::$tlv as $k => $tag)
            if ($tag['t'] == $tagname)
                return $k;
        return false;
    }
	

    public function untoken($token)
    {
        die($this->decryptData($token));
    }
	
	/*
	* $array = ('tag', 'value', <optional>'len')
	*/
    private function getCustomTlv($array){
        $tag = $array['mp_tag'];
        $length = $this->getLenHex($array['val']);
        $value = isset($array['is_numeric']) ? $this->dechex($array['val'], $length) : $this->strToHex($array['val']);
		$length = strlen($value)/2;
        $length = str_pad($length, 2, '0', STR_PAD_LEFT);
        $this->debug_table .= '<tr><td>' . $array['mp_key'] . '</td><td>'
                . $array['val'] . '</td><td>' . $tag . '</td><td>' . $length . '</td><td>' . $value . '</td></tr>' . "\n";
        return $tag . $this->dechex($length, 2) . $value;
		
	}

    private function getTlv($str)
    {
        if (!isset(EticsoftMasterpass::$tlv[$str]))
            $this->trowError($str . ' is not in tlv array');
        if (!isset($this->{$str}))
            $this->trowError($str . ' is not a class variable');

        $row = EticsoftMasterpass::$tlv[$str];
        $tag = $row['t'];
        $length = (isset($row['l']) ? $row['l'] : $this->getLenHex($this->{$str}));
        $length = str_pad($length, 2, '0', STR_PAD_LEFT);
        $value = isset($row['numeric']) ? $this->dechex($this->{$str}, $length) : $this->strToHex($this->{$str});
        $this->debug_table .= '<tr><td>' . $this->findbyTagName($tag) . '</td><td>'
                . $this->{$this->findbyTagName($tag)} . '</td><td>' . $tag . '</td><td>' . $length . '</td><td>' . $value . '</td></tr>' . "\n";
        return $tag . $this->dechex($length, 2) . $value;
    }

    public function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }
        return strToUpper($hex);
    }

    public function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i+=2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }

    public function getLenHex($string)
    {
        return $this->dechex(strlen($string), 2);
    }

    private function trowError($msg, $print = true, $die = true)
    {
        $return = '<pre>' . print_r($msg, true) . '</pre>';
        $return .= '<pre>' . print_r(debug_backtrace(), true) . '</pre>';
        if ($print)
            echo $return;
        if ($die)
            die();
        return $return;
    }

    private function dechex($dec, $l = false)
    {
        $hex = strToUpper(dechex($dec));
        if ($l AND $l == 1)
            $l = 2;

        return $l ? str_pad($hex, (int) $l, "0", STR_PAD_LEFT) : $hex;
    }

    public function setMsisdn($tr)
    {
		if(!$tr->customer_mobile OR $tr->customer_mobile == '')
			$tr->customer_mobile = $tr->customer_phone;
        if(isset($tr->customer_mobile) AND EticTools::isMobile(EticTools::formatMobile($tr->customer_mobile))){
            $this->msisdn = EticTools::formatMobile($tr->customer_mobile);
			return true;
		}
		$this->msisdn = '00';
        return '00';
    }

	
	
	public function prepareUi() {
		$this->generateJavascript();
		$this->generateCheckForm();
		$this->generateRegisterForm();
		$this->generateRegisterCheckBox();
		// $this->generateRegisterPurchaseForm();
		$this->generateMpinForm();
		$this->generateOtpForm();
		$this->generateLinkCardtoClientForm();
		$this->generateTosForm();
	}
	
	public function generateCheckForm (){
		return $this->ui['forms']['checkMP'] = '
		<form action="" method="POST" id="eticsoftMP_checkMP" class="mp-check-form">
			<input type="hidden" name="userId" value="'.$this->msisdn.'" />
			<input type="hidden" name="token" value="'.$this->generateToken().'" />
			<input type="hidden" name="referenceNo" value="'.$this->req_ref_no.'" />
			<input type="hidden" name="sendSmsLanguage" value="eng" />
			<input type="hidden" name="sendSms" value="Y" />
		</form>';
	}
	
	public function generateRegisterCheckBox (){
		return $this->ui['forms']['registerMPcheck'] = '
		<div style="display: none;" id="eticsoftMP_registerMPcheck">
			<div id="eticsoftMP_registerMP_checkbox">
				<input type="checkbox" name="eticsoftMP_register" id="eticsoftMP_register" class="checkbox-inline input-md" value="1">
				'.$this->module_ui->l('I accept ')
				.' <a href="#" onclick="emp_showTos()">'.$this->module_ui->l('Masterpass Terms of Use').'</a>'
				.' '. $this->module_ui->l('and would like to save my credit card details')
				.'</label>
				<img class="img-responsive" id="masterpass_logo_inline" src="'.$this->module_ui->uri.'/img/masterpass.svg"/>
			</div>
		</div>';
	}
	
	public function generateRegisterForm(){
		return $this->ui['forms']['registerMPcontainer'] = '
		<div class="row" style="display: none;" id="eticsoftMP_registerMPcontainer">
				<div class="col-sm-12">
				'.$this->module_ui->l('Your credit card details will be saved only your Masterpass account provided by MasterCard').'
				<hr/>
				</div>
				<div class="col-sm-6">'.$this->module_ui->l('Name for your card').'<br>
					<input name="accountAliasName" id="mp_accountAliasName" type="text" value="Kredi Kartım" class="input-xlarge">
				</div>
				<div class="col-sm-6">'.$this->module_ui->l('Your phone number').'<br>
					<input name="accountnewPhone" id="mp_accountnewPhone" type="text" value="'.$this->tr->customer_mobile.'" class="input-xlarge">
				</div>
		</div>';

	}
	
	public function generateListForm(){
		return $this->ui['forms']['cardlist'] = '				
		<form action="" method="POST" id="eticsoftMP_list-form" class="form-horizontal">
			<input name="msisdn" type="hidden" value="'.$this->msisdn.'" />
		</form>';
	}
	public function generateJavascript(){
		$addresses = array(
			'TEST' => 'https://test.masterpassturkiye.com/MasterpassJsonServerHandler/v2',
			'UAT' => 'https://uatui.masterpassturkiye.com/v2',
			'PROD' => 'https://ui.masterpassturkiye.com/v2',
		);
		return $this->ui['forms']['js_init'] = '				
		<script type="text/javascript">
			var emp_msisdn = '.(EticTools::isMobile($this->tr->customer_mobile) ? "true" : "false").';
			var emp_client_id = "'.$this->client_id.'";
			var emp_address = "'.$addresses[EticConfig::get('MASTERPASS_STAGE')].'";
			var mp_debug_mode = '.(EticConfig::get('MASTERPASS_STAGE') == 'PROD' ? 'false' : 'true').';
			var emp_lang = new Array();
			emp_lang["delete_card"] = "'.$this->module_ui->l('Delete Card').'";
			emp_lang["one_shot"] = "'.$this->module_ui->l('One\'s way').'";
			emp_lang["installment"] = "'.$this->module_ui->l('Installment').'";
			emp_lang["installments"] = "'.$this->module_ui->l('Installments').'";
			emp_lang["total"] = "'.$this->module_ui->l('Total').'";
			emp_lang["please_insert_your_phone_for_register"] = "'.$this->module_ui->l('Please insert your mobile phone number for register this credit card').'";
			emp_lang["please_insert_your_card_name"] = "'.$this->module_ui->l('Please insert a name to your credit card').'";
			emp_lang["link_my_account"] = "'.$this->module_ui->l('Link My Account').'";
			emp_lang["type_mp_sms"] = "'.$this->module_ui->l('Please enter the verification code you received via SMS from Masterpass').'";
			emp_lang["type_bank_sms"] = "'.$this->module_ui->l('Please enter the verification code you received via SMS from your Bank').'";
			emp_lang["card_validation"] = "'.$this->module_ui->l('Credit Card Validation').'";
			emp_lang["phone_validation"] = "'.$this->module_ui->l('Phone Validation').'";
			MFS.setClientId("'.$this->client_id.'");
			MFS.setAddress("'.$addresses[EticConfig::get('MASTERPASS_STAGE')].'");
			var mp_ssapi_url = "'.$this->module_ui->uri.'lib/masterpass/jsapi/masterpass.php";
		</script>

		';
	}
	
	public function generateMpinForm () {
		return $this->ui['forms']['mpin'] ='
		<div class="modal fade emp_modal" id="mpinModal" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
			      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">X</span>
				  </button>
				  <img src="'.$this->module_ui->uri.'img/masterpass.svg" class="img-responsive">		  
			  </div>
			  <div class="modal-body">
			  <form action="" method="POST" id="eticsoftMP_mpinform" class="form-horizontal">
					<fieldset>
						<legend>MPIN Validation</legend>
						<div class="control-group">
							<label class="control-label" for="otp">'.$this->module_ui->l('Please Type Your Mpin').'</label>
							<div class="alert alert-warning" id="mpinformerror"></div>
							<div class="controls">
								<input name="validationCode" type="text" placeholder="1234" class="input-xlarge" />
							</div>
						</div>
						<br>
						<div class="control-group">
							<div class="controls">
								<button name="singlebutton" class="btn btn-primary">Send</button>
							</div>
						</div>
					</fieldset>

					<!-- MFS OTP validation operation parameters start -->
					<input type="hidden" name="referenceNo" value="00000000" />
					<input type="hidden" name="sendSms" value="N" />
					<input type="hidden" name="pinType" value="mpin" />
					<input type="hidden" name="sendSmsLanguage" value="tur" />
					<!-- MFS OTP validation operation parameters end -->
				</form>
			</div>
			  <div class="modal-footer">
			  </div>
			</div>
		  </div>
		</div>';
	}
	
	public function generateOtpForm () {
		return $this->ui['forms']['otp'] ='
		<div class="modal fade emp_modal" id="otpModal" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
			      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">X</span>
				  </button>
				  <img src="'.$this->module_ui->uri.'img/masterpass.svg" class="img-responsive">		  
			  </div>
			  <div class="modal-body">
				<form action="" method="POST" id="eticsoftMP_otpform" class="form-horizontal">
					<fieldset>
						<legend id="emp_otp_title">'.$this->module_ui->l('MasterPass SMS Validation').'</legend>
						<div class="control-group">
							<label class="control-label" id="emp_otp_description" for="otp">'.$this->module_ui->l('Type SMS Code').'</label>
							<div id="otpformerror" class=""></div>
							<div class="controls">
								<input name="validationCode" id="otpValidationInput" type="text" placeholder="******" class="form-control input-xlarge lg" />
							</div>
						</div>
						<br>
						<div id="otpCounter">'
						.$this->module_ui->l('Please enter the SMS code in')
						.' <span id="otpCountersec"></span> '.$this->module_ui->l('seconds').'</div>
						<div class="control-group">
							<div class="controls">
								<button name="singlebutton" class="btn btn-primary">'.$this->module_ui->l('Send').'</button>
							</div>
						</div>
					</fieldset>
					<!-- MFS OTP validation operation parameters start -->
					<input type="hidden" name="referenceNo" value="00000000" />
					<input type="hidden" name="sendSms" value="Y" />
					<input type="hidden" name="sendSmsLanguage" value="tur" />
					<input type="hidden" name="pinType" value="otp" />
					<!-- MFS OTP validation operation parameters end -->
				</form>
				</div>
			  <div class="modal-footer">
					<a class="btn btn-warning btn-sm" href="#" id="emp_resendsms">'.$this->module_ui->l(' Resend SMS Code').'</a>
			  </div>
			</div>
		  </div>
		</div>';
	}	
	
	public function generateTosForm () {
		return $this->ui['forms']['tos'] ='
		<div class="modal fade emp_modal" id="emp_tosModal" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
			      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">X</span>
				  </button>
				  <img src="'.$this->module_ui->uri.'img/masterpass.svg" class="img-responsive">		  
			  </div>
			  <div class="modal-body" style="height: 350px;overflow-y: auto;">
				
				</div>
			  <div class="modal-footer">
			  </div>
			</div>
		  </div>
		</div>';
	}

	public function generateLinkAccountForm () {
		return $this->ui['forms']['linkAccount'] ='
		<div class="modal fade emp_modal" id="otpModal" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
			      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">X</span>
				  </button>
				  <img src="'.$this->module_ui->uri.'img/masterpass.svg" class="img-responsive">		  
			  </div>
			  <div class="modal-body">
					<legend>'.$this->module_ui->l('MasterPass Link Your Account').'</legend>
					<div class="control-group">
						<div class="controls">
						<button type="button" onclick="linkAccount()" class="btn btn-medium btn-default >'.$this->module_ui->l('Link My Account').'</button>
						</div>
					</div>
			  </div>
			  <div class="modal-footer">
			  </div>
			</div>
		  </div>
		</div>';
	}
	
	public function generateLinkCardtoClientForm () {
		return $this->ui['forms']['linkCardtoClient'] ='
		<div class="modal fade emp_modal" id="linkCardtoClient" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
			      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">X</span>
				  </button>
				  <img src="'.$this->module_ui->uri.'img/masterpass.svg" class="img-responsive">		  
			  </div>
			  <div class="modal-body">
					<p>'.$this->module_ui->l('Would you like to use a CreditCard on your MasterPass account ?').'</p>
					<div class="control-group">
						<div class="controls">
						<button type="button" onclick="emp_linkAccount()" class="btn btn-medium btn-success">'.$this->module_ui->l('Yes, list my cards').'</button>
						<button type="button" onclick="mp_hidePanels()" class="btn btn-medium btn-danger">'.$this->module_ui->l('No').'</button>
						</div>
					</div>
			  </div>
			  <div class="modal-footer">
			  </div>
			</div>
		  </div>
		</div>';

	}
	
}

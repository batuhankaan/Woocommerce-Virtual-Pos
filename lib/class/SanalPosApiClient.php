<?php

class SanalPosApiClient
{

    protected $source = 'Wordpress';
    protected $vendor = 1;
    protected $api_url = 'https://api.sanalpospro.com/json/';
    protected $login_url = 'https://api.sanalpospro.com/';
    protected $fraud_url = 'https://fraud.sanalpospro.com/';
    private $domain; // merchant domain
    private $email; // merchant store email
    private $public_key; // merchant store public key 
    private $private_key; // merchant store public key 
    private $function;
    private $rand; // random generated integer used in hash
    private $validated = false;
	private $id_program = 1;
	private $version = 2.1;
	
    private $request = array(
        'header' => array(
            'domain' => false,
            'email' => false,
            'public_key' => false,
            'hash' => false,
            'rand' => false,
            'function' => false,
            'echo' => false,
            'mtid' => false,
            'fpid' => false,
            'source' => false,
            'vendor' => false,
            'id_program' => false,
            'version' => false,
        ),
        'data' => array(
            'token' => false,
            'call_back' => false,
        )
    );
    private $response = array(
        'result' => false, // means query successfull
        'result_code' => 01,
        'result_message' => 'Query Could Not Validated Internal',
        'data' => null,
        'rand' => false,
        'hash' => false,
    );
    private $raw_result;
    private $functions = array(
        'test', 'savesettings', 'getsettings', 'savetransaction', 'gettransaction', 'getlist', 'reportissue'
    );
    public $mtid = false; // merchant transaction ID unique for each merchant
    public $hash; // md5 sha1 privatekey+mtid+domain+random
    public $fpid; // fp07 sessionID token
    public $data; // 
    public $request_template;

    public function __construct($mtid, $fpid = 'UNKNOWN', $function = 'saveTransaction')
    {
        if (!$mtid)
            return $this;
        $this->request_template = $this->request;
        $this->rand = rand(1, 999);
        $this->mtid = (int) $mtid; // merchant transaction ID unique for each merchant
        $this->domain = EticConfig::get('POSPRO_API_DOMAIN'); // merchant domain
        $this->email = EticConfig::get('POSPRO_API_EMAIL'); // merchant store email
        $this->public_key = EticConfig::get('POSPRO_API_PUBLIC'); // merchant store public key 
        $this->private_key = EticConfig::get('POSPRO_API_PRIVATE'); // merchant store public key 
        $this->setHash(); // md5 sha1 privatekey+trnxid+domain+random
        $this->echo = rand(1, 99);
        $this->fpid = $fpid; // fp07 sessionID token
        $this->function = $function; // fp07 sessionID token
        return $this;
    }

    private function setRequestHeader($key, $value)
    {
        $this->request['header'][$key] = $value;
        return $this;
    }

    private function setRequestData($key, $value)
    {
        $this->request['data'][$key] = $value;
        return $this;
    }

    private function setRequest($data)
    {
        foreach ($this->request['header'] as $k => $v)
            $this->setRequestHeader($k, $this->{$k});
        foreach ($data as $k => $v)
            $this->setRequestdata($k, $v);
        $this->validated = $this->validateRequest();
        return $this;
    }

    public function run($data = array())
    {
        $this->setRequest($data);
        if (!$this->validated)
            return $this;
        $this->sendRequest()
                ->validateResponse()
                ->setResponse();
        return $this;
    }

    private function setResponse()
    {
        $this->parseResponse();
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    private function validateResponse()
    {
        return $this;
    }

    private function sendRequest()
    {
        $ch = curl_init(); // initialize curl handle
        curl_setopt($ch, CURLOPT_URL, $this->api_url); // set url to post to
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        //  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:application/json"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 4s
        curl_setopt($ch, CURLOPT_POST, true); // set POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('request' => json_encode($this->request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))); // add POST fields

        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->raw_result = $result;
        if (curl_error($ch))
            $this->response['error_message'] .= curl_error($ch) . ' ' . $http_status;
        return $this;
    }

    public function validateRequest()
    {
        foreach ($this->request_template['header'] as $key)
            if (!isset($this->request->header->$key) OR ! $this->request->header->$key) {
                $this->validated = false;
                $this->response['result_message'] = 'Invalid or Null ' . strtoupper($key) . ' value in Request->header';
            }
        return $this;
    }

    private function parseResponse()
    {
        if (!$this->raw_result OR strlen($this->raw_result) < 3
                OR ! $parsed = json_decode(Etictools::removeBOM($this->raw_result))) {
            $this->response['result_message'] = 'Parse error';
            $this->response['debug'] = $this->raw_result;
            return $this;
        }
        $this->response['result'] = true;
        $this->response = $parsed;
    }

    private function setHash()
    {
        $this->hash = md5(sha1($this->private_key . $this->mtid . $this->domain . $this->rand));
    }

    public function getLoginFormValues()
    {
        return array(
            'public_key' => $this->public_key,
            'email' => $this->email,
            'domain' => $this->domain,
            'hash' => $this->hash,
            'rand' => $this->rand,
            'url' => $this->login_url
        );
    }

    public function getRegisterVariables()
    {
        $form_values = array();
        $form_values['firstname'] = EticTools::getValue('spr_shop_firstname');
        $form_values['lastname'] = EticTools::getValue('spr_shop_lastname');
        $form_values['phone'] = EticTools::getValue('spr_shop_phone');
        $form_values['password'] = EticTools::getValue('spr_shop_password');
        $form_values['url'] = site_url();
        $form_values['email'] = get_option('admin_email');
        $form_values['name'] = get_option('blogname');
        $form_values['id_software'] = (int) 3;
        $form_values['version'] = WOOCOMMERCE_VERSION;
        $form_values['key'] = md5('etic' . get_option('blogname') . AUTH_KEY); 
        return $form_values;
    }

    public function register()
    {
        
    }
	
	public function setFraudScore ($id_transaction = false) {
		
	}

	public function getMerchantGateways () {
		$gws = EticSql::getRows('spr_gateway');
		$names = array();
		foreach($gws as $gw)
			$names[]= $gw['name'];
		return $names;
	}
	
	public function getMerchantRates () {
		return EticInstallment::getOrdered();
	}
	
	public function getMerchantDetails () {
		$sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'spr_transaction ORDER BY `date_create` DESC';
		$last = Db::getInstance()->getRow($sql);
		$form_values = array();
        $form_values['url'] = Context::getContext()->shop->getBaseURL(true);
        $form_values['software'] = (int) 1;
        $form_values['version'] = EticConfig::get('PS_INSTALL_VERSION') ? Configuration::get('PS_INSTALL_VERSION') : _PS_VERSION_;
        $form_values['sp_ver'] = (float)$this->version;
        $form_values['php'] = phpversion();
        $form_values['mp'] = EticConfig::get("MASTERPASS_ACTIVE");
        $form_values['last_tr'] = isset($last['date_create']) ? $last['date_create'] : "-";
        return $form_values;

	}
	
	/*
	 * For update settings from older version
	 */
	public function getMerchantOldSettings(){
		$oldversion_bank = EticTools::tableExists('spr_bank');
		var_dump($oldversion_bank);
		exit;
	}

	
}

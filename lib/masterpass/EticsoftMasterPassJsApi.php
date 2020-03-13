<?php

class EticsoftMasterPassJsApi extends EticsoftMasterpass 
{
	public $response = array(
		'error_message' => '',
		'status' => 'fail',
		'data' => array(), 
		'client' => array(),
		'order' => array(),
		'payment' => array(),
		'transaction' => array(),
		'token' => '',
		'clientSideUrl' => '',
		'additionalParameters' => array(),
		'client_ip' => '',
		'client_time' => ''
	);
	
	public $accountaliasname;
	public $clientsideurl = 'https//ui.masterpassturkiye.com/v2';
	public $action;
	private $available_actions = array('registerpurchase', 'purchase', 'directpurchase', 'otp', 'setInternal', 'linkCardToClient');
	private $error_message = false;
	
	
	public function run(){
		$this->response['action'] = $this->action;
		if(!in_array($this->action, $this->available_actions)){
			$this->error_message = 'invalid action';
			$this->setError();	
			return $this->outPut();
		}
		$this->setResponse();
		return $this->outPut();
	}
	
	public function outPut(){
		return json_encode($this->response);
	}
	
	public function setError() {
		$this->response['error_message'] = $this->error_message;
		$this->response['status'] = 'fail';
	}
	
	
	public function setResponse(){
		$this->response['client_ip'] = $this->client_ip;
		$this->response['clientSideUrl'] = $this->clientsideurl;
		$this->setData();
		$this->setClient();
		$this->setOrder();
		$this->setPayment();
		$this->setToken();
		$this->setAdditionalParameters();
		$this->setTransaction();
		if($this->debug_mode)
			$this->response['debug_table'] = str_replace(array("\n", "\t"), '', $this->debug_table);
		$this->response['status'] = 'success';
	}
	
	private function setData(){
		$this->response['data'] = array (
			'clientId' => $this->client_id,
			'referenceNo' => $this->req_ref_no,
			'msisdn' => $this->msisdn,
			'userId' => $this->user_id,
			'sendSms' => 'Y',
			'sendSmsLanguage' => 'tur',
			'timeZone' => '+03'
		);
	}
	
	private function SetClient() {
		$this->response['client'] = array (
			'statu' => 1,
			'stage' => $this->stage,
			'isChecked' => 0,
			'clientId' => $this->client_id,
			'macroMerchantId' => '',
			'mnoId' => 'MNO-702483',
			'programOwnerNumber' => 'TUR-0001',
			'programOwnerName' => 'TURKEY PROGRAM',
			'programSponsorNumber' => 'PS702482',
			'programSponsorName' => 'Eticsoft Sponsor',
			'programParticipantNumber' => EticConfig::get('MASTERPASS_PART_NO'),
			'programParticipantName' => EticConfig::get('MASTERPASS_PART_NAME')
		);
	}
	
	private function setOrder() {
		$this->response['order'] =  array (
			'orderNumber' => $this->tr->id_cart,
			'orderTotal' => (int)(round($this->tr->total_pay, 2)*100),
			'secure3d' => $this->tr->tds
		);
	}
	
	private function setPayment() {
		$this->response['payment'] = array (
			'id' => $this->tr->id_transaction,
			'subId' => 0,
			'name' => $this->accountaliasname,
			'subName' => null,
			'tur' => 'KrediKarti',
			'onlineTransaction' => '1',
			'installmentCount' => $this->tr->installment,
		);
	}
	
	private function setTransaction() {
		$this->response['transaction'] = array (
			'mptd_url' => $this->tr->mptd_url,
		);
	}
	
	private function setToken() {
		$this->response['token'] = $this->generateToken($this->action);
	}
	
	private function setAdditionalParameters() {
		$this->response['additionalParameters'] = array (
			'number_time' => '',
			'owner_time' => '',
			'bill_country_code' => 'TR',
			'bill_email' => $this->tr->customer_email,
			'bill_first_name' => $this->tr->customer_firstname,
			'bill_last_name' => $this->tr->customer_lastname,
			'bill_phone' => $this->tr->customer_mobile,
			'order_timeout' => '',
			'card_program_name' => $this->tr->family == 'all' ? '' : $this->tr->family ,
			'campaign_type' => '',
			'order_shipping' => '',
			'delivery_address' => $this->tr->customer_address,
			'delivery_address2' => '',
			'delivery_city' => $this->tr->customer_city,
			'delivery_company' => '',
			'delivery_country_code' => 'TR',
			'delivery_email' => $this->tr->customer_email,
			'delivery_first_name' =>  $this->tr->customer_firstname,
			'delivery_last_name' =>  $this->tr->customer_lastname,
			'delivery_phone' =>  $this->tr->customer_mobile,
			'delivery_state' => '',
			'delivery_zip_code' => '',
			);
			
			foreach($this->tr->product_list as $prod){
				$this->response['additionalParameters']['order_product_code_arr'] []= $prod['id_product'];
				$this->response['additionalParameters']['order_product_info_arr'] []= "";
				$this->response['additionalParameters']['order_product_name_arr'] []= $prod['name'];
				$this->response['additionalParameters']['order_price_arr'] []= 0;
				$this->response['additionalParameters']['order_price_type_arr'] []= "";
				$this->response['additionalParameters']['order_qty_arr'] []= $prod['quantity'];
				$this->response['additionalParameters']['order_vat_arr'] []= "";
				$this->response['additionalParameters']['order_ver_arr'] []= "";
				$this->response['additionalParameters']['order_mplace_merchant_arr'] []= "";
			}

			$this->response['additionalParameters']['order_product_code_arr'] []= "0000";
			$this->response['additionalParameters']['order_product_info_arr'] []= "";
			$this->response['additionalParameters']['order_product_name_arr'] []= "Toplam Tutar";
			$this->response['additionalParameters']['order_price_arr'] []= (int)(round($this->tr->total_pay, 2)*100);
			$this->response['additionalParameters']['order_price_type_arr'] []= "";
			$this->response['additionalParameters']['order_qty_arr'] []= 1;
			$this->response['additionalParameters']['order_vat_arr'] []= "";
			$this->response['additionalParameters']['order_ver_arr'] []= "";
			$this->response['additionalParameters']['order_mplace_merchant_arr'] []= "";

			$this->response['additionalParameters']['client_ip'] = EticTools::getIp();
			$this->response['additionalParameters']['client_time'] = '';
	}
	
}
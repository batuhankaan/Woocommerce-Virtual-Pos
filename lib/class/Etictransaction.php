<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EticTransaction
 *
 * @author mahmut
 */
class EticTransaction
{

	public $id_transaction = false;
	public $exists = false;
	public $type = 'S';
	public $notify = false;
	//
	public $cc_name = false;
	public $cc_number = false;
	public $cc_cvv = false;
	public $cc_expire_year = false;
	public $cc_expire_month = false;
	//
	public $gateway = false;
	public $id_cart = false;
	public $id_currency = 1;
	public $id_order = false;
	public $id_customer = false;
	public $total_cart;
	public $total_pay;
	public $total_shipping;
	public $total_discount;
	public $gateway_fee;
	public $family;
	public $installment;
	//
	public $customer_firstname = false;
	public $customer_lastname = false;
	public $customer_address = false;
	public $customer_phone = false;
	public $customer_mobile = false;
	public $customer_email = false;
	public $customer_city;
	//
	public $currency_code = 'TRY';
	public $currency_number = '949';
	//
	public $cip = false;
	public $test_mode = false;
	public $tds = false;
	public $boid; // gatewaya order id
	public $result_code = NULL;
	public $result_message = 'Ödeme yapılmadı';
	public $result = false;
	public $debug = '';
	public $detail = array();
	//
	public $date_create;
	public $date_update;
	public $product_list = array();
	//
	public $shop_name;
	public $iso_lang;
	public $fail_url;
	public $ok_url;
	//
	public $gateway_params;
	public $tds_echo = false;

	function __construct($id_transaction = false)
	{
		if ($this->id_transaction AND ! $id_transaction)
			$id_transaction = $this->id_transaction;

		if ($id_transaction) {
			$this->id_transaction = (int) $id_transaction;
			if ($fields = $this->getById($this->id_transaction)) {
				$this->exists = true;
				foreach ($fields as $k => $v)
					$this->{$k} = $v;
				if ($this->detail)
					$this->detail = unserialize($this->detail);
			}
		}
		WC()->session = new WC_Session_Handler();
		WC()->session->init();								   
		$id_order = WC()->session->get( 'order_awaiting_payment');
		$order = new WC_Order($id_order);

		$this->ok_url = add_query_arg(array('sprtdvalidate' => 'success'), $order->get_checkout_payment_url(true));
		$this->fail_url = add_query_arg(array('sprtdvalidate' => 'fail'), $order->get_checkout_payment_url(true));
		$this->mptd_url = add_query_arg(array('mptd' => 'mptd'), $order->get_checkout_payment_url(true));
		$this->shop_name = get_option('blogname');
		$this->iso_lang = get_bloginfo("language");
		
		$this->date_create = date("Y-m-d H:i:s");
		$this->cip = EticTools::getIp();

		if ($this->gateway) {
			$gateway = New EticGateway($this->gateway);
			$this->gateway_params = $gateway->params;
			if (isset($gateway->params->test_mode))
				$this->test_mode = $gateway->params->test_mode == 'on' ? true : false;
		}
	}

	private function add()
	{
		if ($this->exists)
			return false;
		$fields = $this->getFormated();
		if (!$this->id_transaction = EticSql::insertRow('spr_transaction', $fields))
			return false;
		$this->exists = true;
		$this->saveDebug();
	}

	private function saveDebug()
	{
		if (EticConfig::get('POSPRO_DEBUG_MOD') != 'on')
			return true;
		if (EticSql::getRow('spr_debug', 'id_transaction', $this->id_transaction))
			return $this->updateDebug();
		return $this->addDebug();
	}

	private function addDebug()
	{
		return EticSql::insertRow('spr_debug', array('debug' => $this->debug, 'id_transaction' => $this->id_transaction));
	}

	private function update()
	{
		if (!$this->exists OR ! $this->id_transaction)
			return false;
		$fields = $this->getFormated();
		$fields['date_update'] = date("Y-m-d H:i:s");
		$this->saveDebug();
		return EticSql::updateRow('spr_transaction', $fields, 'id_transaction', $this->id_transaction);
	}

	private function updateDebug()
	{
		return EticSql::updateRow('spr_debug', array('debug' => $this->getDebug() . $this->debug), 'id_transaction', $this->id_transaction);
	}

	public function save()
	{
		if ($this->exists)
			return $this->update();
		return $this->add();
	}

	public function debug($txt, $save_point = false)
	{

		$called = debug_backtrace(false)[1];
		if(!isset($called['line']))
			$called['line'] = 0;
		
		$this->debug .= date("Y/m/d h:i:s") . "\t|"
			. EticSql::fix($txt) . "|\t"
			. $called['class'] . $called['type'] . $called['function'] . ':' . $called['line'] . "\n";
		if ($save_point)
			$this->save();
	}

	public function getDebug()
	{
		if ($debug = EticSql::getRow('spr_debug', 'id_transaction', $this->id_transaction))
			return $debug['debug'];
		return null;
	}

	public function detail($k, $v)
	{
		$this->detail [$k] = $v;
	}

	public function getDetail($k)
	{
		return isset($this->detail[$k]) ? $this->detail[$k] : false;
	}

	private function getFormated()
	{
//        $notes = array(
//            'currency_code' => $this->currency_code,
//            'currency_number' => $this->currency_number,
//            'language_code' => $this->language_code,
//        );

		return array(
			'notify' => $this->notify,
			'cc_name' => EticTools::escape($this->cc_name),
			'cc_number' => EticTools::maskCcNo($this->cc_number),
			'gateway' => $this->gateway,
			'id_cart' => $this->id_cart,
			'id_currency' => $this->id_currency,
			'id_order' => $this->id_order,
			'id_customer' => $this->id_customer,
			'total_cart' => (float) $this->total_cart,
			'total_pay' => (float) $this->total_pay,
			'gateway_fee' => (float) $this->gateway_fee,
			'installment' => (int) $this->installment,
			'cip' => (string) $this->cip,
			'test_mode' => (bool) $this->test_mode,
			'tds' => (bool) $this->tds,
			'boid' => (string) $this->boid,
			'result_code' => EticTools::escape($this->result_code),
			'result_message' => EticTools::escape($this->result_message),
			'result' => (bool) $this->result,
			//      'debug' => $this->debug,
			'detail' => serialize($this->detail),
			'date_create' => $this->date_create,
			'date_update' => $this->date_update,
		);
	}

	public function validateTransaction()
	{

		if (!$this->cc_number OR ! $this->cc_cvv OR ! $this->cc_name OR ! $this->cc_expire_month) {
			$this->result = false;
			$this->result_code = "V0001";
			$this->result_message = "Kart Bilgileri Eksik veya Hatalı";
			return false;
		}
		if (!$this->gateway OR ! EticSql::getRow('spr_gateway', array('name' => $this->gateway, 'active' => true))) {
			$this->result = false;
			$this->result_code = "V0004";
			$this->result_message = "Seçilen POS " . $this->gateway . " yok veya aktif değil";
			return false;
		}
		if ((int) ('20' . substr($this->cc_expire_year, -2) . str_pad($this->cc_expire_month, 2, 0, STR_PAD_LEFT)) < (int) date("Ym")) {
			$this->result = false;
			$this->result_code = "V0002";
			$this->result_message = "Kart Son Kullanım Tarihi Hatalı "
				. (int) ('20' . substr($this->cc_expire_year, -2) . $this->cc_expire_month) . ' - ' . date("Ym");
			return false;
		}
		if ($this->total_cart < 0.1 OR $this->total_pay < $this->total_cart) {
			$this->result = false;
			$this->result_code = "V0003";
			$this->result_message = "Sepet Toplamları Hatalı";
			return false;
		}
		if (!$this->family OR $this->family == null) {
			$this->result = false;
			$this->result_code = "V0005";
			$this->result_message = "Kart türü hatalı";
			return false;
		}
		$this->debug('Validated Internal ');
		return true;
	}

	private function updateTransactionByOrderId($record)
	{
		EticSql::updateRow('spr_transaction', $record->databaseStructure(), 'id_record = ' . (int) $record['id_record'], 1);
	}

	private function updateTransactionByCartId($record)
	{
		EticSql::updateRow('spr_transaction', $record, 'id_cart = ' . (int) $record['id_cart'], 1);
	}

	public function getTransactionByOrderId($id_order)
	{
		return EticSql::getRow('spr_transaction', array('id_order' => (int) $id_order));
	}

	public function getTransactionByBoId($boid)
	{
		return EticSql::getRow('spr_transaction', array('boid' => $boid));
	}

	public static function getTransactionByCartId($id_cart)
	{
		return EticSql::getRow('spr_transaction', array('id_cart' => (int) $id_cart));
	}

	public static function getById($id_transaction)
	{
		return EticSql::getRow('spr_transaction', array('id_transaction' => (int) $id_transaction));
	}

	public static function createTransaction()
	{
		$id_order = WC()->session->get( 'order_awaiting_payment');
		$order = new WC_Order($id_order);
		$currency = Etictools::getCurrency($order->get_currency());
		$tra = New EticTransaction();
		
		
		if (!$id_order || !$order)
			die("invalid cart");
		if ($exists = EticTransaction::getTransactionByCartId($id_order)) {
			$tra->id_transaction = $exists['id_transaction'];
			$tra->__construct();
			$tra->exists = true;
			$tra->detail('count', $tra->getDetail('count') + 1);
			// pre-rules for fraud
			// cnc =  card number change 
			// ipc =  ip change
			if ($tra->cc_number != EticTools::maskCcNo(EticTools::escape(str_replace(' ', '', Etictools::getValue('cc_number')))))
				$tra->detail('cnc', $tra->getDetail('cnc') + 1);
			if ($tra->cip != EticTools::getIp())
				$tra->detail('ipc', $tra->getDetail('ipc') + 1);
		}

		$tra->total_cart = $order->get_total();
		$tra->currency_code = $currency->iso_code;
		$tra->currency_number = $currency->iso_code_num;
		$tra->language_code = 'tr';
		$tra->id_cart = $order->get_id();
		$tra->id_customer = $order->get_customer_id();
		$tra->id_currency = $currency->iso_code_num;
		$tra->customer_city = $order->get_billing_city() ? $order->get_billing_city() : $order->get_shipping_city();
		$tra->customer_firstname = $order->get_billing_first_name() ? $order->get_billing_first_name() : $order->get_shipping_first_name();
		$tra->customer_lastname = $order->get_billing_last_name() ? $order->get_billing_last_name() : $order->get_billing_last_name();
		$tra->customer_address = $order->get_billing_address_1() . ' ' . $order->get_billing_address_1();
		if(!$tra->customer_address OR $tra->customer_address == null){
			$tra->customer_address = $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2();
		}
		$tra->customer_email = $order->get_billing_email();
		$tra->customer_mobile = Etictools::formatMobile($order->get_billing_phone());
		$tra->customer_phone = $order->get_billing_phone();
		$tra->customer_company = $order->get_billing_company();
		$tra->customer_identify = null;
		
		if (!$tra->customer_phone AND $tra->customer_mobile)
			$tra->customer_phone = $tra->customer_mobile;

		if (!$tra->customer_mobile OR ! EticTools::isMobile($tra->customer_mobile))
			$tra->customer_mobile = Etictools::formatMobile($tra->customer_phone);


		if (Etictools::getValue('cc_number')) {
			$tra->family = EticTools::escape(Etictools::getValue('cc_family'));
			$tra->installment = (int) Etictools::getValue('cc_installment') ? Etictools::getValue('cc_installment') : 1;
			$installment = New EticInstallment($tra->family, $tra->installment);
			if ($installment->exists) {
				$tra->gateway = $installment->gateway;
				$tra->total_pay = (float) (1 + ($installment->rate / 100)) * $tra->total_cart;
				$tra->gateway_fee = (float) ($installment->fee / 100) * $tra->total_pay;
				if ($gateway = new EticGateway($installment->gateway))
					$tra->gateway_params = $gateway->params;
			}
			$tra->cc_name = EticTools::escape(Etictools::getValue('cc_name'));
			$tra->cc_number = EticTools::escape(str_replace(' ', '', Etictools::getValue('cc_number')));
			$tra->cc_cvv = EticTools::escape(Etictools::getValue('cc_cvv'));
			if (Etictools::getValue('cc_expiry')) {
				$date = explode("/", EticTools::escape(Etictools::getValue('cc_expiry')));
				$tra->cc_expire_month = (int) $date[0];
				$tra->cc_expire_year = substr((int) $date[1], -2);
			}
		}
		$products = $order->get_items();
		foreach ($products as $product) {
			$tra->product_list[] = array(
				'id_product' => $product['product_id'],
				'name' => strip_tags($product['name']),
				'price' => $product['line_total'],
				'quantity' => $product['qty'],
			);
		}
		
		//$tra->total_shipping = $cart->getOrderTotal(true, 5);
		//$tra->total_discount = $cart->getOrderTotal(true, 2);
		
		return $tra;
	}

	public function requestFraudScore()
	{
		if (!$this->exists OR ! $this->id_transaction)
			return false;
		$data = $this;
		unset(
			$data->cc_cvv, $data->cc_expire_year, $data->cc_expire_month, $data->id_currency, $data->id_customer, $data->notify, $data->debug, $this->tds_echo, $this->gateway_params
		);
		$cli = New SanalPosApiClient($data->id_transaction);
		return $cli->validateRequest()
				->run($data)
				->getResponse();
	}

	public static function getAllRecords()
	{
		$sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'spr_transaction ORDER BY `date_create`';
		return Db::getInstance()->ExecuteS($sql);
	}

	public static function jsonMonthRecords()
	{
		$sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'spr_transaction GROUP BY YEAR(record_date)';
		$result = Db::getInstance()->ExecuteS($sql);
		$data = array();
		foreach ($result as $row) {
			$data[] = array(strftime("%m", strtotime($row['date_create'])), (float) $row['total_paid']);
		}
	}

	public function getShippingPrice()
	{
		$cart = New Cart($this->id_cart);
		return $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
	}

	public function getDiscounts()
	{
		$cart = New Cart($this->id_cart);
		return $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
	}
}

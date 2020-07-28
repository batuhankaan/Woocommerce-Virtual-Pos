<?php
/*
  Plugin Name: Eticsoft SanalPOS PRO! Multi Payment Gateway
  Plugin URI:  https://sanalpospro.com
  Description: SanalPOS PRO! provides all popular payment methods in one plug-in.
  Version:     1.1
  Author:      eticsoft.com
  Author URI:  EticSoft R&D Lab
  License:     GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Text Domain: wporg
  Domain Path: /languages
  Update Date: 09/12/2019
 */
include( plugin_dir_path(__FILE__) . '/lib/class/inc.php');


/* Define the database prefix */
global $wpdb;
define("_DB_PREFIX_", $wpdb->prefix);

/* Install Function */
register_activation_hook(__FILE__, 'sanalpospro_activate');

function sanalpospro_activate()
{
	global $wpdb;
	//Eticconfig::set('POSPRO_INSTALLED_VERSION', $this->version);
	$query1 = "
			CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "spr_gateway` (
            `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
            `method` enum('cc', 'wire', 'other') NOT NULL,
            `full_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
            `lib` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            `params` text COLLATE utf8_unicode_ci NOT NULL,
            UNIQUE KEY (`name`)
          ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	$query2 = "
			CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "spr_installment` (
            `gateway` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
            `family` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
            `divisor` int(3) NOT NULL,
            `rate` decimal(4,2) NOT NULL,
            `fee` decimal(4,2) NOT NULL DEFAULT '0.00',
            UNIQUE KEY `gateway` (`family`,`divisor`)
          ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	$query3 = "
			CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "spr_transaction` (
			  `id_transaction` int(11) NOT NULL AUTO_INCREMENT,
			  `notify` tinyint(1) NOT NULL DEFAULT '0',
			  `cc_name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `cc_number` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `gateway` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
			  `family` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
			  `id_cart` int(11) NOT NULL,
			  `id_currency` int(11) NOT NULL,
			  `id_order` int(11) DEFAULT NULL,
			  `id_customer` int(11) NOT NULL,
			  `total_cart` float(10,2) DEFAULT NULL,
			  `total_pay` float(10,2) DEFAULT NULL,
			  `gateway_fee` float(10,2) DEFAULT NULL,
			  `installment` int(2) NULL,
			  `cip` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `test_mode` int(1) NOT NULL,
			  `tds` int(1) NOT NULL,
			  `boid` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `result_code` varchar(24) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `result_message` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `result` int(1) NOT NULL,
			  `detail` TEXT DEFAULT NULL,
			  `date_create` datetime NOT NULL,
			  `date_update` datetime DEFAULT NULL,
			  PRIMARY KEY (`id_transaction`)
			) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
	;

	$query4 = "
			CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "spr_debug` (
			  `id_transaction` int(11) NOT NULL,
			  `debug` text NULL,
			  UNIQUE KEY `id_transaction` (`id_transaction`)
			) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
	return dbDelta($query1) && dbDelta($query2) && dbDelta($query3) && dbDelta($query4);
}
/* SanalPOS PRO! All Load */
add_action('plugins_loaded', 'init_sanalpospro_gateway_class', 0);

function init_sanalpospro_gateway_class()
{
	if (!class_exists('WC_Payment_Gateway'))
		return;

	class sanalpospro extends WC_Payment_Gateway
	{
		/*
		 * 	__construct function
		 */

		function __construct()
		{
			$this->id = "sanalpospro";
			$this->method_title = "SanalPOS PRO! Kredi Kartı İle Ödeme";
			$this->method_description = "SanalPOS PRO!  Kredi kartı ödeme alma eklentisi";
			$this->title = get_locale() == "tr_TR" ? "Kredi Kartı ile Ödeme" : "Payment by Credit Card";
			$this->icon = null;
			$this->has_fields = true;
			$this->supports = array('default_credit_card_form');
			$this->init_form_fields();
			$this->init_settings();
			$this->version = 1.03;
			$this->id_eticsoft = 21;

			foreach ($this->settings as $setting_key => $value)
				$this->$setting_key = $value;
			//Register the style
			add_action('admin_enqueue_scripts', array($this, 'register_sanalpospro_admin_styles'));
			add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
			add_action('woocommerce_thankyou_' . $this->id, array($this, 'receipt_page'));
			if (is_admin()) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			}
		}

// End __construct()
		public function register_sanalpospro_admin_styles()
		{
			wp_register_style('sanalpospro-adminpanel', plugins_url() . '/sanalpospro/views/css/admin.css');
		}

		public function admin_options()
		{
			echo '<script type="text/javascript">'
			. 'var sanalposprourl = "' . plugins_url('/sanalpospro') . '"'
			. '</script>';
			//NETGSM
			// . '<script type="text/javascript">'
			// . 'jQuery.noConflict(true);'
			// . '</script>';
			// wp_enqueue_script('sanalpospro_js_bootstrap_hack', plugins_url('/sanalpospro/views/js/bootstrap-hack.js'), false, '1.0.0', false);
			// wp_enqueue_script('sanalpospro_bootstrap', plugins_url('/sanalpospro/views/js/bootstrap.min.js'), false, '1.0.0', false);
			wp_enqueue_script('sanalpospro_admin', plugins_url('/sanalpospro/views/js/admin.js'), false, '1.0.0', false);

			if (Etictools::getValue('WOO_POSPRO_SETTINGS')) {
				update_option('woocommerce_sanalpospro_settings', Etictools::getValue('WOO_POSPRO_SETTINGS'));
			}
			//EticConfig::set('POSPRO_TERMS', false);


			if (Etictools::getValue('spr_terms')) {
				if (!EticTools::isMobile(EticTools::getValue('spr_shop_phone'))) {
					$error_message = 'Girdiğiniz cep telefonu (' . EticTools::getValue('spr_shop_phone') . ') doğru değil ! Doğru örnek 0505XXXYYZZ. 
				<br/> Not:<b> Cep telefonu numaranız kesinlikle spam/rahatsız edici sms amacıyla kullanılmayacaktır </b> ';
					include(plugin_dir_path(__FILE__) . '/views/templates/admin/terms.php');
					return;
				}

				$spapi = New SanalPosApiClient(0);
				$data = $spapi->getRegisterVariables();
				$request = Etictools::curlPostExt(
						array('data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), $spapi->getLoginFormValues()['url'] . 'register/', false);
				if (!$result = json_decode(Etictools::removeBOM($request)))
					$error_message = 'Veri alışverişi sırasında hata oldu. Lütfen Tekrar Deneyin veya EticSoft ile görüşünüz'
						. '<small>(' . $request . ')</small>';
				else if ($result->result) {
					EticConfig::set('POSPRO_TERMS', true);
					EticConfig::set('POSPRO_API_PUBLIC', $result->public);
					EticConfig::set('POSPRO_API_PRIVATE', $result->private);
					EticConfig::set('POSPRO_API_DOMAIN', $result->domain);
					EticConfig::set('POSPRO_API_EMAIL', $data['email']);
				} else
					$error_message = $result->result_message;
			}

			if (!EticConfig::get('POSPRO_API_PUBLIC') OR ! EticConfig::get('POSPRO_TERMS')) {
				include(plugin_dir_path(__FILE__) . '/views/templates/admin/terms.php');
				return;
			}

			$definitions = $this->sppGetStoreMethods();

			if ($definitions->result != 1 OR $definitions->result_message != 'success') {
				echo EticConfig::displayError($definitions->result_message, 'Hata ' . $definitions->result_code);
				return;
			}
			EticGateway::$gateways = $definitions->data->gateways;
			EticGateway::$api_libs = $definitions->data->api_libs;
			$this->sppSaveSettings();


			$api_libs = EticGateway::$api_libs;
			EticConfig::getConfigNotifications();
			$viewlog = (Etictools::getValue('id_transaction') ? $this->getDebug(Etictools::getValue('id_transaction')) : false);
			$general_tab = EticConfig::getAdminGeneralSettingsForm(plugin_dir_path(__FILE__));
			$banks_tab = EticConfig::getAdminGatewaySettingsForm(plugin_dir_path(__FILE__));
			$integration_tab = EticConfig::getAdminIntegrationForm(plugin_dir_path(__FILE__));
			$cards_tab = EticConfig::getCardSettingsForm(plugin_dir_path(__FILE__));
			$tools_tab = EticConfig::getAdminToolsForm(plugin_dir_path(__FILE__));
			$help_tab = EticConfig::getHelpForm(plugin_dir_path(__FILE__));
			$masterpass_tab = EticConfig::getMasterPassForm(plugin_dir_path(__FILE__));
			$last_records = $this->getLastRecordsTable();
			$stats_gateways = EticStats::getChart('getGwUsagebyTotal');
			$stats_monthly = EticStats::getChart('getMontlyIncome');
			$module_dir = plugin_dir_path(__FILE__);
			$messages = EticConfig::$messages;
			$key = EticTools::GenerateKey($this->id);

			include( plugin_dir_path(__FILE__) . '/views/templates/admin/form.php');
		}
		/* 	Admin Panel Fields */

		public function process_payment($order_id)
		{
			global $woocommerce;
			$order = new WC_Order($order_id);
			if (version_compare(WOOCOMMERCE_VERSION, '2.1.0', '>=')) {
				/* 2.1.0 */
				$checkout_payment_url = $order->get_checkout_payment_url(true);
			} else {
				/* 2.0.0 */
				$checkout_payment_url = get_permalink(get_option('woocommerce_pay_page_id'));
			}


			return array(
				'result' => 'success',
				'redirect' => $checkout_payment_url,
			);
		}

//END process_payment

		public function validate_fields()
		{
			return isset($_POST['sanalpospro-card-number']) && isset($_POST['sanalpospro-card-name']) && isset($_POST['sanalpospro-card-expiry']) && isset($_POST['sanalpospro-card-cvc']) && isset($_POST['sanalpospro_selected_installment']);
		}

//END validate_fields



		/* OVERRIDE */

		public function credit_card_form($args = array(), $fields = array())
		{

			/* ?>
			  <p>Ödemenizi tüm kredi kartları ile tek çekim ve aşağıdaki kartlar ile taksitli yapabilirsiniz. </p>
			  <img src="<?php echo plugins_url() ?>/sanalpospro/img/available_cards.png" width="100%"/>

			  <?php */
		}

		/**
		 * Generates secure key
		 */
		public function getKey($key)
		{
			return md5('EticSoft' . $key);
		}
		/*
		 * Post CC data to SanalPOS PRO! gateWay
		 */

		function receipt_page($orderid)
		{
			$error_message = false;
			$order = new WC_Order($orderid);
			$tr = EticTransaction::createTransaction();
			$status = $order->get_status();
			$cur_name = get_woocommerce_currency();
			$currency = Etictools::getCurrency($cur_name);
			$mp = false;

			echo '<script type="text/javascript">'
			. 'var sanalposprourl = "' . plugins_url('/sanalpospro') . '"'
			. '</script>';

			
			wp_enqueue_script('sanalpospro_jquerycard', plugins_url('/sanalpospro/views/js/jquery.card.js'), array('jquery'), '1.0.0', false);
			wp_enqueue_script('sanalpospro_jquerypayment', plugins_url('/sanalpospro/views/js/jquery.payment.min.js'), false, '1.0.0', false);
			wp_enqueue_script('sanalpospro_pro', plugins_url('/sanalpospro/views/js/pro.js'), false, '1.0.0', false);

			wp_register_style('sanalpospro_jquerycard', plugins_url() . '/sanalpospro/views/css/jquery.card.css');
			wp_register_style('sanalpospro_payment', plugins_url() . '/sanalpospro/views/css/payment.css');
			wp_register_style('sanalpospro_pro-form', plugins_url() . '/sanalpospro/views/css/pro-form.css');

			wp_enqueue_style('sanalpospro_jquerycard');
			wp_enqueue_style('sanalpospro_payment');
			wp_enqueue_style('sanalpospro_pro-form');

			if (EticConfig::get("MASTERPASS_ACTIVE") == 'on') {
				include(dirname(__FILE__).'/lib/masterpass/EticsoftMasterPassLoader.php');
				$mp = new EticsoftMasterpass($tr);
				$mp->prepareUi();
				wp_register_style('sanalpospro_masterpass', plugins_url() . '/sanalpospro/views/css/masterpass.css');
				wp_enqueue_style('sanalpospro_masterpass');
			}
			else {
				// wp_enqueue_script('sanalpospro_bootstrap', plugins_url('/sanalpospro/views/js/bootstrap.min.js'), false, '1.0.0', false);
				// wp_enqueue_script('sanalpospro_js_bootstrap_hack', plugins_url('/sanalpospro/views/js/bootstrap-hack.js'), false, '1.0.0', false);
			}

			$card_rates = EticInstallment::getRates((float) $tr->total_cart);
			$restrictions = EticInstallment::getRestrictedProducts($tr->id_cart);
			if (is_array($restrictions) && !empty($restrictions))
				$card_rates = array();

			$curname = $order->get_currency();
			$currency_default = 'TRY';
			$cards = $card_rates;
			$defaultins = EticInstallment::calcDefaultRate((float) $tr->total_cart);
			$c_auto_currency = EticConfig::get('POSPRO_AUTO_CURRENCY');
			$c_min_inst_amount = (float) EticConfig::get('POSPRO_MIN_INST_AMOUNT');
			$auf = EticConfig::get('POSPRO_ORDER_AUTOFORM');

			if (EticConfig::get("MASTERPASS_ACTIVE") == 'on') {

				if (Etictools::getValue('mp_api_token') AND Etictools::getValue('mp_api_refno')) {
					$mpgw = new EticsoftMasterpassGateway($tr, Etictools::getValue('mp_api_refno'));
					$mpgw->apiPay();
					$tr = $mpgw->tr;
					if ($tr->result) {
						$this->completePayment($order, $tr);
						return;
					}
					$error_message = $tr->result_code . ' ' . $tr->result_message;
					return include(dirname(__FILE__) . '/payform.php');
				}

				if (Etictools::getValue('mptd') AND Etictools::getValue('oid')) {
					$mpgw = new EticsoftMasterpassGateway($tr);
					$mpgw->tdValidate();
					$tr = $mpgw->tr;
					if ($tr->result) {
						$this->completePayment($order, $tr);
						return;
					}
					$error_message = $tr->result_code . ' ' . $tr->result_message;
					return include(dirname(__FILE__) . '/payform.php');
				}
			}

			if (!Etictools::getValue('cc_number') AND ! Etictools::getValue('sprtdvalidate')) {
				return include(dirname(__FILE__) . '/payform.php');
			}

			$gateway = New EticGateway($tr->gateway);
			$lib_class_name = 'Eticsoft_' . $gateway->lib;
			$lib_class_path = dirname(__FILE__) . '/lib/gateways/' . $gateway->lib . '/' . $lib_class_name . '.php';
			$tr->debug("Try to include  " . $lib_class_name, true);
			include_once($lib_class_path);

			if (Etictools::getValue('sprtdvalidate')) {
				if ($exists = EticTransaction::getTransactionByCartId($order->get_id())) {
					$tr->id_transaction = $exists['id_transaction'];
					$tr->__construct();
					$tr->exists = true;
				} else
					die("order not found");

				$lib = New $lib_class_name();
				$tr = $lib->tdValidate($tr);
				$tr->save();
			}
			else {
				$tr->createTransaction();
				$tr->debug("\n\n*********\n\n " . 'Form posted via ' . EticConfig::get('POSPRO_PAYMENT_PAGE'));
				if (!$tr->validateTransaction()) {
					$error_message = $tr->result_code . ' ' . $tr->result_message;
					return include(dirname(__FILE__) . '/payform.php');
				}
				$lib = New $lib_class_name();
				$tr = $lib->pay($tr);
				$tr->save();
				if ($tr->tds AND $tr->tds_echo) {
					echo $tr->tds_echo;
					return;
				}
			}
			if ($tr->result) {
				$this->completePayment($order, $tr);
				return;
			}
			$error_message = $tr->result_code . ' ' . $tr->result_message;
			return include(dirname(__FILE__) . '/payform.php');
		}

		private function completePayment($order, $tr)
		{
			$tr->id_order = $order->get_order_number();
			$order_fee = new stdClass();
			$order_fee->id = 'komisyon-farki';
			$order_fee->name = 'Kredi kartı komisyon farkı ' . $tr->installment . ' taksit';
			$order_fee->amount = $tr->total_pay - $tr->total_cart;
			$order_fee->taxable = false;
			$order_fee->tax = 0;
			$order_fee->tax_data = array();
			$order_fee->tax_class = '';
			$order->add_fee($order_fee);
			$order->calculate_totals(true);
			$order->update_status('processing', __('Processing SanalPOS PRO! payment', 'woocommerce'));
			$order->add_order_note('Ödeme SanalPOS PRO! ile tamamlandı. İşlem no: #' . $tr->id_transaction);
			$order->payment_complete();
			$tr->requestFraudScore();
			$tr->save();
			WC()->cart->empty_cart();
			wp_redirect($this->get_return_url());
			die('sipariş tamamlandı');
		}

		private function sppGetStoreMethods()
		{
			$cli = New SanalPosApiClient(1, 'UNKNOWN', 'getstoregateways2');
			return $cli->validateRequest()
					->run()
					->getResponse();
		}

		private function sppSaveSettings()
		{
			if (EticTools::getValue('add_new_pos')) {
				$gateway = New EticGateway(EticTools::getValue('add_new_pos'));
				$gateway->add();
			}
			if (Etictools::getValue('savetoolsform') OR Etictools::getValue('check-oldtables')) {
				EticConfig::saveToolsForm();
			}
			if (Etictools::getValue('submitcardrates')) {
				EticConfig::saveCardSettingsForm();
			}
			if (Etictools::getValue('submitgwsetting')) {
				EticConfig::saveGatewaySettings();
			}
			if (EticTools::getValue('conf-form') && EticTools::getValue('conf-form') == 1) {
				EticConfig::saveGeneralSettings();
				EticTools::rwm('Genel Ayarlar Güncellendi', true, 'success-spp');
			}
		}

		private function getLastRecordsTable()
		{
			$data = EticSql::getRows('spr_transaction');
			$table = new SanalPOSTable($data);
			return $table;
		}
	}

//END Class SanalPOS PRO!

	function sanalpospro($methods)
	{
		$methods[] = 'sanalpospro';
		return $methods;
	}
	add_filter('woocommerce_payment_gateways', 'sanalpospro');

	function cSanalPOSPRO()
	{
		global $woocommerce, $post;
		$order = new WC_Order($post->ID);
		$order_id = trim(str_replace('#', '', $order->get_order_number()));
		//echo $order_id;
	}
		if (Eticconfig::get('POSPRO_TAKSIT_GOSTER') != "off"){
			add_filter('woocommerce_product_tabs', 'woo_installment_tab');
		}
		
	function woo_installment_tab($tabs)
	{

		$tabs['test_tab'] = array(
			'title' => __('Taksit Seçenekleri', 'woocommerce'),
			'priority' => 50,
			'callback' => 'woo_installment_tab_content'
		);

		return $tabs;
	}

	function woo_installment_tab_content()
	{

		global $woocommerce;
		global $product;
		$price = $product->get_price();

		
		if (Eticconfig::get('POSPRO_TAKSIT_GOSTER') == "off")
			return "-";
		$ui = New EticUiWoo(New sanalpospro());
		echo '<script type="text/javascript">'
		. 'var sanalposprourl = "' . plugins_url('/sanalpospro') . '"'
		. '</script>';
		wp_enqueue_style('sanalpospro_inst', plugins_url('/sanalpospro/views/css/installments.css'));
		// wp_enqueue_script('sanalpospro_js_bootstrap_hack', plugins_url('/sanalpospro/views/js/bootstrap-hack.js'), false, '1.0.0', false);
		// wp_enqueue_script('sanalpospro_bootstrap', plugins_url('/sanalpospro/views/js/bootstrap.min.js'), false, '1.0.0', false);
		wp_enqueue_style('sanalpospro_installments', plugins_url('/sanalpospro/views/css/installments-' . Eticconfig::get('POSPRO_PRODUCT_TMP') . '.css'));
		echo '<div class="yui3-cssreset spp_bootstrap-wrapper">';
		echo $ui->displayProductInstallments($price);
		echo '<div>';
	}
}
add_action('woocommerce_order_actions_end', 'eticsoft_sanalpospro_order_details');

function eticsoft_sanalpospro_order_details($id)
{
	wp_register_style('sanalpospro-adminpanel', plugins_url() . '/sanalpospro/views/css/admin.css');
	wp_enqueue_style('sanalpospro-adminpanel');
	if (!$tra = EticSql::getRow('spr_transaction', 'id_order', $id))
		return false;
	$tr = New EticTransaction($tra['id_transaction']);
	$ui = New EticUiWoo();
	echo $ui->displayAdminOrder($tr);
}
if (!class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SanalPOSTable extends WP_List_Table
{

	private $easy_table_data = array();
	private $perpage = 30;

	function __construct($data, $perpage = 30, $name = 'records')
	{
		// WP_List_Table isn't available for some reason

		$this->perpage = $perpage;
		$this->easy_table_data = $data;
		parent::__construct(array(
			'singular' => $name,
			'plural' => $name . 's',
			'ajax' => false
		));
		//$this->prepare_items();
		//$this->display();
	}

	// prepare column names according to array keys (capitalized)
	function get_columns()
	{
		$first_row = $this->easy_table_data[0];
		$columns = array();
		foreach ($first_row as $key => $value) {
			$columns[$key] = __(ucwords(str_replace("_", " ", $key)));
		}
		return $columns;
	}

	// all columns are sortable by default
	public function get_sortable_columns()
	{
		$first_row = $this->easy_table_data[0];
		$sortable = array();
		foreach ($first_row as $key => $value) {
			$sortable[$key] = $key;
		}
		return $sortable;
	}

	// prepare items for display
	function prepare_items()
	{
		$data = $this->easy_table_data;
		$this->set_pagination_args(array(
			"total_items" => sizeof($data),
			"total_pages" => sizeof($data) / $this->perpage,
			"per_page" => $this->perpage,
		));
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $data;
	}

	// display the table
	// this is where you would want to modify the code if you want something special
	function display_rows()
	{
		$records = $this->items;
		list( $columns, $hidden ) = $this->get_column_info();
		$record_count = 0;
		foreach ($records as $rec) {
			$record_count++;
			echo '<tr id="record_' . $record_count . '">';

			foreach ($columns as $column_name => $column_display_name) {
				$class = "class='$column_name column-$column_name'";
				$style = "";

				if (in_array($column_name, $hidden))
					$style = ' style="display:none;"';
				$attributes = $class . $style;
				echo '<td ' . $attributes . '>' . @$rec[$column_name] . '</td>';
			}
			echo'</tr>';
		}
	}
}

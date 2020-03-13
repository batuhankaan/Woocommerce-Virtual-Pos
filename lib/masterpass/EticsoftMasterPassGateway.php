<?php
class EticSoftMasterPassGateway extends EticsoftMasterpass {

	public function tdValidate(){
		$tr = $this->tr;
		$tr->result_code = EticTools::getValue('errorCode');
		$tr->result_message = EticTools::getValue('errorMessage');
		$tr->result = false;
		
		$oid = (int)substr(Etictools::getValue('oid'), 0, 11);
		
		if($this->tr->id_cart	!= $oid){
			$tr->result_code = "MP01";
			$tr->result_message = $oid."Cart id's not matched".$tr->id_cart;
			$tr->notify = true;
			return false;
		}
        $tr->debug(" 3D POST Received  (MasterPass) |\n " . json_encode($_POST) . "\n", true);		
		$gateway = New EticGateway($tr->gateway);
		$f_names = array(
			$gateway->lib.$tr->gateway_params->tdmode,
			$gateway->lib
		);
		
		if($tr->gateway_params->tdmode == 'auto')
			$tr->gateway_params->tdmode = '3D';
		foreach($f_names as $f_name)
			if(method_exists($this, $f_name))
				return $this->$f_name();
		if($tr->gateway_params->tdmode == '3D')
			return $this->generic3d();
		if($tr->gateway_params->tdmode == '3D_PAY')
			return $this->generic3d_Pay();
		
		$tr->result_code = "MP02" ;
		$tr->result_message = $f_name.' is not callable';
		return $tr;
	}
	
	
	public function ipara() {
		return $this->generic3d();
	}
	
	public function apiPay(){
		return $this->genericApiPay();
	}
	
	public function genericApiPay() {
		$tr = $this->tr;
		$gw_params =  $tr->gateway_params;
		$data = array(
            'CustomCommitPurchaseRequest' => array(
                'transaction_header' => array(
                    'client_id' => $this->client_id,
                    'request_datetime' => date("Y-m-d") . 'T' . date('H:i:s'),
                    'request_reference_no' => $this->req_ref_no,
                    'send_sms' => 'N',
                    'send_sms_language' => 'tur',
                    'client_token' => '',
                    'device_fingerprint' => '',
                    'version' => '',
                    'ip_address' => $tr->cip,
                    'client_type' => '',
                ),
                'transaction_body' => array(
                    'amount' => (int)(round($tr->total_pay, 2)*100),
                    'order_no' => $this->req_ref_no,
                    'payment_type' => 'DIRECT_PAYMENT',
                    'installment_count' => $tr->installment,
                    'currency_code' => $tr->currency_code,
                    'acquirer_ica' => EticsoftMasterTools::icaMatch($tr->gateway),
                    'vpos_merchant_id' => "",
                    'vpos_merchant_terminal_id' => "",
                    'vpos_merchant_email' => EticConfig::get('PS_SHOP_EMAIL'),
                    'vpos_terminal_user_id' => "",
                    'vpos_provision_user_id' => "",
                    'vpos_provision_password' => "",
                    'vpos_store_key' => "",
                    'vpos_posnet_id' => null,
                    'token' => EticTools::getValue('mp_api_token'),
                    'msisdn' => $tr->customer_mobile,
                    'custom_fields' => "",
					'other_details' => array(
						'client_ip' => $tr->cip,
						'client_time' => null,
					),
					'anti_fraud_details' => array(
						'number_time' => null,
						'owner_time' => null,
					)
                )
            )
        );
		$data['CustomCommitPurchaseRequest']['transaction_body']['bill_details'] = array(
				'bill_last_name' => $tr->customer_lastname,
				'bill_first_name' => $tr->customer_firstname,
				'bill_email' => $tr->customer_email,
				'bill_phone' => $tr->customer_phone,
				'bill_country_code' => "TR",
				'bill_fax' => "",
				'bill_address' => $tr->customer_address,
				'bill_address2' => "",
				'bill_zip_code' => "",
				'bill_city' => $tr->customer_city,
		);
		$data['CustomCommitPurchaseRequest']['transaction_body']['delivery_details'] = array(
			'delivery_last_name' => $tr->customer_lastname,
			'delivery_first_name' => $tr->customer_firstname,
			'delivery_email' => $tr->customer_email,
			'delivery_phone' => $tr->customer_phone,
			'delivery_company' => "",
			'delivery_company' => "",
			'delivery_address' => $tr->customer_address,
			'delivery_address2' => "",
			'delivery_zip_code' => "",
			'delivery_state' => "",
			'delivery_country_code' => "TR",
		);
		
	
		foreach(EticsoftMasterTools::setMasterParamsApiPay($tr) as $k => $v)
			$data['CustomCommitPurchaseRequest']['transaction_body'][$k] = $v['val'];
		if($tr->gateway == 'ipara' OR $tr->gateway == 'payu'){
			if($tr->gateway == 'ipara')
				$data['CustomCommitPurchaseRequest']['transaction_body']['order_details']['vendor_id'] = 4;
			$data['CustomCommitPurchaseRequest']['transaction_body']['order_details']['orders'] = array();
			$data['CustomCommitPurchaseRequest']['transaction_body']['order_details']['orders'] []= array(
				'order_product_name' => 'Toplam Tutar',
				'order_product_code' => 1,
				'order_price' => (int)(round($tr->total_pay, 2)*100),
				'order_vat' => null,
				'order_qty' => 1,
				'order_product_info' => null,
				'order_ver' => null,
				'order_mplace_merchant' => null,
			);
		}
		$tr->debug("Data2Wsdl: ".print_r($data, true));
		
		//print_r($data); exit;
		if(EticConfig::get('MASTERPASS_STAGE') == 'TEST')
			$url = $this->backend_url.'MMIUIMasterPass_V2/MerchantServices/MPGCommitPurchaseService.asmx?wsdl';
		if(EticConfig::get('MASTERPASS_STAGE') == 'UAT')
			$url = $this->backend_url.'MMIUIMasterPass_V2_LB/MerchantServices/MPGCommitPurchaseService.asmx?wsdl';
		if(EticConfig::get('MASTERPASS_STAGE') == 'UAT')
			$url = $this->backend_url.'MMIUIMasterPass_V2_LB/MerchantServices/MPGCommitPurchaseService.asmx?wsdl';
			
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
			$tr->result_code = "MP04";
			$tr->result_message = $e->getMessage();	
			$tr->debug("WSDL Client Error: ".$e->getMessage());
			return $tr;
        }
		

        try {

            $result = $client->CustomCommitPurchase($data);
        } catch (Exception $e) {
			$tr->result_code = "MP05";
			$tr->result_message = $e->getMessage();	
			$tr->debug("WSDL Process Error: ".$e->getMessage());
			return $tr;
        }
		if(!isset($result->CustomCommitPurchaseResult->transaction_body)){
			$tr->result_code = "MP05";
			$tr->result_message = '';
			$tr->notify = true;
			return $tr;
		}
		$r = $result->CustomCommitPurchaseResult;
		
		$tr->result_code = $r->transaction_body->approval_code;
		$tr->result_message = "MasterPass ile ödendi." . $r->transaction_body->retrieval_reference_no;	
		$tr->boid = $r->transaction_body->retrieval_reference_no;	
		$tr->result = true;
		return $tr;
	}
		
	private function generic3d_Pay(){
		$tr = $this->tr;
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
            $tr->debug("Received POST (MasterPass): " . $post_log);
            $tr->notify = true;
            $tr->result_code = "-1";
            $tr->result_message = "Invalid Hash Signature";
            return $tr;
        }

        $tr->debug(" 3D POST Received  (MasterPass) |\n " . json_encode($_POST) . "\n");
//
// 3DPAY
//		
		return $tr;
	}
	
	private function generic3d() {
		
		$tr = $this->tr;
		if((int)EticTools::getValue('mdStatus') == 0 OR (int)EticTools::getValue('mdStatus') > 4){
			$tr->result_code = "MD ".EticTools::getValue('mdStatus');
			$tr->result_message = EticTools::getValue('mdErrorMsg'). ' ' .EticTools::getValue('errorMessage');
			return $tr;			
		}
		$gw_params = $tr->gateway_params;
		$data = array(
            'CustomCommitPurchaseRequest' => array(
                'transaction_header' => array(
                    'client_id' => $this->client_id,
                    'request_datetime' => date("Y-m-d") . 'T' . date('H:i:s'),
                    'request_reference_no' => $tr->id_cart,
                    'send_sms' => 'Y',
                    'send_sms_language' => 'eng',
                    'client_token' => '',
                    'device_fingerprint' => '',
                    'version' => '',
                    'ip_address' => '',
                    'client_type' => '',
                ),
                'transaction_body' => array(
                    'amount' => EticTools::getValue('amount'),
                    'order_no' => EticTools::getValue('oid'),
                    'payment_type' => 'DIRECT_PAYMENT',
                    'installment_count' => $tr->installment,
                    'currency_code' => $tr->currency_code,
                    'acquirer_ica' => EticTools::getValue('bankIca'),
                    'vpos_merchant_id' => "",
                    'vpos_merchant_terminal_id' => "",
                    'vpos_merchant_email' => EticConfig::get('PS_SHOP_EMAIL'),
                    'vpos_terminal_user_id' => "",
                    'vpos_provision_user_id' => "",
                    'vpos_provision_password' => "",
                    'vpos_store_key' => "",
                    'vpos_posnet_id' => null,
                    'token' => EticTools::getValue('token'),
                    'msisdn' => $tr->customer_mobile,
					'other_details' => array(
						'client_ip' => $tr->cip,
						'client_time' => null,
					),
					'anti_fraud_details' => array(
						'number_time' => null,
						'owner_time' => null,
					),
                    'custom_fields' => array(
						'custom_field_4' => array('name' => 'ECI', 'value' => EticTools::getValue('eci')),
						'custom_field_5' => array('name' => 'XID', 'value' => EticTools::getValue('xid')),
						'custom_field_6' => array('name' => 'CAVV', 'value' => EticTools::getValue('cavv')),
						'custom_field_7' => array('name' => 'MD', 'value' => EticTools::getValue('md')),
						'custom_field_11' => array('name' => 'MRCPACKET', 'value' => EticTools::getValue('merchantPacket')),
						'custom_field_12' => array('name' => 'BANKPACKET', 'value' => EticTools::getValue('bankPacket')),
						'custom_field_13' => array('name' => 'SIGN', 'value' => EticTools::getValue('sign')),
					)
                )
            )
        );
		if(EticConfig::get('MASTERPASS_STAGE') == 'TEST')
			$url = $this->backend_url.'MMIUIMasterPass_V2/MerchantServices/MPGCommitPurchaseService.asmx?wsdl';
		if(EticConfig::get('MASTERPASS_STAGE') == 'UAT')
			$url = $this->backend_url.'MMIUIMasterPass_V2_LB/MerchantServices/MPGCommitPurchaseService.asmx?wsdl';
		if(EticConfig::get('MASTERPASS_STAGE') == 'PROD')
			$url = $this->backend_url.'MMIUIMasterPass_V2_LB/MerchantServices/MPGCommitPurchaseService.asmx?wsdl';
				
				
		$data['CustomCommitPurchaseRequest']['transaction_body']['bill_details'] = array(
			'bill_last_name' => $tr->customer_lastname,
			'bill_first_name' => $tr->customer_firstname,
			'bill_email' => $tr->customer_email,
			'bill_phone' => $tr->customer_phone,
			'bill_country_code' => "TR",
			'bill_fax' => "",
			'bill_address' => $tr->customer_address,
			'bill_address2' => "",
			'bill_zip_code' => "",
			'bill_city' => $tr->customer_city,
		);
		
		$data['CustomCommitPurchaseRequest']['transaction_body']['delivery_details'] = array(
			'delivery_last_name' => $tr->customer_lastname,
			'delivery_first_name' => $tr->customer_firstname,
			'delivery_email' => $tr->customer_email,
			'delivery_phone' => $tr->customer_phone,
			'delivery_company' => "",
			'delivery_company' => "",
			'delivery_address' => $tr->customer_address,
			'delivery_address2' => "",
			'delivery_zip_code' => "",
			'delivery_state' => "",
			'delivery_country_code' => "TR",
		);
	
		foreach(EticsoftMasterTools::setMasterParamsApiPay($tr) as $k => $v)
			$data['CustomCommitPurchaseRequest']['transaction_body'][$k] = $v['val'];
		if($tr->gateway == 'ipara' OR $tr->gateway == 'payu'){
			if($tr->gateway == 'ipara')
				$data['CustomCommitPurchaseRequest']['transaction_body']['order_details']['vendor_id'] = 4;
			$prices = 0;
			$data['CustomCommitPurchaseRequest']['transaction_body']['order_details']['orders'] []= array(
				'order_product_name' => 'Toplam Tutar',
				'order_product_code' => 1,
				'order_price' => EticTools::getValue('amount'),
				'order_vat' => null,
				'order_qty' => 1,
				'order_product_info' => null,
				'order_ver' => null,
				'order_mplace_merchant' => null,
			);

			//$data['CustomCommitPurchaseRequest']['transaction_body']['order_details']['orders'] = array();
			// foreach($tr->product_list as $pro){
				// $price = (int)(round($pro['price']*$pro['quantity'], 2)*100);
				// $prices += $price;
				// $data['CustomCommitPurchaseRequest']['transaction_body']['order_details']['orders'] []= array(
					// 'order_product_name' => $pro['name'],
					// 'order_product_code' => $pro['id_product'],
					// 'order_price' => $price,
					// 'order_vat' => null,
					// 'order_qty' => $pro['quantity'],
					// 'order_product_info' => null,
					// 'order_ver' => null,
					// 'order_mplace_merchant' => null,
				// );
			// }
		}
		
		$tr->debug("Data2Wsdl:".print_r($data, true));

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
			$tr->debug("WSDL Client Error: ".$e->getMessage());
			$tr->result_code = "MP04";
			$tr->result_message = $e->getMessage();	
			return $tr;
        }
		

        try {

            $result = $client->CustomCommitPurchase($data);
        } catch (Exception $e) {
			$tr->result_code = "MP05";
			$tr->result_message = $e->getMessage();	
			$tr->debug("WSDL Process Error: ".$e->getMessage());
			return $tr;
        }
		if(!isset($result->CustomCommitPurchaseResult->transaction_body)){
			$tr->result_code = "MP05";
			$tr->result_message = '';
			$tr->notify = true;
			return $tr;
		}
		$r = $result->CustomCommitPurchaseResult;
		
		$tr->result_code = $r->transaction_body->approval_code;
		$tr->result_message = "MasterPass ile ödendi." . $r->transaction_body->retrieval_reference_no;	
		$tr->boid = $r->transaction_body->retrieval_reference_no;	
		$tr->result = true;
		return $tr;
	}	
	



	
}
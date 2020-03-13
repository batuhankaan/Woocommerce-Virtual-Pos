<?php

class EticsoftMasterTools{
	
	public static function paramTagMatch ($index){
		$array = array(
			'posnet' => array(
				'mid' 	 	=> array('key' => 'mid', 'mp_key'  => 'vpos_merchant_id','mp_name' => 'clientId', 'mp_tag'  => 'FF0C'),
				'enckey' 	=> array('key' => 'enckey','mp_key'  => 'vpos_store_key','mp_name' => 'enckey','mp_tag'  => 'FF11'),
			),
			'est' => array(
				'usr' 		=> array('key' => 'usr','mp_key'  => 'vpos_provision_user_id','mp_name' => 'userName','mp_tag'  => 'FF0E'),
				'pas' 		=> array('key' => 'pas','mp_key'  => 'vpos_provision_password','mp_name' => 'password','mp_tag'  => 'FF10'),
				'tid' 		=> array('key' => 'tid','mp_key'  => 'vpos_terminal_user_id','mp_name' => 'Vpos Terminal User Id','mp_tag'  => 'FF0E'),
				'mid' 		=> array('key' => 'mid','mp_key'  => 'vpos_merchant_id','mp_name' => ' Vpos Merchant Id','mp_tag'  => 'FF0B'),
				'cid' 		=> array('key' => 'cid','mp_key'  => 'vpos_merchant_id','mp_name' => ' Vpos Merchant Id ','mp_tag'  => 'FF0B'),
				'key' 		=> array('key' => 'key','mp_key'  => 'vpos_store_key',	'mp_name' => 'storeKey','mp_tag'  => 'FF11'),
				'tdm' 		=> array('key' => 'tdm','mp_key'  => 'vpos_3D_model',	'mp_name' => 'storeKey','mp_tag'  => 'FF15'),
			)
		);
		return isset($array[$index]) ? $array[$index] : false;
	}
	public static function tagParamMatch ($index){
		$array = array(
			'posnet' => array(
				'mid' 	 	=> array('param' => 'mid', 'mp_key'  => 'vpos_merchant_id','mp_name' => 'clientId', 'mp_tag'  => 'FF0C'),
				'enckey' 	=> array('param' => 'enckey','mp_key'  => 'vpos_store_key','mp_name' => 'enckey','mp_tag'  => 'FF11'),
			),
			'est' => array(
				'vpos_merchant_id'			=> array('param' => 'mid', 'mp_tag'  => 'FF11'),
				'vpos_store_key' 		 	=> array('param' => 'key', 'mp_tag'  => 'FF11'),
				'vpos_provision_user_id' 	=> array('param' => 'usr', 'mp_tag'  => 'FF0E'),
				'vpos_terminal_user_id' 	=> array('param' => 'tid', 'mp_tag'  => 'FF10'),
				'vpos_provision_password' 	=> array('param' => 'pas', 'mp_tag'  => 'FF10'),
			),
		);
		return isset($array[$index]) ? $array[$index] : false;
	}
	
	public static function icaMatch($gw_name){
		$array = array(
			'garanti' => 2030,
			'akbank' => 2110,
			'vakifbank' => 2119,
			'ziraat' => 2374,
			'halkbank' => 3039,
			'turkishbank' => 9088,
			'finsansbank' => 1684,
			'tekstilbank' => 6701,
			'yapikredi' => 2117,
			'citibank' => 7244,
			'fibabanka' => 7182,
			'denizbank' => 7338,
			'hsbc' => 7656,
			'ingbank' => 2029,
			'anadolubank' => 7160,
			'teb' => 9165,
			'sekerbank' => 9299,
			'kuveytturk' => 8914,
			'bankasya' => 4033,
			'albaraka' => 10554,
			'tfinans' => 10684,
			'isbankasi' => "3771",
			'bankopozitif' => 14348,
			'odeobank' => 14194,
			'aktifbank' => 15140,
			'payu' => 1000,
			'ipara' => 1002,
		);
		return isset($array[$gw_name]) ? $array[$gw_name] : false;		
	}
	
	public static function setMasterParamsApiPay($tr){
		$gw = New EticGateway($tr->gateway);
		$lib = $gw->lib;
		$gw_params = $tr->gateway_params;
		
		if($lib == 'est') 
			return array(
				'vpos_merchant_id'			=> array('val' => $gw_params->mid, 'mp_tag'  => 'FF0B'), //Asseco tarafından iletilen clientid,merchantid bilgisidir.
				'vpos_merchant_terminal_id'	=> array('val' => $gw_params->tid, 'mp_tag'  => 'FF0C'), //Herhangi bir değer belirleyebilirsiniz
				'vpos_terminal_user_id' 	=> array('val' => $gw_params->usr, 'mp_tag'  => 'FF0E'), //Herhangi bir değer olabilir. Tercihimiz provision_user_id 
				'vpos_provision_user_id' 	=> array('val' => $gw_params->usr, 'mp_tag'  => 'FF0F'), //Asseco tarafından iletilen userid,username bilgisidir.
				'vpos_provision_password' 	=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'), //Asseco tarafından iletilen password bilgisidir.
				'vpos_store_key' 			=> array('val' => $gw_params->key, 'mp_tag'  => 'FF11'), //Asseco tarafından iletilen enckey bilgisidir
			);
		if($lib == 'garanti') 
			return array(
				'vpos_merchant_id'			=> array('val' => $gw_params->mid, 'mp_tag'  => 'FF0B'), 
				'vpos_merchant_terminal_id'	=> array('val' => $gw_params->tid, 'mp_tag'  => 'FF0C'),
				'vpos_terminal_user_id' 	=> array('val' => $gw_params->usr, 'mp_tag'  => 'FF0E'),
				'vpos_provision_password' 	=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'),
				'vpos_store_key' 			=> array('val' => $gw_params->sec, 'mp_tag'  => 'FF11'),
			);
		if($lib == 'kvtpos') 
			return array(
				'vpos_merchant_id'			=> array('val' => $gw_params->mid, 'mp_tag'  => 'FF0B'), 
				'vpos_merchant_terminal_id'	=> array('val' => $gw_params->cid, 'mp_tag'  => 'FF0C'), 
				'vpos_terminal_user_id' 	=> array('val' => $gw_params->usr, 'mp_tag'  => 'FF0E'),
				'vpos_provision_user_id' 	=> array('val' => $gw_params->usr, 'mp_tag'  => 'FF0F'),
				'vpos_provision_password' 	=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'),
			);
		if($lib == 'payu') 
			return array(
				'vpos_provision_user_id' 	=> array('val' => $gw_params->payu_merchant, 'mp_tag'  => 'FF0F'), //Asseco tarafından iletilen userid,username bilgisidir.
				'vpos_provision_password' 	=> array('val' => $gw_params->payu_key, 'mp_tag'  => 'FF10'), //Asseco tarafından iletilen password bilgisidir.
				'vpos_store_key' 			=> array('val' => $gw_params->payu_key, 'mp_tag'  => 'FF11'), //Asseco tarafından iletilen enckey bilgisidir
			);
		if($lib == 'ipara') 
			return array(
				'vpos_terminal_user_id' 	=> array('val' => $gw_params->public, 'mp_tag'  => 'FF0E'), 
				'vpos_provision_password' 	=> array('val' => $gw_params->private, 'mp_tag'  => 'FF10'),
				'vpos_store_key' 			=> array('val' => $gw_params->private, 'mp_tag'  => 'FF11'),
			);		
	}

	
	public static function setMasterParams($tr){
		$gw = New EticGateway($tr->gateway);
		$lib = $gw->lib;
		$gw_params = $tr->gateway_params;
		
		if($lib == 'est') 
			return array(
				'merchant_type'				=> array('val' => 01, 'mp_tag'  => 'FF09', 'is_numeric' => 1), //
				'vpos_currency_code'		=> array('val' => $tr->currency_code, 'mp_tag'  => 'FF0A'), 
				'vpos_merchant_id'			=> array('val' => $gw_params->mid, 'mp_tag'  => 'FF0B'), //Asseco tarafından iletilen clientid,merchantid bilgisidir.
				'vpos_merchant_terminal_id'	=> array('val' => $gw_params->tid, 'mp_tag'  => 'FF0C'), //Herhangi bir değer belirleyebilirsiniz
				'vpos_merchant_email  ' 	=> array('val' => EticConfig::get('PS_SHOP_EMAIL'), 'mp_tag'  => 'FF0D'), //Herhangi bir değer olabilir.
				'vpos_terminal_user_id' 	=> array('val' => $gw_params->usr, 'mp_tag'  => 'FF0E'), //Herhangi bir değer olabilir. Tercihimiz provision_user_id 
				'vpos_provision_user_id' 	=> array('val' => $gw_params->usr, 'mp_tag'  => 'FF0F'), //Asseco tarafından iletilen userid,username bilgisidir.
				'vpos_provision_password' 	=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'), //Asseco tarafından iletilen password bilgisidir.
			//	'Vpos Refund Password' 		=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'), //Asseco tarafından iletilen password bilgisidir.
				'vpos_store_key' 			=> array('val' => $gw_params->key, 'mp_tag'  => 'FF11'), //Asseco tarafından iletilen enckey bilgisidir
				'vpos_posnet_id' 			=> array('val' => '0', 'mp_tag'  => 'FF12'),
				'bank_ica' 					=> array('val' => (string)EticsoftMasterTools::icaMatch($tr->gateway), 'mp_tag'  => 'FF13'),
				'vpos_3D_model' 			=> array('val' => ($gw_params->tdmode == '3D' ? 00 : 01), 'mp_tag'  => 'FF15', 'is_numeric' => 1),
			);
		if($lib == 'garanti') 
			return array(
				'merchant_type'				=> array('val' => 01, 'mp_tag'  => 'FF09', 'is_numeric' => 1), //
				'vpos_currency_code'		=> array('val' => $tr->currency_code, 'mp_tag'  => 'FF0A'), 
				'vpos_merchant_id'			=> array('val' => $gw_params->mid, 'mp_tag'  => 'FF0B'), 
				'vpos_merchant_terminal_id'	=> array('val' => $gw_params->tid, 'mp_tag'  => 'FF0C'),
				'vpos_merchant_email  ' 	=> array('val' => EticConfig::get('PS_SHOP_EMAIL'), 'mp_tag'  => 'FF0D'),
				'vpos_terminal_user_id' 	=> array('val' => $gw_params->usr, 'mp_tag'  => 'FF0E'),
				'vpos_provision_user_id' 	=> array('val' => "PROVRFN", 'mp_tag'  => 'FF0F'),
				'vpos_provision_password' 	=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'),
				'Vpos Refund Password' 		=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'),
				'vpos_store_key' 			=> array('val' => $gw_params->sec, 'mp_tag'  => 'FF11'),
				'vpos_posnet_id' 			=> array('val' => '0', 'mp_tag'  => 'FF12'),
				'bank_ica' 					=> array('val' => (string)EticsoftMasterTools::icaMatch($tr->gateway), 'mp_tag'  => 'FF13'),
				'vpos_3D_model' 			=> array('val' => ($gw_params->tdmode == '3D' ? 00 : 01), 'mp_tag'  => 'FF15', 'is_numeric' => 1),
			);
		if($lib == 'kvtpos') 
			return array(
				'merchant_type'				=> array('val' => 01, 'mp_tag'  => 'FF09', 'is_numeric' => 1), //
				'vpos_currency_code'		=> array('val' => $tr->currency_code, 'mp_tag'  => 'FF0A'), 
				'vpos_merchant_id'			=> array('val' => $gw_params->mid, 'mp_tag'  => 'FF0B'), 
				'vpos_merchant_terminal_id'	=> array('val' => $gw_params->cid, 'mp_tag'  => 'FF0C'), 
				'vpos_merchant_email  ' 	=> array('val' => EticConfig::get('PS_SHOP_EMAIL'), 'mp_tag'  => 'FF0D'),
				'vpos_terminal_user_id' 	=> array('val' => $gw_params->usr, 'mp_tag'  => 'FF0E'),
				'vpos_provision_user_id' 	=> array('val' => $gw_params->usr, 'mp_tag'  => 'FF0F'),
				'vpos_provision_password' 	=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'),
			//	'Vpos Refund Password' 		=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'),
				'vpos_store_key' 			=> array('val' => "", 'mp_tag'  => 'FF11'),
				'vpos_posnet_id' 			=> array('val' => '0', 'mp_tag'  => 'FF12'),
				'bank_ica' 					=> array('val' => (string)EticsoftMasterTools::icaMatch($tr->gateway), 'mp_tag'  => 'FF13'),
				'vpos_3D_model' 			=> array('val' => ($gw_params->tdmode == '3D' ? 00 : 01), 'mp_tag'  => 'FF15', 'is_numeric' => 1),
			);
		if($lib == 'payu') 
			return array(
				'merchant_type'				=> array('val' => 01, 'mp_tag'  => 'FF09', 'is_numeric' => 1), //
				'vpos_currency_code'		=> array('val' => $tr->currency_code, 'mp_tag'  => 'FF0A'), 
				'vpos_merchant_email  ' 	=> array('val' => EticConfig::get('PS_SHOP_EMAIL'), 'mp_tag'  => 'FF0D'), //Herhangi bir değer olabilir.
			//	'vpos_terminal_user_id' 	=> array('val' => "", 'mp_tag'  => 'FF0E'), //Herhangi bir değer olabilir. Tercihimiz provision_user_id 
				'vpos_provision_user_id' 	=> array('val' => $gw_params->payu_merchant, 'mp_tag'  => 'FF0F'), //Asseco tarafından iletilen userid,username bilgisidir.
				'vpos_provision_password' 	=> array('val' => $gw_params->payu_key, 'mp_tag'  => 'FF10'), //Asseco tarafından iletilen password bilgisidir.
			//	'Vpos Refund Password' 		=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'), //Asseco tarafından iletilen password bilgisidir.
				'vpos_store_key' 			=> array('val' => $gw_params->payu_key, 'mp_tag'  => 'FF11'), //Asseco tarafından iletilen enckey bilgisidir
				'bank_ica' 					=> array('val' => "1000", 'is_numeric', 'mp_tag'  => 'FF13'),
			//	'vpos_3D_model' 			=> array('val' => ($gw_params->tdmode == '3D' ? 00 : 01), 'mp_tag'  => 'FF15', 'is_numeric' => 1),
			);
		if($lib == 'ipara') 
			return array(
				'merchant_type'				=> array('val' => 01, 'mp_tag'  => 'FF09', 'is_numeric' => 1), //
				'vpos_currency_code'		=> array('val' => $tr->currency_code, 'mp_tag'  => 'FF0A'), 
			//	'vpos_merchant_id'			=> array('val' => "0", 'mp_tag'  => 'FF0B'), 
			//	'vpos_merchant_terminal_id'	=> array('val' => "0", 'mp_tag'  => 'FF0C'), 
				'vpos_merchant_email  ' 	=> array('val' => EticConfig::get('PS_SHOP_EMAIL'), 'mp_tag'  => 'FF0D'), 
				'vpos_terminal_user_id' 	=> array('val' => $gw_params->public, 'mp_tag'  => 'FF0E'), 
			//	'vpos_provision_user_id' 	=> array('val' => $gw_params->public, 'mp_tag'  => 'FF0F'),
				'vpos_provision_password' 	=> array('val' => $gw_params->private, 'mp_tag'  => 'FF10'),
			//	'Vpos Refund Password' 		=> array('val' => $gw_params->pas, 'mp_tag'  => 'FF10'),
				'vpos_store_key' 			=> array('val' => $gw_params->private, 'mp_tag'  => 'FF11'),
			//	'vpos_posnet_id' 			=> array('val' => '0', 'mp_tag'  => 'FF12'),
				'bank_ica' 					=> array('val' => "1002", 'is_numeric', 'mp_tag'  => 'FF13'),
			//	'vpos_3D_model' 			=> array('val' => ($gw_params->tdmode == 'off' ? 00 : 01), 'mp_tag'  => 'FF15', 'is_numeric' => 1),
			);
			echo $lib.' lib not found';
			print_R($tr);
			exit;
	}
	
	// public static function tagParamMatch ($index){
		// $array = array(
			// 'posnet' => array(
				// 'mid' 	 	=> array('param' => 'mid', 'mp_key'  => 'vpos_merchant_id','mp_name' => 'clientId', 'mp_tag'  => 'FF0C'),
				// 'enckey' 	=> array('param' => 'enckey','mp_key'  => 'vpos_store_key','mp_name' => 'enckey','mp_tag'  => 'FF11'),
			// ),
			// 'est' => array(
				// 'vpos_merchant_id'			=> array('param' => 'mid', 'mp_tag'  => 'FF0B'), //Asseco tarafından iletilen clientid,merchantid bilgisidir.
				// 'vpos_merchant_terminal_id'	=> array('param' => 'mid', 'mp_tag'  => 'FF0C'), //Herhangi bir değer belirleyebilirsiniz
				// 'vpos_terminal_user_id' 	=> array('param' => 'mid', 'mp_tag'  => 'FF0E'), //Herhangi bir değer olabilir. Tercihimiz provision_user_id 
				// 'vpos_provision_user_id' 	=> array('param' => 'usr', 'mp_tag'  => 'FF0F'), //Asseco tarafından iletilen userid,username bilgisidir.
				// 'vpos_provision_password' 	=> array('param' => 'pas', 'mp_tag'  => 'FF10'), //Asseco tarafından iletilen password bilgisidir.
				// //'Vpos Refund Password' 		=> array('param' => 'pas', 'mp_tag'  => 'FF10'), //Asseco tarafından iletilen password bilgisidir.
				// 'vpos_store_key' 			=> array('param' => 'key', 'mp_tag'  => 'FF11'), //Asseco tarafından iletilen enckey bilgisidir
				// 'vpos_merchant_email  ' 	=> array('param' => 'key', 'mp_tag'  => 'FF11', 'val' => ''), //Herhangi bir değer olabilir.
				// 'bank_ica' 					=> array('param' => 'key', 'mp_tag'  => 'FF11', 'val' => ''), //Herhangi bir değer olabilir.
			// ),
		// );
		// return isset($array[$index]) ? $array[$index] : false;
	// }
	
	public static function getTagbyName($index) {
		$array = array(
			'vpos_merchant_id' => 'FF0B',
			'vpos_merchant_terminal_id' => 'FF0C',
			'vpos_merchant_email' => 'FF0D',
			'vpos_terminal_user_id' => 'FF0E',
			'vpos_provision_user_id' => 'FF0F',
			'vpos_provision_password' => 'FF10',
			'vpos_store_key' => 'FF11',
			'vpos_posnet_id' => 'FF12'
		);
		return isset($array[$index]) ? $array[$index] : false;
	}
	
	public static function setTagsbyParams($tr){
		$gw = New EticGateway($tr->gateway);
		$lib = $gw->lib;
		$mac = EticsoftMasterTools::paramTagMatch($gw->lib);
		$return = array();
		foreach($tr->gateway_params as $k => $v){
			$param = $mac[$k];
			$param['val'] = $v;
			if($k == 'tdm')
				$param['val'] = 01;
			$return[]= $param;
		}
		return $return;
	}	
	
	public static function to_xml(SimpleXMLElement $object, array $data)
	{   
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$new_object = $object->addChild($key);
				$this->to_xml($new_object, $value);
			} else {
				// if the key is an integer, it needs text with it to actually work.
				if ($key == (int) $key) {
					$key = "$key";
				}

				$object->addChild($key, $value);
			}   
		}   
	}   

}
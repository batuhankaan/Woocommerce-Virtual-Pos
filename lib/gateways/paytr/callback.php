<?php 


include_once (__DIR__.'/../../../../../../wp-config.php');

$query='SELECT * FROM ' . $wpdb->prefix . 'spr_gateway where name="paytr"';
$gateway = $wpdb->get_row($query);

$params = json_decode($gateway->params); 

$post = $_POST;

$merchant_key 	= $params->merchant_key;
$merchant_salt	= $params->merchant_salt;

$hash = base64_encode( hash_hmac('sha256', $post['merchant_oid'].$merchant_salt.$post['status'].$post['total_amount'], $merchant_key, true) );

if( $hash != $post['hash'] )
	die('PAYTR notification failed: bad hash');

if( $post['status'] == 'success' ) { 

	

} else { 


	//die($post['failed_reason_code']." - ".$post['failed_reason_msg']);

} 

echo "OK";
exit;
?>

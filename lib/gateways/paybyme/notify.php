<?php 

 if ($_SERVER['REQUEST_METHOD'] != 'POST') {
   exit;
 }


include_once (__DIR__.'/../../../../../../wp-config.php');

$query='SELECT * FROM ' . $wpdb->prefix . 'spr_gateway where name="paybyme"';
$gateway = $wpdb->get_row($query);

$params = json_decode($gateway->params); 
$status		= isset($_POST["status"]) ? $_POST["status"] : "";
$secretKey	= isset($_POST["secretKey"]) ? $_POST["secretKey"] : "";  

if($secretKey == $params->secretKey) {
	if ($status == 1)
	{
		// Log success
	}
	else
	{
		// Log error
	}
	echo 'OK';
}
else
{
	echo 'Error!';
}

?>


<?php
include(dirname(__FILE__).'/../../../../../../wp-load.php');
//include(dirname(__FILE__).'/../../../sanalpospro.php');
include(dirname(__FILE__).'/../EticsoftMasterPassLoader.php');
header('Content-type: application/json; charset=utf-8');

// if(!EticTools::getValue('callback'))
	// response('here');
$allowed_actions = array(
	'registerpurchase', 'directpurchase', 'purchase', 'setInternal'
);

$tr = EticTransaction::createTransaction();
if(EticTools::isMobile(EticTools::formatMobile(EticTools::getValue('accountnewPhone')))){
	$tr->customer_mobile = EticTools::formatMobile(EticTools::getValue('accountnewPhone'));
	$tr->save();
}

$m = New EticsoftMasterPassJsApi($tr, EticTools::getValue('ref'));
$m->action = EticTools::getValue('a');
echo $m->run();
$tr->save();
exit;
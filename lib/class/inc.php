<?php

/* 
 */
include_once (dirname(__FILE__).'/Eticconfig.php');
include_once (dirname(__FILE__).'/Etictools.php');
include_once (dirname(__FILE__).'/Eticgateway.php');
include_once (dirname(__FILE__).'/Eticinstallment.php');
include_once (dirname(__FILE__).'/Eticsql.php');
include_once (dirname(__FILE__).'/Etictransaction.php');
include_once (dirname(__FILE__).'/SanalPosApiClient.php');
include_once (dirname(__FILE__).'/Eticstats.php');
include_once (dirname(__FILE__).'/EticUi.php');
include_once (dirname(__FILE__).'/EticUiWoo.php');
include_once (dirname(__FILE__).'/../tool/SameSiteCookieSetter.php');
include_once (dirname(__FILE__).'/../extraCode.php');

// if(substr(get_locale(), 0,2) == 'tr'){
//     include_once (dirname(__FILE__).'/../lang/tr.php');
// }else{
//     include_once (dirname(__FILE__).'/../lang/en.php');
// }
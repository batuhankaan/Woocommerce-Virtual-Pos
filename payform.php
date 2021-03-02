<?php  
if (!defined('ABSPATH')) {
	exit;
}
		/**
			Chrome Cookie SameSite Policy fix 
			*/
 
			$path = COOKIEPATH;
			$domain = COOKIE_DOMAIN;
			foreach($_COOKIE as $k => $v){
				SameSiteCookieSetter::setcookie($k,$v, array('secure' => true, 'samesite' => 'None', 'path' => $path, 'domain' => $domain));
			}	
			/**
			Chrome Cookie SameSite Policy fix 
			*/
			// include_once(dirname(__FILE__).'/lib/class/Eticconfig.php');

?>
<div class="spp_bootstrap-wrapper">
<input type="hidden" id="spp_lang" value="<?php echo _Lang ?>">

<script>
    var $ = jQuery;
<?php /* if ($c_auto_currency == "on" AND $currency->name != 'TRY'): ?>
  setCurrency({$currency_default);
  alert('Seçtiğiniz para birimi bu ödeme yönteminde kullanılamıyor. Kurunuz <?php echo $currency->name ?> olarak değiştrildi.')
  <?php endif; */ ?>
    var protrccname = '<?php echo __('Your Name') ?>';
    var currency_sign = "<?php echo $currency->sign ?>";
    var card = new Array();
    var cards = new Array();
    var sanalposprouri = "<?php echo plugins_url() ?>/sanalpospro/";
    var defaultins = "<?php echo $defaultins['total'] ?>";

	<?php foreach ($cards as $family => $frates): ?>
	    cards ['<?php echo $family ?>'] = new Array();
	<?php foreach ($frates as $div => $ins): ?>
		<?php if ($c_min_inst_amount < $ins['month']): ?>
			    cards["<?php echo $family ?>"]["<?php echo $div ?>"] = "<?php echo $ins['total'] ?>";
		<?php endif; ?>
	<?php endforeach; ?>
<?php endforeach; ?>
</script>

	<div class="row" id="spp_top">
		<div class="col-xs-12 col-lg-6">
			<h2><?php echo _MethodHeader ?></h2>
			<small>
				<?php echo _MethodDesc1 ?><br/>
				<?php echo _MethodDesc2 ?>
			</small>
		</div>
		<div class="col-xs-12 col-sm-6 hidden-md-down">
			<img class="img-responsive" src="<?php echo plugins_url() ?>/sanalpospro/img/safepayment.png"/>
		</div>
	</div>

	<?php if ($error_message) : ?>
		<div class="row">
			<div class="alert alert-danger" id="errDiv">
				<div class="spperror" id="errDiv">
					<?php echo __('Ödeme başarısız. Banka cevabınız:') ?> <br/><?php echo $error_message ?><br/>
					<?php echo __('Lütfen formu kontrol edip tekrar deneyin.') ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<hr/>



<form class="w-30 p-3 mx-auto" novalidate action="<?php echo $order->get_checkout_payment_url(true);?>" autocomplete="on" method="POST" id="cc_form" style="background:<?= EticConfig::get("POSPRO_PAYMENT_PAGE_BGCOLOR"); ?> !important;">
		<div class="d-inline-block bg-white w-100" id="cc_form_table">
				<div class="col-xs-12 col-sm-12 col-md-12">
					<img src="<?php echo plugins_url() ?>/sanalpospro/img/icons/credit-card.svg"" alt="" srcset="">
					<input class="cc_input form-control input-lg border-0 shadow-none" type="text" id="cc_number" name="cc_number" onchange="sppFormApp.keyup(this)" oninput="sppFormApp.skipIfMax(this)" maxlength="19" placeholder="<?php echo _CardNumber ?>" 
						   value="<?php if (Etictools::getValue('cc_number')): ?><?php echo Etictools::getValue('cc_number') ?><?php endif; ?>"/>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12">
				<img src="<?php echo plugins_url() ?>/sanalpospro/img/icons/calendar.svg"" alt="" srcset="">
					<input class="cc_input form-control input-lg border-0 shadow-none" type="text" id="cc_expiry" name="cc_expiry"  oninput="sppFormApp.skipIfMax(this)" maxlength="5" placeholder="<?php echo _ExpirationDate ?>"
						   value="<?php echo Etictools::getValue('cc_expiry') ?>"/>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12">
				<img src="<?php echo plugins_url() ?>/sanalpospro/img/icons/cvv.svg"" alt="" srcset="">
					<input class="cc_input form-control input-lg border-0 shadow-none" type="text" id="cc_cvc" name="cc_cvv" oninput="sppFormApp.skipIfMax(this)" maxlength="3" placeholder="Cvv" 
						   value="<?php echo Etictools::getValue('cc_cvv') ?>"/>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12">
				<img src="<?php echo plugins_url() ?>/sanalpospro/img/icons/person.svg"" alt="" srcset="">
					<input class="cc_input form-control input-lg border-0 shadow-none" type="text" id="cc_name" name="cc_name" placeholder="<?php echo _CardHolderName ?>"
						   value="<?php echo Etictools::getValue('cc_name') ?>"/>
				</div>
		</div>
		<div class="" id="installment-table_div">
			<table class="my-3 border-0" style="display:none;" id="installment-table">
            <tbody><tr id="installment-titles">
              <th><?php echo _Installment ?></th>
              <th><?php echo _InstallmentAmount ?></th>
              <th><?php echo _Amount ?></th>
            </tr>
          </tbody></table>
		</div>
		<div class="mt-3">
		<button class="w-100" name="sanalpospro_submit" type="submit" id="cc_form_submit" style="background:<?= EticConfig::get("POSPRO_PAYMENT_PAGE_BUTTON_COLOR"); ?> !important;"><?php echo _CompletePayment ?></button>
	</div>
</form>
<!-- form tagine target="iframe-payment" -->
<!-- <iframe class="w-75 h-75 position-fixed mx-auto" style="top:0;bottom:0;left:0;right:0; " name="iframe-payment" src="<?php echo $order->get_checkout_payment_url(true);?>"></iframe> -->


						<div class="row">
							<a href="<?php echo $order->get_checkout_payment_url()?>" class="button_large"><?php echo _OtherPaymentMethod ?></a>
						</div>
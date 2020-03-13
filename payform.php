<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="spp_bootstrap-wrapper">


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

	<!-- <div id="spp_top">
		<div class="col-xs-12 col-lg-6" align="center">
			<h2><?php echo __('Kredi Kartı ile Güvenli Ödeme') ?></h2>
			<small>
				<?php echo __('Bu sayfa SSL şifreli bir form üzerinden güvenli bir şekilde kredi kartı ödemesi yapmanızı sağlar.') ?><br/>
				<?php echo __('3D Güvenli sayfaya yönlendirebilir ve SMS şifrenizi kullanabilirsiniz.') ?>
			</small>
		</div>
		<div class="col-xs-12 col-sm-6 hidden-md-down" align="center">
			<img class="img-responsive" src="<?php echo plugins_url() ?>/sanalpospro/img/safepayment.png"/>
		</div>
	</div> -->


	<hr/>
	<?php if ($mp): ?>
		<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"> -->

		<script type="text/javascript" src="<?php echo plugins_url() ?>/sanalpospro/views/js/mfs-client.min.js" ></script>
		<?php echo $mp->ui['forms']['js_init'] ?>
		<script type="text/javascript" src="<?php echo plugins_url() ?>/sanalpospro/views/js/masterpass.js" ></script>

		<form id="masterpass_payform" method="post" action="#">
			<div>
				<div>
					<div id="eticsoftMP_cardList_container" style="display:none">
						<div class="list-header">
							<img id="eticsoftMP_cardList_container_img" src="<?php echo plugins_url() ?>/sanalpospro/img/masterpass.svg" class="img-responsive">
							<span class="btn btn-info pull-right" id="usesavedcard" style="display:none">
								<?php echo __('Use a saved card') ?>
							</span>
						</div>
						<ul class="" id="eticsoftMP_cardList">
						</ul>
						<div class="eticsoftMP_cartitem2">
							<div class="col-md-6">
								<a id="usenewcard" class="btn btn-info" style="color:#fff" ><span class="glyphicon glyphicon-plus"></span> <?php echo __('Use another credit card') ?></a>
							</div>
							<div class="col-md-6">
								<a class="btn btn-warning" style="text-align:right; color:#fff" id="emp_delete_cc" > <?php echo __('Delete Selected Card') ?></a>
							</div>
						</div>
					</div>
				</div>
		</form>
		<div>
			<div id="mp_tx_selected_holder" style="display:none">
				<div id="eticsoftMP_scard_display">
					<select class="form-control input-lg" name="cc_installment" id="mp_installment_select">
						<option value="1"> Tek Çekim </option>
					</select>
				</div>

				<hr/>
				<small><?php echo __('The amount will be charged your credit card is :') ?></small>
				<div id="eticsoftMP_totalToPay"></div>
				<hr/>
				<!-- <input name="cc_family" id="mp_tx_selected_holder_family" type="hidden"/> -->
				<input id="mp_tx_selected_holder_cc_id" name="cc_id" type="hidden"/>
				<input id="mp_tx_selected_holder_cc_name" name="cc_name" type="hidden"/>
				<input id="mp_tx_selected_holder_cc_number" name="cc_number" type="hidden"/>
				<input id="mp_tx_selected_holder_cc_expiry" name="cc_expiry" type="hidden"/>
				<div id="mp_tx_selected_holder_total_pay">
					<div id="emp_form_cardcvv">
						<input type="text" size="3" style="font-size:1.5em" name="cc_cvv" placeholder="CVV" id="emp_cc_cvv"/>
						<img width="80px" src="<?php echo plugins_url() ?>/sanalpospro/img/cvv.png" style="vertical-align: bottom;">
					</div>
					<br/>
				</div>
				<div class="text-center">
					<button type="submit" class="btn btn-info">Ödemeyi Tamamla </button>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>


<form class="spp-payment-page-row" novalidate action="<?php echo $order->get_checkout_payment_url(true);?>" autocomplete="on" method="POST" id="cc_form">
	<div style="width: 100%;">
		<ul style="display: flex;margin: 0px;">
		<a href="#cc_form"><li class="spp-payment-page-button">Kredi Kartı İle</li></a>
		<a href="<?php echo $order->get_checkout_payment_url()?>"><li class="spp-payment-page-button">Diğer Ödeme</li></a>
		</ul>
	</div>
	<div id="form_error">
	<?php if ($error_message) : ?>
		<div>
			<div class="alert-danger-spp" id="errDiv">
				<div class="spperror" id="errDiv">
					<?php echo __('Ödeme başarısız. Banka cevabınız:') ?> <br/><?php echo $error_message ?><br/>
					<?php echo __('Lütfen formu kontrol edip tekrar deneyin.') ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	</div>
	<div style="padding:15px;">
		<div class="tab" id="cc_form_table">
			<div>
				<div>
					<div class="spp-card-icon">
						<img id="card-icons" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjIxcHgiIGhlaWdodD0iMThweCIgdmlld0JveD0iMCAwIDIxIDE4IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IFNrZXRjaCAzOS4xICgzMTcyMCkgLSBodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2ggLS0+CiAgICA8dGl0bGU+Y2FyZDwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4KICAgIDxkZWZzPjwvZGVmcz4KICAgIDxnIGlkPSJQYWdlLTEiIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxnIGlkPSJjYXJkIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxLjAwMDAwMCwgMS4wMDAwMDApIiBzdHJva2U9IiNBQTkxQTQiIHN0cm9rZS13aWR0aD0iMiI+CiAgICAgICAgICAgIDxwb2x5bGluZSBpZD0iUmVjdGFuZ2xlIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHBvaW50cz0iMCAxNiAwIDAgMTkgMCAxOSAxNCA2LjAxNjgyMTI5IDE0Ij48L3BvbHlsaW5lPgogICAgICAgICAgICA8cGF0aCBkPSJNMC4wMzI3MTQ4NDM4LDUuNTgyMjc1MzkgTDE4LjE5NTE3NTksNS41ODIyNzUzOSIgaWQ9IlBhdGgtMiI+PC9wYXRoPgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+" class="">
					</div>
					<input type="tel" id="cc_number" name="cc_number" class="cc_input form-control input-lg" placeholder="Kart Numarası" maxlength="19" onkeypress="return isNumber(event)" onkeyup="keyup(), inputCheck(inputCcNumber,inputMonth,19)"
						   value=""/>
						   <!-- <?php if (Etictools::getValue('cc_number')): ?><?php echo Etictools::getValue('cc_number') ?><?php endif; ?> -->
				</div>
                <div style="width: 100%;">
				<div style="width: 60%;float: left;">
					<div class="spp-card-icon">
						<img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjIxcHgiIGhlaWdodD0iMjJweCIgdmlld0JveD0iMCAwIDIxIDIyIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IFNrZXRjaCAzOS4xICgzMTcyMCkgLSBodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2ggLS0+CiAgICA8dGl0bGU+R3JvdXA8L3RpdGxlPgogICAgPGRlc2M+Q3JlYXRlZCB3aXRoIFNrZXRjaC48L2Rlc2M+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPgogICAgICAgIDxnIGlkPSJHcm91cCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMS4wMDAwMDAsIDEuMDAwMDAwKSIgc3Ryb2tlPSIjQUE5MUE2IiBzdHJva2Utd2lkdGg9IjIiPgogICAgICAgICAgICA8cG9seWxpbmUgaWQ9IlJlY3RhbmdsZS0yIiBwb2ludHM9IjE1LjE5MTQwNjIgMiAxOSAyIDE5IDIwIDAgMjAgMCAyIDAgMiA0LjE2MzMzMDA4IDIiPjwvcG9seWxpbmU+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0xMiwwIEwxMiw1LjkwMjczMzk2IiBpZD0iUGF0aC0zIj48L3BhdGg+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik03LDAgTDcsNS45MDI3MzM5NiIgaWQ9IlBhdGgtMy1Db3B5Ij48L3BhdGg+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4=">
					</div>
					<input type="tel" id="cc_expiry" name="cc_expiry" maxlength="7" onkeyup="inputCheck(inputMonth,inputCvc,7), ccFormatExpiry(event)" class="cc_input form-control input-lg" placeholder="<?php echo __('Tarih') ?>"
						   value="<?php echo Etictools::getValue('cc_expiry') ?>"/>
				</div>
                <div style="width: 40%;float: left;">
				<div>
					<div class="spp-card-icon">
						<img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjIxcHgiIGhlaWdodD0iMjNweCIgdmlld0JveD0iMCAwIDIxIDIzIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IFNrZXRjaCAzOS4xICgzMTcyMCkgLSBodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2ggLS0+CiAgICA8dGl0bGU+R3JvdXAgMjwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4KICAgIDxkZWZzPjwvZGVmcz4KICAgIDxnIGlkPSJQYWdlLTEiIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxnIGlkPSJHcm91cC0yIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxLjAwMDAwMCwgMS4wMDAwMDApIiBzdHJva2U9IiNBQTkyQTIiIHN0cm9rZS13aWR0aD0iMiI+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0xNSw2IEMxNSwyLjY4NjI5MTUgMTIuMzEzNzA4NSwwIDksMCBDNS42ODYyOTE1LDAgMywyLjY4NjI5MTUgMyw2IiBpZD0iT3ZhbC0yIj48L3BhdGg+CiAgICAgICAgICAgIDxwb2x5bGluZSBpZD0iUmVjdGFuZ2xlLTMiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIgcG9pbnRzPSIwIDIxIDAgNyAwIDcgMTkgNyAxOSAyMSA2LjA0MzIxMjg5IDIxIj48L3BvbHlsaW5lPgogICAgICAgICAgICA8Y2lyY2xlIGlkPSJPdmFsIiBjeD0iMTAiIGN5PSIxNCIgcj0iMiI+PC9jaXJjbGU+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4=">
					</div>
					<input type="tel" id="cc_cvc" name="cc_cvv" maxlength="3" onkeyup="inputCheck(inputCvc,inputName,3)" class="cc_input form-control input-lg" placeholder="Cvv"
						   value="<?php echo Etictools::getValue('cc_cvv') ?>"/>
				</div>
			</div>
        </div>
            <div>
				<div class="spp-card-icon">
					<img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjE5cHgiIGhlaWdodD0iMjBweCIgdmlld0JveD0iMCAwIDE5IDIwIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IFNrZXRjaCAzOS4xICgzMTcyMCkgLSBodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2ggLS0+CiAgICA8dGl0bGU+Q29tYmluZWQgU2hhcGU8L3RpdGxlPgogICAgPGRlc2M+Q3JlYXRlZCB3aXRoIFNrZXRjaC48L2Rlc2M+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPgogICAgICAgIDxwYXRoIGQ9Ik0xLDE4LjYyNjQ2NDggQzEsMTguNjI2NDY0OCAxLjU1MTkzNDUzLDEzLjA3MzM4ODQgNi4yMDg3NTAyOCwxMS4xMDcxOTQzIEM0LjcwNjUwODA2LDEwLjEyNTMzMjkgMy43MTM4NjcxOSw4LjQyODU4ODMxIDMuNzEzODY3MTksNi41IEMzLjcxMzg2NzE5LDMuNDYyNDMzODggNi4xNzYzMDEwNiwxIDkuMjEzODY3MTksMSBDMTIuMjUxNDMzMywxIDE0LjcxMzg2NzIsMy40NjI0MzM4OCAxNC43MTM4NjcyLDYuNSBDMTQuNzEzODY3Miw4LjI2MTMyMDQzIDEzLjg4NTk0NDQsOS44MjkyNjkxMyAxMi41OTgwNDA3LDEwLjgzNTkwNDIgQzE3LjgzODUwNTgsMTIuNTM1NzE4NiAxNy44ODM1MTA2LDE4Ljk1ODAwNzggMTcuODgzNTEwNiwxOC45NTgwMDc4IEw2LjY5MTQwNjI1LDE4Ljk1ODAwNzgiIGlkPSJDb21iaW5lZC1TaGFwZSIgc3Ryb2tlPSIjQUI5MkE1IiBzdHJva2Utd2lkdGg9IjIiPjwvcGF0aD4KICAgIDwvZz4KPC9zdmc+">
			   </div>
					<input type="text" id="cc_name" name="cc_name" class="cc_input form-control input-lg" placeholder="<?php echo __('Kart Sahibinin Adı') ?>"
						   value="<?php echo Etictools::getValue('cc_name') ?>"/>
						   <input name="cc_family" id="mp_tx_selected_holder_family" type="hidden"/>
				</div>
			</div>
				<!-- <div class="col-xs-12 col-sm-4">
					<select name="cc_family" id="tx_bank_selector" class="form-control" data-no-uniform="true">
						<option value="all"><?php echo __('Tüm Kartlar') ?></option>
						<?php foreach ($cards as $family => $rate): ?>
							<option value = "<?php echo $family ?>"><?php echo ucfirst($family) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-xs-12 col-sm-8">
					<div id="tx_selected_holder" class="pull-left">

					</div>
				</div>
			</div> -->
		<!-- <div class="tab" id="digerodeme">
			<p>diğer ödeme yöntemleri</p>
		</div> -->

		<!-- <div class="col-xs-12 col-md-6 hidden-md-down">
			<div id="card-wrapper"></div>
			<div id="prefix_bank_logo" align="center"></div>
			<div id="prefix_bank" align="center"></div>
		</div> -->
	</div>
	<div id="add">
		<table id="installment-table">
            <tbody><tr id="installment-titles">
              <th>TAKSİT</th>
              <th>T.TUTARI</th>
              <th>TUTAR</th>
            </tr>
          </tbody></table>
		</div>
		<div class="loader loader--style2" id="installment-loading" title="loading">
						<svg xmlns="http://www.w3.org/2000/svg" style="margin:auto;margin-top:15px;" width="38" height="38" viewBox="0 0 38 38" stroke="#fff" style="&#10;">
					<g fill="none" fill-rule="evenodd">
						<g transform="translate(1 1)" stroke-width="2">
							<circle stroke-opacity=".5" cx="18" cy="18" r="18"/>
							<path style="transition:none !important;" d="M36 18c0-9.94-8.06-18-18-18" transform="rotate(217.215 18 18)">
								<animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"/>
							</path>
						</g>
					</g>
				</svg>
		</div>
			<button name="sanalpospro_submit" type="submit" value="1" id="cc_form_submit" align="center" class="btn btn-lg btn-primary"><?php echo __('ÖDEMEYİ ONAYLA') ?></button>
	<script type="text/javascript" src="<?php echo plugins_url() ?>/sanalpospro/views/js/admin.js" ></script>


	<?php if ($mp): ?>
		<div class = "text-center" id = "mp_register_container" style = "text-align:center">
			<?php echo $mp->ui['forms']['registerMPcheck'] ?>
			<?php echo $mp->ui['forms']['registerMPcontainer'] ?>
		</div>
	<?php endif; ?>
	</div>
</form>

<?php foreach ($cards as $family => $rate): ?>
	<div class = "tx_banka" style = "display:none" id = "tx_banka_<?php echo $family ?>">
		<select style = "min-width:200px" class = "cc_installment_select cc_input form-control" name="cc_installment" id = "tx_inst_<?php echo $fam ?>">
			<?php foreach ($rate as $div => $installment): ?>
				<option value = "<?php echo $div ?>" dataamount = "<?php echo $installment['total'] ?>">
					<?php if ($div == 1) : ?>
						<?php echo __('Tek çekim ödeme') ?>
						<?php if ($installment['rate'] == 0): ?> <?php echo __('Komisyonsuz') ?>
						<?php else: ?> <?php echo $installment['total'] . ' ' . $currency->sign ?>
						<?php endif; ?>
						<?php else: ?>
						<?php echo $div ?> <?php echo __('Taksit') ?> X <?php echo $installment['month'] ?>
						<?php if ($installment['rate'] == 0): ?> <?php echo __('Komisyonsuz') ?>
						<?php else: ?> <?php echo __('Total') ?> <?php echo $installment['total'] . ' ' . $currency->sign ?> <?php endif; ?>
					</option>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
	</div>
<?php endforeach; ?>
<div class="tx_banka" style="display:none" id="tx_banka_all">
	<select style="min-width:200px"  class="cc_installment_select cc_input form-control" name="cc_installment" id="tx_inst_all">
		<option value="1" dataamount="<?php echo $defaultins['total'] ?>"> <?php echo __('Tek çekim ödeme') ?></option>
	</select>
</div>

<?php if ($mp): ?>
	<div class = "modal fade emp_modal" id = "eticsoftMP_loader" role = "dialog">
		<div class = "modal-dialog">
			<div class = "modal-content">
				<div class = "modal-body">
					<h2 align = "center">Lütfen Bekleyin<h2>
							<div id = "eticsoftMP_loaderImg"></div>
							</div>
							</div>
							</div>
							</div>

							<div class = "modal fade emp_modal" id = "eticsoftMP_message_panel" role = "dialog">
								<div class = "modal-dialog">
									<div class = "modal-content">
										<div class = "modal-header">
											<button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
												<span aria-hidden = "true">X</span>
											</button>
											<img src = "<?php echo plugins_url() ?>/sanalpospro/img/masterpass.svg" class = "img-responsive">
										</div>
										<div class = "modal-body">
											<h2 align = "center" id = "eticsoftMP_message_title">Bir hata oluştu</h2>
											<div id = "eticsoftMP_message_text" class = "alert alert-warning"></div>
										</div>
									</div>
								</div>
							</div>
							<div id = "eticsoftMP_container" style = "font-size:.8em">
								<?php echo $mp->ui['forms']['checkMP'] ?>
								<?php echo $mp->ui['forms']['otp'] ?>
								<?php echo $mp->ui['forms']['mpin'] ?>
								<?php echo $mp->ui['forms']['linkCardtoClient'] ?>
								<?php echo $mp->ui['forms']['tos'] ?>
							</div>
						<?php endif; ?>
						<div>
						</div>
						</div>

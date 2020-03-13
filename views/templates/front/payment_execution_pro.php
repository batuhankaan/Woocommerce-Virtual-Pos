<script>
	if(!window.jQuery){
		console.log("adding Jquery by Payment pro.tpl");
		var s = document.createElement('script');
		s.src = "https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js";
		document.head.appendChild(s);
	}
	
	{if	$currency->id != $currency_default AND $c_auto_currency eq "on"}
	setCurrency({$currency_default});
	alert('Seçtiğiniz para birimi bu ödeme yönteminde kullanılamıyor. Kurunuz {$curname->name} olarak değiştrildi.')
	{/if}
	var protrccname = '{l s="Your Name" mod="sanalpospro"}';
	var currency_sign = "{$currency->sign}";
	var card = new Array();
	var cards = new Array();
	var sanalposprouri = "{$sanalpospro_uri}/";
	var defaultins = "{$defaultins.total}";
	{foreach from=$cards item=frates key=family}
	cards ['{$family}'] = new Array();
		{foreach from=$frates item=ins key=divisor}
			{if $c_min_inst_amount < $ins.month}
	cards["{$family}"]["{$divisor}"] = "{$ins.total}";
			{/if}
		{/foreach}
	{/foreach}
	var sanalpospro_uri = "{$sanalpospro_uri}";
</script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <div class="row" id="spp_top">
        <div class="col-xs-12 col-lg-6" align="center">
            <h2>{l s='Secure Payment With Credit Card' mod='sanalpospro'}</h2>
            <small>
                {l s='This page allows you make a secure credit card payment via an SSL encrypted form' mod='sanalpospro'}<br/>
                {l s='You may redirect to the 3D Secure page and use your SMS password.' mod='sanalpospro'}
				{l s='Total to Pay is ' mod='sanalpospro'}  
            </small><span class="price" id="spr_total_to_pay"></span> {$curname->sign nofilter}
			<hr/>
            <span class="price" id="total_to_pay"></span>
        </div>
        <div class="col-xs-12 col-sm-6 hidden-md-down" align="center">
            <img class="img-responsive" src="{$sanalpospro_uri}/img/safepayment.png"/>
        </div>
    </div>

    {if $errBanka == '1'}
        <div class="row">
            <div class="alert alert-danger" id="errDiv">
                <div class="spperror" id="errDiv">
                    {l s='Payment Failed. Your bank answer:' mod='sanalpospro'} <br/>{$errmsg}<br/>
                    {l s='Please check the form and try again.' mod='sanalpospro'}
                </div>
            </div>
        </div>
    {/if}
    <hr/>
	{if $mp}
	<script type="text/javascript" src="{$sanalpospro_uri}/views/js/mfs-client.min.js" ></script>
	{$mp->ui.forms.js_init nofilter}
	<script type="text/javascript" src="{$sanalpospro_uri}/views/js/masterpass.js" ></script>

	<form id="masterpass_payform" method="post" action="#">
	<div class="row">
	<div class="col-lg-6 col-md-12">
		<div id="eticsoftMP_cardList_container" style="display:none">
			<div class="list-header">
				<img id="eticsoftMP_cardList_container_img" src="{$sanalpospro_uri}/img/masterpass.svg" class="img-responsive">
				<span class="btn btn-info pull-right" id="usesavedcard" style="display:none">
				{l s='Use a saved card' mod='sanalpospro'}
				</span>
			</div>
			<ul class="" id="eticsoftMP_cardList">
			</ul>
			<div class="eticsoftMP_cartitem2 row">
				<div class="col-md-6">
					<a id="usenewcard" class="btn btn-info" style="color:#fff" ><span class="glyphicon glyphicon-plus"></span> {l s='Use another credit card' mod='sanalpospro'}</a>
				</div>
				<div class="col-md-6">
					<a class="btn btn-warning" style="text-align:right; color:#fff" id="emp_delete_cc" > {l s='Delete Selected Card' mod='sanalpospro'}</a>
				</div>
			</div>
		</div>
	</div>
	</form>
	<div class="col-lg-6 col-md-12">
		<div id="mp_tx_selected_holder" style="display:none">
			<div id="eticsoftMP_scard_display">	
				<select class="form-control input-lg" name="cc_installment" id="mp_installment_select">
				<option value="1"> Tek Çekim </option>
				</select>
			</div>

			<hr/>
			<small>{l s='The amount will be charged your credit card is :' mod='sanalpospro'}</small>
			<div id="eticsoftMP_totalToPay"></div>
			<hr/>
			<input name="cc_family" id="mp_tx_selected_holder_family" type="hidden"/>
			<input id="mp_tx_selected_holder_cc_id" name="cc_id" type="hidden"/>
			<input id="mp_tx_selected_holder_cc_name" name="cc_name" type="hidden"/>
			<input id="mp_tx_selected_holder_cc_number" name="cc_number" type="hidden"/>
			<input id="mp_tx_selected_holder_cc_expiry" name="cc_expiry" type="hidden"/>
			<div id="mp_tx_selected_holder_total_pay"> 
				<div id="emp_form_cardcvv">
					<input type="text" size="3" style="font-size:1.5em" name="cc_cvv" placeholder="CVV" id="emp_cc_cvv"/>
					<img width="80px" src="{$sanalpospro_uri}/img/cvv.png" style="vertical-align: bottom;">
				</div>
				<br/>
			</div>
			<div class="text-center">
				<button type="submit" class="btn btn-info">Ödemeyi Tamamla </button>
			</div>
		</div>
	</div>
	</div>
	{/if}


    <form novalidate action="{$form_action}" autocomplete="on" method="POST" id="cc_form">
        <div class="row">
            <div class="col-xs-12 col-md-12 col-lg-6" align="center" id="cc_form_table">
                <div class="row" >
                    <div class="col-xs-12 col-sm-6 col-md-6">
                        {l s='Card Number' mod='sanalpospro'}<br/>
                        <input type="text" id="cc_number" name="cc_number" class="cc_input form-control input-lg" placeholder="•••• •••• •••• ••••" value="{if isset($smarty.post.cc_number)}{$smarty.post.cc_number}{/if}"/>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-6">
                        {l s='Card Expity Date' mod='sanalpospro'}<br/>
                        <input type="text" id="cc_expiry" name="cc_expiry" class="cc_input form-control input-lg" placeholder="{l s='MM/YY' mod='sanalpospro'}" value="{if isset($smarty.post.cc_expiry)}{$smarty.post.cc_expiry}{/if}"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-6 col-md-6">
                        {l s='Card CVC' mod='sanalpospro'} <br/>
                        <input type="text" id="cc_cvc" name="cc_cvv" class="cc_input form-control input-lg" placeholder="•••" value="{if isset($smarty.post.cc_cvc)}{$smarty.post.cc_cvc}{/if}"/>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-6">
                        {l s='Name On Card' mod='sanalpospro'}<br/>
                        <input type="text" id="cc_name" name="cc_name" class="cc_input form-control input-lg" placeholder="{l s='Your Name' mod='sanalpospro'}" value="{if isset($smarty.post.cc_name)}{$smarty.post.cc_name}{/if}"/>
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-xs-12 col-sm-4">
                        <select name="cc_family" id="tx_bank_selector" class="input-lg form-control" data-no-uniform="true">
                            <option value="all">{l s='All Card Types' mod='sanalpospro' }</option>
                            {foreach from=$cards key=family item=frates}
                                <option value="{$family}">{$family|@ucfirst}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-8">
                        <div id="tx_selected_holder" class="pull-left">

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-6 hidden-md-down">
                <div id="card-wrapper"></div>
                <div id="prefix_bank_logo" align="center"></div>
                <div id="prefix_bank" align="center"></div>
            </div>
        </div>

        <div class="row text-center{if $mp} {/if}" style="text-align:center">
            <hr/>
            <button name="sanalpospro_submit" type="submit" id="cc_form_submit" align="center" class="btn btn-lg btn-primary">{l s='Pay Now' mod='sanalpospro'}</button>
        </div>
		{if $mp}
        <div class="row text-center" id="mp_register_container" style="text-align:center">
			{$mp->ui.forms.registerMPcheck nofilter}
			{$mp->ui.forms.registerMPcontainer nofilter}
		</div>
		{/if}
    </form>

    {foreach from=$cards item=family key=fam}
	<div class="tx_banka" style="display:none" id="tx_banka_{$fam}">
		<select style="min-width:200px" class="cc_installment_select input-lg cc_input form-control" name="cc_installment" id="tx_inst_{$fam}">
			{foreach from=$family key=divisor item=ins}
				<option value="{$divisor}" dataamount="{$ins.total}">
					{if $divisor == 1}
						{l s='One\'s Way' mod='sanalpospro' } {if $ins.rate eq 0.00}{l s='No Fees' mod='sanalpospro'}{else}{$ins.total} {$currency->sign}{/if}
					{else}
						{$divisor} {l s='Ins.' mod='sanalpospro'} X {$ins.month} {if $ins.rate eq 0.00}{l s='No Fees' mod='sanalpospro'}{else} {l s='Total' mod='sanalpospro'} {$ins.total} {$currency->sign}{/if}
					</option>
				{/if}
			{/foreach}
		</select>
	</div>
    {/foreach}
    <div class="tx_banka" style="display:none" id="tx_banka_all">
        <select style="min-width:200px"  class="cc_installment_select input-lg cc_input form-control" name="cc_installment" id="tx_inst_all">
            <option value="1" dataamount="{$defaultins.total}"> {l s='Pay one\'s way' mod='sanalpospro'}</option>
        </select>
    </div>
    <br/>
    <br/>
    <hr/>
	{if $mp}
	<div class="modal fade emp_modal" id="eticsoftMP_loader" role="dialog">
		<div class="modal-dialog">
		  <div class="modal-content">
			<div class="modal-body">
			<h2 align="center">Lütfen Bekleyin<h2>
			<div id="eticsoftMP_loaderImg"></div>
			</div>
		  </div>
		</div>
	</div>
	
	<div class="modal fade emp_modal" id="eticsoftMP_message_panel" role="dialog">
		<div class="modal-dialog">
		  <div class="modal-content">
			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">X</span>
			  </button>
			  <img src="{$sanalpospro_uri}/img/masterpass.svg" class="img-responsive">		  
			</div>
			<div class="modal-body">
			<h2 align="center" id="eticsoftMP_message_title">Bir hata oluştu</h2>
			<div id="eticsoftMP_message_text" class="alert alert-warning"></div>
			</div>
		  </div>
		</div>
	</div>
	<div id="eticsoftMP_container" style="font-size:.8em">
			{$mp->ui.forms.checkMP nofilter}
			{$mp->ui.forms.otp nofilter}
			{$mp->ui.forms.mpin nofilter}
			{$mp->ui.forms.linkCardtoClient nofilter}
			{$mp->ui.forms.tos nofilter}
	</div>
	{/if}
    <div class="row">
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other Payment Methods' mod='sanalpospro'}</a>
    </div>
	var last_token = '';
	var referenceNo = '';
	var interval;
	var sendSms = 'Y';
    var pinType = 'otp';
	var external_data;
	var internal_data;
	var mp_cards = false;
	var mp_c_hasaccount;
	var mp_c_hascards;
	var mp_c_isrelated;
	var mp_c_islocked;
	var mp_c_numberchanged;
	var mp_selected_card;
	var mp_selected_famiy;
	var current_action = "validate";

jQuery(function ($) {
	// if($("form#eticsoftMP_checkMP input[name=userId]").val() == '00') {
		// mp_showErrors('Lütfen bir cep telefonu numarası giriniz.'
		// +'<input type="form-control" name="new_phone" /><br/><hr/>'
		// +'<button type="submit" class="btn btn-info">Kaydet</button>'
		// );
		// return false;
	// }
	if(referenceNo == '')
		referenceNo = $("form#eticsoftMP_checkMP input[name=referenceNo]").val();
	if(emp_msisdn){
		mp_showProgress();
		mp_debuger('started');
		getTokenSS('mp_setInternal');
		MFS.checkMasterPass($(this), MP_checkResponseHandler);
	}
	else {
		$("#eticsoftMP_registerMPcheck").show(300);
	}
	//mp_hideProgress();
	
	$('#eticsoftMP_otpform').submit(function(event) {
		mp_debuger('f -> eticsoftMP_otpform submit');
		event.preventDefault();
		mp_hidePanels();
		mp_showProgress();
		MFS.validateTransaction($(this), MP_validateTransactionResponseHandler);
		return true;
	});
	
	$('#eticsoftMP_mpinform').submit(function(event) {
		mp_debuger('f -> eticsoftMP_mpinform submit');
		event.preventDefault();
		mp_hidePanels();
		mp_showProgress();
		$(this).hide();
		MFS.validateTransaction($(this), MP_validateTransactionResponseHandler);
		return false;
	});
	
	$("a#emp_resendsms").click(function() {
		mp_debuger('f -> emp_resendsms click');
		clearInterval(interval);
		var token = MFS.getLastToken();
		var lang = "tur";
		MFS.resendOtp(token, lang, MP_responseHandler);
		mp_hidePanels();
		otpModalShow();
		return false;
	});
	
	$(document).on("click", 'li.eticsoftMP_cartitem', function() {
		mp_debuger('f -> eticsoftMP_cartitem click');
		$('input#mp_tx_selected_holder_cc_id').val($(this).attr("id"));
		$("#mp_tx_selected_holder_cc_id").change();
		$("li.eticsoftMP_cartitem").removeClass('eticsoftMP_selectedli');
		$(this).addClass('eticsoftMP_selectedli');
	});
	
	$(document).on("change", '#mp_installment_select', function() {
		mp_debuger('f -> mp_installment_select change');
		$("#eticsoftMP_totalToPay").html($("#mp_installment_select option:selected").attr('dataamount')+' '+currency_sign);
		console.log($("#mp_installment_select option:selected").attr('dataamount'));
	});
	
	$(document).on("change", 'input#mp_tx_selected_holder_cc_id', function () {                
		mp_debuger('f -> mp_tx_selected_holder_cc_id change');
		mp_selected_card = mp_cards[$(this).val()];
		mp_selected_famiy = mp_selected_card.ProductName.toLowerCase();

		if (typeof cards[mp_selected_famiy] == 'undefined' ) {
			var bin = mp_selected_card.Value1.slice(0, 6);
			return emp_getBin(bin);
		}
		return emp_setInstallmentOptions('mp_selected_famiy');
	});
	
	function emp_getBin (bin) {
		mp_debuger('f -> emp_getBin '+bin);
		$.ajax({
			type: "GET",
			cache:true,
			url: "https://bin.sanalpospro.com/?cc=" + bin,
			success: function (data) {
				console.log('Calling BIN service');
				if(data.family !== 'undefined')
					return emp_setInstallmentOptions(data.family);
				return emp_setInstallmentOptions("all");
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console_log(jqXHR.status);
				return emp_setInstallmentOptions("all");

			},
			dataType: "jsonp"
		});
	}
	
	function emp_setInstallmentOptions (emp_selected_card_family){
		mp_debuger('f -> emp_setInstallmentOptions '+emp_selected_card_family);
		mp_selected_card_id = $("input#mp_tx_selected_holder_cc_id").val();
		mp_selected_card = mp_cards[mp_selected_card_id];
		mp_selected_famiy = emp_selected_card_family;

		var fam_image = (typeof cards[mp_selected_famiy] !== 'undefined' ) ?  mp_selected_famiy : 'default';
		$("#mp_card_list_"+mp_selected_card_id).attr('src', sanalposprouri+"/img/cards/"+fam_image+".png");
		$("a#emp_delete_cc").attr('onclick', "mp_deleteCard('"+mp_selected_card.Name+"')");
		//console.log(cards[mp_selected_famiy]);
		
		$("form#cc_form").hide(300);
		$("#mp_tx_selected_holder").show();	
		$("input#mp_tx_selected_holder_family").val('all');
		$("select#mp_installment_select").html(" ");
		$("#mp_tx_selected_holder_cc_number").val(mp_selected_card.Value1);
		if (typeof cards[mp_selected_famiy] !== 'undefined' ) {
			$("#mp_tx_selected_holder_family").val(mp_selected_famiy);
			$("select#mp_installment_select").html();
			$.each(cards[mp_selected_famiy], function( index, value ) {
			$("select#mp_installment_select").html();
				if(index > 0)
					$("select#mp_installment_select").append('<option dataamount="'+value+'" value="'+index+'">'
				+index+' '+emp_lang.installments +' '+ emp_lang.total +' '+value+' '+currency_sign+'</option>');
			});
		}
		else {
			$("select#mp_installment_select").html('<option value="1" dataamount="'+defaultins+'" selected="1">'+emp_lang.one_shot+'</option>');
		}
		$("select#mp_installment_select").change();
		
		
	}
	
	$(document).on('change', '#eticsoftMP_register', function() {
		mp_debuger('f -> eticsoftMP_register change ');
		if($(this).is(":checked")){
			$("#eticsoftMP_registerMPcontainer").show();
			$("#mp_register_container").addClass("mp_register_container");
		}
		else {
			$("#eticsoftMP_registerMPcontainer").hide();
			$("#mp_register_container").removeClass("mp_register_container");
		}
	});
	
	$(document).on('click', '#usenewcard', function() {
		mp_debuger('f -> usenewcard click');
		$("#eticsoftMP_cardList_container_img").attr("width", "125px");
		$("form#cc_form").fadeIn(300);	
		$(".eticsoftMP_cartitem").fadeOut(300);	
		$(".eticsoftMP_cartitem2").fadeOut(300);	
		$("#mp_tx_selected_holder").fadeOut(300);	
		$("#usesavedcard").fadeIn();	
	});
	
	$(document).on('click', '#usesavedcard', function() {
		mp_debuger('f -> usesavedcard click');
		$("#eticsoftMP_cardList_container_img").attr("width", "250px");
		$(".eticsoftMP_cartitem").fadeIn(300);	
		$(".eticsoftMP_cartitem2").fadeIn(300);	
		$("#usesavedcard").fadeOut();	
		$("#mp_tx_selected_holder").fadeIn();	
		$("form#cc_form").fadeOut(300);	
		//$('input:radio[name=eticsoftMP_selectedcard][disabled=false]:first').attr('checked', true);
		$('li.eticsoftMP_cartitem:first').click();
	});
	
	$("form#masterpass_payform").submit(function(event) {
		mp_debuger('f -> masterpass_payform submit');
		event.preventDefault();
		mp_hidePanels();
		mp_showProgress();
		createForm('purchase');
		getTokenSSPurchase('purchase');
		mp_hideProgress();
		return false;
	});
	
	$('form#cc_form').submit(function(event) {
		mp_debuger('f -> cc_form submit');
		mp_hidePanels();
		mp_showProgress();
		event.preventDefault();
		$("form#eticsoftMP_checkMP input[name=userId]").val($("#mp_accountnewPhone").val());
		
		var formatedPhone = emp_formatPhone($("form#eticsoftMP_checkMP input[name=userId]").val());
		if($('input#eticsoftMP_register').is(":checked")){
			if($("#mp_accountAliasName").val() === '')
				return mp_showErrors(emp_lang.please_insert_your_card_name);
			if(emp_isMobile(formatedPhone) === false)
				return mp_showErrors(emp_lang.please_insert_your_phone_for_register);
			if(mp_c_hasaccount === '1'){
				getTokenSS('mp_registerpurchase');
				return false;
			}
			else {
				console.log($("form#eticsoftMP_checkMP input[name=userId]").val());
				getTokenSS('mp_registerpurchase');
				return false;
			}
		}
		else {
			getTokenSS('mp_directpurchase');
			return false;
		}
	});	
});


function validateOTP(result){
	mp_debuger('f -> validateOTP');
}


function MP_validateTransactionResponseHandler(status, response)
{
	mp_debuger('f -> MP_validateTransactionResponseHandler :'+ response.responseCode);
	external_data = response;
	mp_hideProgress();
	if (response.responseCode == "0000" || response.responseCode == "") { // Register Success
		last_token = response.token;
		if(current_action === 'purchase')
			return mp_apiPayment(last_token);
		return MFS.checkMasterPass(jQuery, MP_checkResponseHandler);
	} else {
		if (response.responseCode == "5008" ||response.responseCode == "5001") {
			otpModalShow();
		} else if (response.responseCode == "5010") { // Ask 3D Secure
			mp_showProgress();
			window.location.assign(response.url3D + "&returnUrl="+internal_data.transaction.mptd_url);
		} else if (response.responseCode == "5015") {
			$('#mpin-form').show();
			$('#mpin-define-label').show();
		} else {
			mp_showErrors('<div class="alert alert-error">'+response.responseDescription+'</div>');
		}
	}
}

function MP_responseHandler(status, response)
{
	mp_debuger('f -> MP_responseHandler :'+ response.responseCode);
	external_data = response;
	mp_hideProgress();
	if (response.responseCode == "0000" || response.responseCode == "") { // Register Success
		return true;
	} else {
		if (response.responseCode == "5008" ||response.responseCode == "5001") {
			otpModalShow();
		} else if (response.responseCode == "5010") { // Ask 3D Secure
			mp_showProgress();
			window.location.assign(response.url3D + "&returnUrl="+internal_data.transaction.mptd_url);
		} else if (response.responseCode == "5015") {
			$('#mpin-form').show();
			$('#mpin-define-label').show();
		} else {
			mp_showErrors('<div class="alert alert-error">'+response.responseDescription+'</div>');
		}
	}
}

function MP_purchaseResponseHandler(status, response)
{
	mp_debuger('f -> MP_purchaseResponseHandler :'+ response.responseCode);
	external_data = response;
	mp_hideProgress();
	current_action = 'purchase';
	if (response.responseCode == "0000" || response.responseCode == "") { // Register Success
		last_token = response.token;
		if(current_action === 'purchase')
			mp_apiPayment(last_token);
		return;
	} else {
		if (response.responseCode == "5008" || response.responseCode == "5001") {
			otpModalShow();
		} else if (response.responseCode == "5010") { // Ask 3D Secure
			mp_showProgress();
			window.location.assign(response.url3D + "&returnUrl="+internal_data.transaction.mptd_url);
		} else if (response.responseCode == "5015") {
			$('#mpin-form').show();
			$('#mpin-define-label').show();
		} else {
			mp_showErrors('<div class="alert alert-error">'+response.responseDescription+'</div>');
		}
	}
}

function MP_checkResponseHandler(status, mpcresponse) {
	mp_debuger('f -> MP_checkResponseHandler');
	external_data = mpcresponse;
	if (mpcresponse.responseCode == "0000" || mpcresponse.responseCode == "") {
		//alert(mpcresponse.accountStatus);
		Uicontroller(mpcresponse.accountStatus);
	}else {
		mp_showErrors(mpcresponse.responseDescription);
	}
	mp_hideProgress();
}


function MP_updateUserIDResponseHandler(statusCode, response)
{
	mp_debuger('f -> MP_updateUserIDResponseHandler '+response.responseCode);
	if (response.responseCode != "0000" && response.responseCode != "") {
		alert(response.responseDescription);
		if (response.responseCode == "5008" || response.responseCode == "5001") {
			mp_hideProgress();
			mp_hidePanels();
			otpModalShow();
		}
    }
	else {
		listCards();
	}

}

function MP_deleteCardResponseHandler(statusCode, response)
{
	mp_debuger('f -> MP_deleteCardResponseHandler ' +response.responseCode);
	if (response.responseCode == "0000" || response.responseCode == "") { // Success
		mp_showErrors('Sayfa yeniden yükleniyor', 'Lütfen Bekleyin !');
		location.reload();
		MFS.checkMasterPass(jQuery, MP_checkResponseHandler);
    }
	else if (response.responseCode == "5001" || response.responseCode == "5008" || response.responseCode == "5002" || response.responseCode == "5015")
        otpModalShow(response.responseCode);
    else if (response.responseCode == "5010")
		window.location.assign(response.url3D + "&returnUrl="+internal_data.transaction.mptd_url);
    else{
		mp_hideProgress();
		if(response.responseDescription){
			mp_showErrors(response.responseDescription);
		}
		else {
			mp_showErrors('Sayfa yeniden yükleniyor');
			location.reload();
		}
	}
}

function MP_linkCardResponseHandler(statusCode, response)
{
	mp_debuger('f -> MP_linkCardResponseHandler ' +response.responseCode);
	if (response.responseCode == "0000" || response.responseCode == "") { // Success
		MFS.checkMasterPass(jQuery, MP_checkResponseHandler);
    }
	else if (response.responseCode == "5001" || response.responseCode == "5008" || response.responseCode == "5002" || response.responseCode == "5015")
        otpModalShow(response.responseCode);
    else if (response.responseCode == "5010")
		window.location.assign(response.url3D + "&returnUrl="+internal_data.transaction.mptd_url);
    else{
		mp_hideProgress();
		if(response.responseDescription){
			mp_showErrors(response.responseDescription);
		}
		else {
			mp_showErrors('Sayfa yeniden yükleniyor');
			location.reload();
		}
	}
}


function MP_listCardsResponseHandler(statusCode, response)
{
	mp_debuger('f -> MP_listCardsResponseHandler '+response.responseCode );
	mp_hideProgress();
	if (response.responseCode != "0000" && response.responseCode != "") {
        if (response.responseCode == "1078") {
            mp_openModal("updateUserID", 'Update your user id',
			'<button type="button" onclick="updateUserID()" class="btn btn-info">'+emp_lang.link_my_account+'</button>'
			);
        }
		return false;
	}
	
	mp_cards = response.cards;
	$("li#eticsoftMP_cartitem").remove();
	for (var i = 0; i < response.cards.length; i++) {
		var card = response.cards[i];
		var fam_image = (typeof cards[card.ProductName.toLowerCase()] !== 'undefined' ) ?  card.ProductName.toLowerCase() : 'default';
		
		var imgurl = sanalposprouri+"img/cards/"+fam_image+'.png';
		var fimage = $('<img src="'+imgurl+'" />');
		if (fimage.attr('width') === 0){
			console.log(imgurl+" not found");
			imgurl = sanalposprouri+'img/cards/default.png';
		}

		var t = '<li class="eticsoftMP_cartitem" id="'+i+'">'
		+'<div class="cardName">'+card.Name+' ('+card.ProductName+')</div>'
		+'<div class="cardNumber">'+card.Value1+'</div>'
		//+'<div class="cardcvv">cvv kodu: <input type="text" size="3" name="'+i+'_mp_cc_cvv" id="'+i+'_mp_cc_cvv"/> </div>'
		+'<div class="masterpassLogo"><img class="img-responsive" id="mp_card_list_'+i+'" alt="'+card.ProductName+'" src="'+imgurl+'"/></div>'
		+'</li>';
		$("ul#eticsoftMP_cardList").prepend(t);
		
	};
		$("#eticsoftMP_cardList_container").show(300);
		$('li.eticsoftMP_cartitem:first').click()
		//$("form#cc_form").hide(300);
	//alert(cards);
}


function listCards(){
	mp_debuger('f -> listCards');
	MFS.listCards($("form#eticsoftMP_checkMP input[name=userId]").val(),
		$("form#eticsoftMP_checkMP input[name=token]").val(),
		MP_listCardsResponseHandler);
	
}

function updateUserID () {
	mp_debuger('f -> updateUserID');
	createForm("updateUserID");
	var uu = 'eticsoftMP_updateUserID';
	setinput2form(uu, 'token', internal_data.token);
	setinput2form(uu, 'msisdn', internal_data.data.msisdn);
	setinput2form(uu, 'oldValue', 1);
	setinput2form(uu, 'theNewValue', internal_data.data.userId);
	setinput2form(uu, 'valueType', "USER_ID");
	setinput2form(uu, 'sendSmsLanguage', internal_data.data.sendSmsLanguage);
	setinput2form(uu, 'referenceNo', internal_data.data.referenceNo);
	setinput2form(uu, 'sendSms', internal_data.data.sendSms);
    MFS.updateUser($("#eticsoftMP_updateUserID"), MP_updateUserIDResponseHandler);
}

function mp_setInternal(data){
	mp_debuger('f -> mp_setInternal');
	internal_data = data;
}

function otpModalShow (statusCode = '5001') {
	mp_debuger('f -> otpModalShow');
	mp_hidePanels();
	//getTokenSS('otp');
	var vt = 'eticsoftMP_otpform';
	setinput2form(vt, 'referenceNo', external_data.referenceNo);
	setinput2form(vt, 'sendSms', internal_data.data.sendSms);
	setinput2form(vt, 'sendSmsLanguage', internal_data.data.sendSmsLanguage);
	setinput2form(vt, 'pinType', pinType);
	if(statusCode == '5001'){
		$("#emp_otp_title").text(emp_lang.card_validation);
		$("#emp_otp_description").text(emp_lang.type_bank_sms);
	}
	else {
		$("#emp_otp_title").text(emp_lang.phone_validation);
		$("#emp_otp_description").text(emp_lang.type_mp_sms);
	}
		
	//setinput2form(vt, 'token', internal_data.token);
	$('div#otpModal').modal('show');
	var counter = 180;
	clearInterval(interval);
	interval = setInterval(function() {
		$("span#otpCountersec").html(counter);
		if (counter == 0){
			$("span#otpCountersec").html(0);
			clearInterval(interval);
		}
		counter--;	
	}, 1000);
	$("input#otpValidationInput").val("");
	$("input#otpValidationInput").focus();
}


function Uicontroller(mp_accountStatus){
	mp_debuger('f -> Uicontroller' + mp_accountStatus);
	var res = mp_accountStatus.split("");
	
	mp_c_hasaccount = res[1];
	mp_c_hascards = res[2];
	mp_c_isrelated = res[3];
	mp_c_islocked = res[4];
	mp_c_numberchanged = res[5];
	mp_debuger('v -> mp_c_hasaccount '+ mp_c_hasaccount);
	mp_debuger('v -> mp_c_hascards '+ mp_c_hascards);
	mp_debuger('v -> mp_c_isrelated '+ mp_c_isrelated);
	mp_debuger('v -> mp_c_islocked '+ mp_c_islocked);
	mp_debuger('v -> mp_c_numberchanged '+ mp_c_numberchanged);
	
	$("#eticsoftMP_registerMPcheck").show();	
	// müşterinin kaydı yok
	
	if(mp_c_hasaccount === '0'){
		return;
	}
	else {
		if(mp_c_hascards === '1'){
			if( mp_c_isrelated === '1'){
			MFS.listCards($("form#eticsoftMP_checkMP input[name=userId]").val(),
				$("form#eticsoftMP_checkMP input[name=token]").val(),
				MP_listCardsResponseHandler);
			return;
			}
			else {
				$("#linkCardtoClient").modal("show");
				return;
			}
			
		}
	}
}

function createForm(formid, action = '#'){
	mp_debuger('f -> createForm '+formid);
	var currentformid = 'eticsoftMP_'+formid;
	if ($("form#"+currentformid).length <= 0){
		if(mp_debug_mode)
			$("div#eticsoftMP_container").append('<div class="panel"><form id="'+currentformid+'" method="post" action="'+action+'"><h3>'+formid+'</h3></form></div>');
		else
			$("div#eticsoftMP_container").append('<form id="'+currentformid+'" method="post" action="'+action+'"></form>');

	}
}

function mp_createModal(id, title, body = '') {
	mp_debuger('f -> mp_createModal');
	return ''
	+'<div class="modal fade emp_modal" id="'+id+'" role="dialog">'
		  +'<div class="modal-dialog">'
			+'<div class="modal-content">'
			  +'<div class="modal-header">'
			      +'<button type="button" class="close" data-dismiss="modal" aria-label="Close">'
				  +'<span aria-hidden="true">X</span>'
				  +'</button>'
				  +'<img src="'+sanalposprouri+'/img/masterpass.svg" class="img-responsive">'
			  +'</div>'
			  +'<div class="modal-body">'
					+'<legend>'+title+'</legend>'
					+'<div class="control-group">'
					+body
					+'</div>'
			  +'</div>'
			  +'<div class="modal-footer">'
			  +'</div>'
			+'</div>'
		  +'</div>'
		+'</div>';
}

function mp_openModal(id, title, body = ''){
	mp_debuger('f -> mp_openModal');
	$("div#eticsoftMP_container").append(mp_createModal(id, title, body));
	$("div#"+id).modal('show');
}

function mp_closeModal(id){
	mp_debuger('f -> mp_closeModal');
	$("div#"+id).modal('hide');
	//$("div#"+id).remove();
}

function setinput2form(formid, name, value){
	if ($("form#"+formid+" input[name="+name+"]").length > 0){
		$("form#"+formid+" input[name="+name+"]").val(value);
	}
	else {
		if(mp_debug_mode)
			$("form#"+formid).append(name+' <input name="'+name+'" value="'+value+'" /><br/>');
		else
			$("form#"+formid).append('<input name="'+name+'" type="hidden" value="'+value+'" />');
	}
}	

function mp_deleteCard(mp_s_cardName){
	mp_debuger('f -> mp_deleteCard'. mp_s_cardName);
	mp_showProgress();
	var msisdn = $("form#eticsoftMP_checkMP input[name=userId]").val();
	var token = $("form#eticsoftMP_checkMP input[name=token]").val();
	createForm('deletecard');
	var dc = 'eticsoftMP_deletecard';
	
	setinput2form(dc, 'msisdn', msisdn);
	setinput2form(dc, 'token', token);
	setinput2form(dc, 'clientId', internal_data.client.clientId);
	setinput2form(dc, 'programOwnerNumber', internal_data.client.programOwnerNumber);
	setinput2form(dc, 'programOwnerName', internal_data.client.programOwnerName);
	setinput2form(dc, 'programParticipantName', internal_data.client.programParticipantName);
	setinput2form(dc, 'programParticipantNumber', internal_data.client.programParticipantNumber);
	setinput2form(dc, 'programSponsorName', internal_data.client.programSponsorName);
	setinput2form(dc, 'programSponsorNumber', internal_data.client.programSponsorNumber);
	setinput2form(dc, 'actionType', "E");
	setinput2form(dc, 'clientIp', "");
	setinput2form(dc, 'delinkReason', 'User Request');
	setinput2form(dc, 'ActionType', 'E');
	setinput2form(dc, 'eActionType', 'D');
	setinput2form(dc, 'cardTypeFlag', '05');
	setinput2form(dc, 'cpinFlag', 'Y');
	setinput2form(dc, 'defaultAccount', 'Y');
	setinput2form(dc, 'mmrpConfig', '110010');
	setinput2form(dc, 'identityVerificationFlag', 'Y');
	setinput2form(dc, 'mno', internal_data.client.mnoId);
	setinput2form(dc, 'mobileAccountConfig', "MWA");
	setinput2form(dc, 'uiChannelType', 6);
	setinput2form(dc, 'referenceNo', internal_data.data.referenceNo);
	setinput2form(dc, 'sendSmsLanguage', 'tur');
	setinput2form(dc, 'sendSms', 'Y');
	setinput2form(dc, 'accountAliasName', mp_s_cardName);
	MFS.deleteCard($("#eticsoftMP_deletecard"), MP_deleteCardResponseHandler);
	
}

function mp_purchase (rd){
	mp_debuger('f -> purchase');
	var pf = 'eticsoftMP_purchase';
	var msisdn = $("form#eticsoftMP_checkMP input[name=userId]").val();
	var total_to_pay = $("#mp_installment_select option:selected").attr('dataamount');
	var installmentCount = $("select#mp_installment_select").val();
	var mp_selected_card = mp_cards[$("input#mp_tx_selected_holder_cc_id").val()];
	if(mp_debug_mode)
		$("div#eticsoftMP_container").append('<div class="panel">'+rd.debug_table+'</div>');
	setinput2form(pf, 'msisdn', msisdn);
	setinput2form(pf, 'amount', rd.order.orderTotal);
	setinput2form(pf, 'token', rd.token);
	setinput2form(pf, 'referenceNo',rd.data.referenceNo);
	setinput2form(pf, 'sendSmsLanguage', rd.data.sendSmsLanguage);
	setinput2form(pf, 'macroMerchantId', '');
	setinput2form(pf, 'orderNo', $("form#eticsoftMP_checkMP input[name=referenceNo]").val());
	setinput2form(pf, 'installmentCount', installmentCount);
	setinput2form(pf, 'sendSms', rd.data.sendSms);
	setinput2form(pf, 'aav', "");
	setinput2form(pf, 'clientIp', "");
	setinput2form(pf, 'encCPin', "0");
	setinput2form(pf, 'encPassword', "0");
	setinput2form(pf, 'sendSmsMerchant', "Y");
	setinput2form(pf, 'password', "");
	setinput2form(pf, 'listAccountName', mp_selected_card.Name);
	setinput2form(pf, 'cvc', $("#emp_cc_cvv").val());
	MFS.purchase($("#eticsoftMP_purchase"), MP_purchaseResponseHandler);
}

function mp_registerpurchase(rd){
	mp_debuger('f -> registerpurchase');
	createForm('registerpurchase');
	MFS.setAdditionalParameters(rd.additionalParameters);
	if(mp_debug_mode)
		$("div#eticsoftMP_container").append('<div class="panel">'+rd.debug_table+'</div>');
	var rp = 'eticsoftMP_registerpurchase';
	var accountAliasName = $("form#cc_form input[name=accountAliasName]").val();
	var msisdn = $("form#eticsoftMP_checkMP input[name=userId]").val();
	var installmentCount = $("form#cc_form select[name=cc_installment]").val();
	if(installmentCount == '1')
		installmentCount = '0';
	setinput2form(rp, 'msisdn', msisdn);
	setinput2form(rp, 'accountAliasName', accountAliasName);
	setinput2form(rp, 'rtaPan', emp_formatCcNo($("input#cc_number").val()));
	setinput2form(rp, 'expiryDate', emp_formatExDate($("input#cc_expiry").val()));
	setinput2form(rp, 'cvc', $("input#cc_cvc").val());
	setinput2form(rp, 'cardHolderName', $("input#cc_name").val());
	setinput2form(rp, 'amount', rd.order.orderTotal);
	setinput2form(rp, 'token', rd.token);
	setinput2form(rp, 'referenceNo',rd.data.referenceNo);
	setinput2form(rp, 'sendSmsLanguage', rd.data.sendSmsLanguage);
	setinput2form(rp, 'macroMerchantId', '');
	setinput2form(rp, 'orderNo', $("form#eticsoftMP_checkMP input[name=referenceNo]").val());
	setinput2form(rp, 'installmentCount', installmentCount);
	setinput2form(rp, 'sendSms', rd.data.sendSms);
	setinput2form(rp, 'actionType', "A");
	referenceNo = rd.data.referenceNo;
	MFS.purchaseAndRegister($("form#eticsoftMP_registerpurchase"), MP_purchaseResponseHandler);
}

function mp_directpurchase(rd) {
	mp_debuger('f -> mp_directpurchase');
	createForm('directpurchase');
	MFS.setAdditionalParameters(rd.additionalParameters);
	if(mp_debug_mode)
		$("div#eticsoftMP_container").append('<div class="panel">'+rd.debug_table+'</div>');
	var dp = 'eticsoftMP_directpurchase';
	var accountAliasName = $("form#cc_form input[name=accountAliasName]").val();
	//var msisdn = $("form#eticsoftMP_checkMP input[name=userId]").val();
	var installmentCount = $("form#cc_form select[name=cc_installment]").val();
	if(installmentCount == '1')
		installmentCount = '0';
	//setinput2form(dp, 'msisdn', msisdn);
	setinput2form(dp, 'accountAliasName', accountAliasName);
	setinput2form(dp, 'rtaPan', emp_formatCcNo($("input#cc_number").val()));
	setinput2form(dp, 'expiryDate', emp_formatExDate($("input#cc_expiry").val()));
	setinput2form(dp, 'cvc', $("input#cc_cvc").val());
	setinput2form(dp, 'cardHolderName', $("input#cc_name").val());
	setinput2form(dp, 'amount', rd.order.orderTotal);
	setinput2form(dp, 'token', rd.token);
	setinput2form(dp, 'referenceNo',rd.data.referenceNo);
	setinput2form(dp, 'sendSmsLanguage', rd.data.sendSmsLanguage);
	setinput2form(dp, 'macroMerchantId', '');
	setinput2form(dp, 'orderNo', $("form#eticsoftMP_checkMP input[name=referenceNo]").val());
	setinput2form(dp, 'installmentCount', installmentCount);
	setinput2form(dp, 'sendSms', rd.data.sendSms);
	setinput2form(dp, 'actionType', "A");
	referenceNo = rd.data.referenceNo;
	MFS.directPurchase($("form#eticsoftMP_directpurchase"), MP_purchaseResponseHandler);
}

function linkCardToClient(rd){
	mp_debuger('f -> mp_directpurchase');
	createForm('linkCardToClient');
	if(mp_debug_mode)
		$("div#eticsoftMP_container").append('<div class="panel">'+rd.debug_table+'</div>');
	var lc = 'eticsoftMP_linkCardToClient';
	var msisdn = $("form#eticsoftMP_checkMP input[name=userId]").val();
	
	setinput2form(lc, 'msisdn', msisdn);
	//setinput2form(lc, 'cardAliasName', "");
	setinput2form(lc, 'token', rd.token);
	setinput2form(lc, 'referenceNo', rd.data.referenceNo);
	setinput2form(lc, 'sendSms', rd.data.sendSms);
	setinput2form(lc, 'sendSmsLanguage', rd.data.sendSmsLanguage);
	MFS.linkCardToClient($("form#eticsoftMP_linkCardToClient"), MP_linkCardResponseHandler);
}

function register(rd){
	mp_debuger('f -> register');
	createForm('register');
	MFS.setAdditionalParameters(rd.additionalParameters);
	if(mp_debug_mode)
		$("div#eticsoftMP_container").append('<div class="panel">'+rd.debug_table+'</div>');
	var rp = 'eticsoftMP_register';
	var accountAliasName = $("form#cc_form input[name=accountAliasName]").val();
	var msisdn = $("form#eticsoftMP_checkMP input[name=userId]").val();
	var installmentCount = $("form#cc_form select[name=cc_installment]").val();
	if(installmentCount == '1')
		installmentCount = '0';
	setinput2form(rp, 'msisdn', msisdn);
	setinput2form(rp, 'accountAliasName', accountAliasName);
	setinput2form(rp, 'rtaPan', emp_formatCcNo($("input#cc_number").val()));
	setinput2form(rp, 'expiryDate', emp_formatExDate($("input#cc_expiry").val()));
	setinput2form(rp, 'cvc', $("input#cc_cvc").val());
	setinput2form(rp, 'cardHolderName', $("input#cc_name").val());
	setinput2form(rp, 'amount', rd.order.orderTotal);
	setinput2form(rp, 'token', rd.token);
	setinput2form(rp, 'referenceNo',rd.data.referenceNo);
	setinput2form(rp, 'sendSmsLanguage', rd.data.sendSmsLanguage);
	setinput2form(rp, 'macroMerchantId', '');
	setinput2form(rp, 'orderNo', $("form#eticsoftMP_checkMP input[name=referenceNo]").val());
	setinput2form(rp, 'installmentCount', installmentCount);
	setinput2form(rp, 'sendSms', rd.data.sendSms);
	setinput2form(rp, 'actionType', "A");
	referenceNo = rd.data.referenceNo;
	MFS.register($("form#eticsoftMP_register"), MP_responseHandler);
}

function getTokenSS(f_action){
	mp_debuger('f -> getTokenSS');
	$.ajax({
		type: "POST",
		url: mp_ssapi_url+'?a='+f_action.replace('mp_', '')+'&ref='+referenceNo,
        data: $("form#cc_form").serialize(),
		success: function (data) {
			internal_data = data;
			window[f_action](data);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log("failed");
			console.log(jqXHR.status);
			console.log(jqXHR);
			mp_hidePanels();
			mp_showErrors('İşlem şu anda tamamlanamıyor. Lütfen daha sonra tekrar deneyiniz.');
		},
		//dataType: "jsonp"
	});
	return;
}

function mp_apiPayment(token){
	mp_debuger('f -> mp_apiPayment ');
	mp_showProgress();
	$("#mp_n_form").remove();
	var n_form = $("#eticsoftMP_container").append('<form style="display:none" method="post" id="mp_n_form">'
	+'<input type="hidden" name="mp_api_token" value="'+token+'">'
	+'<input type="hidden" name="mp_api_refno" value="'+referenceNo+'">'
	+'</form>');
		var fields = $("form#cc_form").serializeArray();
    jQuery.each( fields, function( i, field ) {
		$("#mp_n_form").append('<input type="hidden" name="'+field.name+'" value="'+field.value+'" />');
    });
	$("#mp_n_form").submit();
	mp_hideProgress();

}


function getTokenSSPurchase(){
	mp_debuger('f -> getTokenSSPurchase');
	$.ajax({
		type: "POST",
		url: mp_ssapi_url+'?a=purchase&ref='+referenceNo,
        data: $("form#masterpass_payform").serialize(),
		success: function (data) {
			internal_data = data;
			mp_purchase(data);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log("failed");
			console.log(jqXHR.status);
			console.log(jqXHR);
			mp_hidePanels();
			mp_showErrors('İşlem şu anda tamamlanamıyor. Lütfen daha sonra tekrar deneyiniz.');
		},
		//dataType: "jsonp"
	});
	return;
}

function emp_formatExDate(exdate) {
    var res = exdate.split('/');
    var year = res[1].trim().slice(-2);
    var month = res[0];
    if(month.length == 1)
    	month = "0"+month.trim();
	var fexdate = year+month;
	return fexdate.replace(/\s+/g, '');
}

function emp_formatPhone(phone){
	return "90"+phone.replace(/[^0-9.]/g, "").slice(-10);
}

function emp_isMobile(phone){
	if(phone.slice(2, 3) === '5' && phone.length === 12 )
		return true;
	return false;
}

function emp_formatCcNo(ccno) {
    return ccno.replace(/\s+/g, '');
}

function mp_showProgress(){
	mp_debuger('f -> mp_showProgress');
	//$("#eticsoftMP_loader").modal('show');
}

function mp_hideProgress(){
	mp_debuger('f -> mp_hideProgress');
	$("#eticsoftMP_loader").modal('hide');
	$("#eticsoftMP_loader").modal('hide');
}

function mp_showErrors(mp_error_message, mp_error_title = 'Bir hata oluştu'){
	mp_hidePanels();
	$("#eticsoftMP_message_text").html(mp_error_message);
	$("#eticsoftMP_message_title").html(mp_error_title);
	$("#eticsoftMP_message_panel").modal('show');
}

function mp_hidePanels(){
	$("#linkCardtoClient").modal('hide');
	$("#eticsoftMP_loader").modal('hide');
	$("#updateUserID").modal('hide');
	$("#mpinModal").modal('hide');
	$("#otpModal").modal('hide');
	$("#eticsoftMP_loader").modal('hide');
}

function emp_showTos() {
  $('#emp_tosModal').modal('show').find('.modal-body').load(sanalposprouri+'/lib/masterpass/jsapi/tos.html');
  $('#emp_tosModal').find('.modal-footer').html("");
  $('#emp_tosModal').find('.modal-footer').append('<a data-dismiss="modal" class="btn btn-info">Kabul ediyorum</a>');
}

function parseAccountStatus(accountStatus){
	
}

function mp_debuger(log) {
	console.log(log);
}

function emp_linkAccount(){
	mp_hidePanels();
	getTokenSS('linkCardToClient');
}

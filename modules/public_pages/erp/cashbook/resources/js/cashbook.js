/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * cashbook.js
 * 
 * $Revision: 1.16 $
 * 
 */
$(document).ready(function(){

	/* cashbook -> bankaccounts -> reconcile */
	
	$reconcile_context = $('#cashbook-bankaccounts-reconcile');
	
	$("tr input:checkbox", $reconcile_context).live("change", function() {
		
		var row		= $(this).parents('tr').attr('rel'),
			value	= $(this).parents('tr').find('#gross_value'+row),
			target	= $('#allocated_total');
		
		$(this).setInvoiceTotal(value,target);
		
	});
		
	$("tr input:checkbox, #CBAccount_statement_balance", $reconcile_context).live("change", function() {
			
		// set a few vars
		var current_statement_balance	= parseFloat($('#statement_balance').val()),
			new_statement_balance		= parseFloat($('#CBAccount_statement_balance').val()),
			transactions_sum			= 0;
		
		// calculate actual difference
		$('tr input:checkbox:checked').each(function() {
			transactions_sum += parseFloat($(this).data('transaction-value'));
		});
		
		// differences between current balance and propose balance with transaction sums
		var difference = Math.round((current_statement_balance + transactions_sum - new_statement_balance)*100)/100;

		difference = difference.toFixed(2);
		
		if (difference !== "0.00") {
			$('.reconcile_error').show().find('span').html(difference);
			$('#saveform').attr('disabled', 'disabled');
		} else {
			$('.reconcile_error').hide();
			$('#saveform').removeAttr('disabled');
		}
		
	});
	
	$("a.select-all", $reconcile_context).live("click", function(event) {

		event.preventDefault();
		
		// only check + trigger elements that are not already checked
		// otherwise the value at the bottom will be inaccurate
		
		$("tr input:checkbox").not(':checked').each(function() {
			$(this).attr('checked', 'checked').trigger('change');
		});
		
	});
		
	
	/* cashbook -> bankaccounts -> revaluation */
	
	$revaluation_context = $('#cashbook-bankaccounts-revaluation');
	
	$("#CBAccount_new_balance", $revaluation_context).live("change", function() {
		
		var new_balance = $("#CBAccount_new_balance").val()
		   ,rate = 0;
		
		if ($("#CBAccount_method").val()=='D') {
			rate = $("#CBAccount_balance").val()/$("#CBAccount_new_balance").val();
		}
		else {
			rate = $("#CBAccount_new_balance").val()/$("#CBAccount_balance").val();
		}

		$("#CBAccount_rate").val(rate.toFixed(4));
		
	});
		
	$("#CBAccount_rate", $revaluation_context).live("change", function() {
		
		var rate = $("#CBAccount_rate").val()
		   ,balance = $("#CBAccount_balance").val();
		
		if ($("#CBAccount_method").val()=='D') {
			new_balance = balance/rate;
		}
		else {
			new_balance = balance*rate;
		}
		
		$("#CBAccount_new_balance").val(new_balance.toFixed(2));
		
	});
		
	/* cashbook -> cbtransactions -> receive_payment */

	$("#CBTransaction_cb_account_id", "#cashbook-cbtransactions-receive_payment").live("change", function(){
		
		var $self = $(this);
		
		$('#CBTransaction_currency_id').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getAllowedCurrencies',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				ajax			: ''
			},
		});

	});
	
	$("#CBTransaction_currency_id", "#cashbook-cbtransactions-receive_payment").live("change", function(){
		
		$('#CBTransaction_rate').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getCurrencyRate',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				currency_id		: $('#CBTransaction_currency_id').val(),
				ajax			: ''
			},
		});

	});
	
	$("#CBTransaction_glaccount_id", "#cashbook-cbtransactions-receive_payment").live("change", function(){
		
		var $self = $(this);
		
		$('#CBTransaction_glcentre_id').uz_ajax({
			data:{
				module		: 'cashbook',
				controller	: 'Cbtransactions',
				action		: 'getCentres',
				glaccount_id: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#CBTransaction_net_value, #CBTransaction_tax_value", "#cashbook-cbtransactions-receive_payment").change(function(){
		$(this).val(roundNumber($(this).val(), 2));
		$('#CBTransaction_gross_value').val(roundNumber(parseFloat($('#CBTransaction_net_value').val())+parseFloat($('#CBTransaction_tax_value').val()), 2));
	});

	$( "#cashbook-cbtransactions-receive_payment").on("change", "#CBTransaction_company_id", function(){
		
		var $self = $(this);
		
		$('#CBTransaction_person_id').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getCompanyPeople',
				company_id		: $('#CBTransaction_company_id').val(),
				ajax			: ''
			},
		});
		
	});

	/* cashbook -> cbtransactions -> make_payment */

	$("#CBTransaction_cb_account_id", "#cashbook-cbtransactions-make_payment").live("change", function(){
		
		var $self = $(this);
		
		$('#CBTransaction_currency_id').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getAllowedCurrencies',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				ajax			: ''
			},
		});
	
	});

	$("#cashbook-cbtransactions-make_payment").on("change", "#CBTransaction_company_id", function(){
		
		var $self = $(this);
		
		$('#CBTransaction_person_id').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getCompanyPeople',
				company_id		: $('#CBTransaction_company_id').val(),
				ajax			: ''
			},
		});
		
	});
	
	$("#CBTransaction_currency_id", "#cashbook-cbtransactions-make_payment").live("change", function(){
		
		$('#CBTransaction_rate').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getCurrencyRate',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				currency_id		: $('#CBTransaction_currency_id').val(),
				ajax			: ''
			},
		});

	});
	
	$("#CBTransaction_glaccount_id", "#cashbook-cbtransactions-make_payment").live("change", function(){
		
		var $self = $(this);
		
		$('#CBTransaction_glcentre_id').uz_ajax({
			data:{
				module		: 'cashbook',
				controller	: 'Cbtransactions',
				action		: 'getCentres',
				glaccount_id: $self.val(),
				ajax		: ''
			}
		});
		
	});

	$("#CBTransaction_net_value, #CBTransaction_tax_value", "#cashbook-cbtransactions-make_payment").live("change", function(){
		$(this).val(roundNumber($(this).val(), 2));
		$('#CBTransaction_gross_value').val(roundNumber(parseFloat($('#CBTransaction_net_value').val())+parseFloat($('#CBTransaction_tax_value').val()),2));
	});
	
	/* cashbook -> cbtransactions -> make_refund */

	$("#CBTransaction_cb_account_id", "#cashbook-cbtransactions-make_refund").live("change", function(){
		
		var $self = $(this);
		
		$('#CBTransaction_currency_id').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getAllowedCurrencies',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				ajax			: ''
			},
		});
		
	});
	
	$("#CBTransaction_currency_id", "#cashbook-cbtransactions-make_refund").live("change", function(){
		
		$('#CBTransaction_rate').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getCurrencyRate',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				currency_id		: $('#CBTransaction_currency_id').val(),
				ajax			: ''
			},
		});

	});
	
	$("#CBTransaction_glaccount_id", "#cashbook-cbtransactions-make_refund").live("change", function(){
		
		var $self = $(this);
		
		$('#CBTransaction_glcentre_id').uz_ajax({
			data:{
				module		: 'cashbook',
				controller	: 'Cbtransactions',
				action		: 'getCentres',
				glaccount_id: $self.val(),
				ajax		: ''
			}
		});
		
	});

	$("#CBTransaction_net_value, #CBTransaction_tax_value", "#cashbook-cbtransactions-make_refund").live("change", function(){
		$(this).val(roundNumber($(this).val(), 2));
		$('#CBTransaction_gross_value').val(roundNumber(parseFloat($('#CBTransaction_net_value').val())+parseFloat($('#CBTransaction_tax_value').val()),2));
	});
	
	/* cashbook -> cbtransactions -> move_money */
	
	$("#CBTransaction_cb_account_id", "#cashbook-cbtransactions-move_money").live("change", function(){
		
		var $self = $(this);
		
		$('#CBTransaction_to_account_id').uz_ajax({
			data:{
				module		: 'cashbook',
				controller	: 'cbtransactions',
				action		: 'getOtherAccounts',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
		$('#CBTransaction_rate').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getAccountRate',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				to_account_id	: $('#CBTransaction_to_account_id').val(),
				ajax			: ''
			},
		});
		
	});
	
	$("#CBTransaction_to_account_id", "#cashbook-cbtransactions-move_money").live("change", function(){
		
		var $self = $(this);
		
		$('#CBTransaction_currency_id').uz_ajax({
			data:{
				module		: 'cashbook',
				controller	: 'cbtransactions',
				action		: 'getAccountCurrencies',
				id			: $self.val(),
				id2			: $('#CBTransaction_cb_account_id').val(),
				ajax		: ''
			}
		});
		
		$('#CBTransaction_rate').uz_ajax({
			data:{
				module			: 'cashbook',
				controller		: 'cbtransactions',
				action			: 'getAccountRate',
				cb_account_id	: $('#CBTransaction_cb_account_id').val(),
				to_account_id	: $('#CBTransaction_to_account_id').val(),
				ajax			: ''
			},
		});
		
	});
	
	$("#cashbook-cbtransactions-move_money #CBTransaction_rate, #cashbook-cbtransactions-make_refund #CBTransaction_rate, #cashbook-cbtransactions-make_payment #CBTransaction_rate, #cashbook-cbtransactions-receive_payment #CBTransaction_rate").live("change", function(){
		if ($(this).val()=='') {
			$("#conversion_rate").hide();
		}
		else {
			$("#conversion_rate").show();
		}
		
	});
	
	$("#CBTransaction_net_value", "#cashbook-cbtransactions-move_money").live("change", function(){
		$(this).val(roundNumber($(this).val(), 2));
	});

	/* cashbook -> periodicpayments -> makepayments */
	
	$("input.net_value, input.tax_value", "#cashbook-periodicpayments-makepayments").live("change", function(){
		var rownum = $(this).data('row-number');
		$(this).val(roundNumber($(this).val(), 2));
	   	$('#PeriodicPayment_gross_value'+rownum).text(roundNumber(parseFloat($('#PeriodicPayment_net_value'+rownum).val())+parseFloat($('#PeriodicPayment_tax_value'+rownum).val()), 2));
	});

	$("input.pay", "#cashbook-periodicpayments-makepayments").live("change", function(){
		var rownum = $(this).data('row-number');
	   	if ($(this).is(':checked')) {
	   		$('#PeriodicPayment_skip'+rownum).removeAttr('checked');
	   	}
	});

	$("input.skip", "#cashbook-periodicpayments-makepayments").live("change", function(){
		var rownum = $(this).data('row-number');
	   	if ($(this).is(':checked')) {
	   		$('#PeriodicPayment_pay'+rownum).removeAttr('checked');
	   	}
	});

});
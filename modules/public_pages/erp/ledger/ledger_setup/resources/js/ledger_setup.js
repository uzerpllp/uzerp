/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * ledger_setup.js
 * 
 * 	$Revision: 1.14 $
 * 
 */

$(document).ready(function(){

	/* ledger_setup -> periodicpayments -> new */
	

	$("#ledger_setup-periodicpayments-new").ready(function(){
		
		PPsetProperties();

	});
	
	$("#PeriodicPayment_source","#ledger_setup-periodicpayments-new").live("change", function(){
		
		var $self = $(this);
		
		$('#PeriodicPayment_company_id').uz_ajax({
			data:{
				module		: 'ledger_setup',
				controller	: 'periodicpayments',
				action		: 'getCompanyList',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	// ATTENTION: be careful, this one also uses a third param, selected
	$("#PeriodicPayment_glaccount_id", "#ledger_setup-periodicpayments-new").live("change", function(){
		
		var $self = $(this);
		
		$('#PeriodicPayment_glcentre_id').uz_ajax({
			data:{
				module		: 'ledger_setup',
				controller	: 'periodicpayments',
				action		: 'getCentres',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#PeriodicPayment_cb_account_id", "#ledger_setup-periodicpayments-new").live("change", function(){
		 if(action!='edit') {
			 getPPCurrency();
			 PPsetProperties();
		 }
	});
	
	$("#PeriodicPayment_company_id", "#ledger_setup-periodicpayments-new").live("change", function(){
		
		var $self = $(this);
		
		$('#PeriodicPayment_person_id').uz_ajax({
			data:{
				module		: 'ledger_setup',
				controller	: 'periodicpayments',
				action		: 'getPeople',
				company_id	: $self.val(),
				ajax		: ''
			}
		});
		
		if(action!='edit') {
			 getPPCurrency();
			 PPsetProperties();
		 }
	});
	
	$("#PeriodicPayment_currency_id", "#ledger_setup-periodicpayments-new").live("change", function() {
		PPsetProperties();
	});
	
	$("#PeriodicPayment_net_value", "#ledger_setup-periodicpayments-new").live("change", function() {
		$(this).val(roundNumber($(this).val(), 2));
		addValues($('#PeriodicPayment_net_value'),$('#PeriodicPayment_tax_value'), $('#PeriodicPayment_gross_value'));
	});
	
	$("#PeriodicPayment_tax_value", "#ledger_setup-periodicpayments-new").live("change", function() {
		$(this).val(roundNumber($(this).val(), 2)); 
		addValues($('#PeriodicPayment_net_value'),$('#PeriodicPayment_tax_value'), $('#PeriodicPayment_gross_value'));
	});
	
	$("#PeriodicPayment_gross_value", "#ledger_setup-periodicpayments-new").live("change", function() {
		$(this).val(roundNumber($(this).val(), 2)); 
	});
	

	/* ledger_setup -> currencys -> new */
	
	$("#Currency_writeoff_glaccount_id","#ledger_setup-currencys-new").live("change", function(){
		
		var $self = $(this);
		
		$('#Currency_glcentre_id').uz_ajax({
			data:{
				module		: 'general_ledger',
				controller	: 'glaccounts',
				action		: 'getCentres',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	/* ledger_setup -> bankaccounts -> new */
	
	$("#CBAccount_glaccount_id","#ledger_setup-bankaccounts-new").live("change", function(){
		
		var $self = $(this);
		
		$('#CBAccount_glcentre_id').uz_ajax({
			data:{
				module		: 'ledger_setup',
				controller	: 'bankaccounts',
				action		: 'getCentres',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});

	$("#CBAccount_balance","#ledger_setup-bankaccounts-new").live("change", function(){
		$(this).val(roundNumber($(this).val(), 2));
	});
	
	$("#CBAccount_statement_balance","#ledger_setup-bankaccounts-new").live("change", function(){
		$(this).val(roundNumber($(this).val(), 2));
	});
	
	/* ledger_setup -> glbudgets -> new */
	
	$("#GLBudget_glaccount_id","#ledger_setup-glbudgets-new").live("change", function(){
		
		var $self = $(this);
		
		$('#GLBudget_glcentre_id').uz_ajax({
			data:{
				module		: 'ledger_setup',
				controller	: 'glbudgets',
				action		: 'getCentres',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});

	/* ledger_setup -> paymentterms -> new */
	
	$('#ledger_setup-paymentterms-new #PaymentTerm_discount').live("change", function() {
		
		var $self = $(this),
		pl_discount_glaccount_id = $('#PaymentTerm_pl_discount_glaccount_id').val(),
		sl_discount_glaccount_id = $('#PaymentTerm_sl_discount_glaccount_id').val();
		
		if ($self.val()>0) {
			$('#PaymentTerm_allow_discount_on_allocation').prop('checked', true);
		}

		$.uz_ajax({
			target:[
			    {
					element	: '#PaymentTerm_pl_discount_glaccount_id',
					field	: "pl_discount_glaccount_id",
					selected: {
						value: pl_discount_glaccount_id
					}
				},
				{
					element	: '#PaymentTerm_sl_discount_glaccount_id',
					field	: "sl_discount_glaccount_id",
					selected: {
						value: sl_discount_glaccount_id
					}
				}
			],
			data:{
				module		: 'ledger_setup',
				controller	: 'paymentterms',
				action		: 'check_discount',
				discount	: $self.val(),
				allocate	: $('#PaymentTerm_allow_discount_on_allocation').prop('checked'),
				ajax		: ''
			}
		});
	});
				
	$('#ledger_setup-paymentterms-new #PaymentTerm_allow_discount_on_allocation').live("change", function() {
		
		var $self = $(this),
			pl_discount_glaccount_id = $('#PaymentTerm_pl_discount_glaccount_id').val(),
			sl_discount_glaccount_id = $('#PaymentTerm_sl_discount_glaccount_id').val();
		
		if ($('#PaymentTerm_discount').val()>0) {
			$self.prop('checked', true);
		}
		else {
			$.uz_ajax({
				target:[
				    {
				    	element	: '#PaymentTerm_pl_discount_glaccount_id',
				    	field	: "pl_discount_glaccount_id",
				    	selected: {
				    		value: pl_discount_glaccount_id
				    	}
				    },
				    {
				    	element	: '#PaymentTerm_sl_discount_glaccount_id',
				    	field	: "sl_discount_glaccount_id",
				    	selected: {
				    		value: sl_discount_glaccount_id
				    	}
				    }
				],
				data:{
					module		: 'ledger_setup',
					controller	: 'paymentterms',
					action		: 'check_discount',
					discount	: $('#PaymentTerm_discount').val(),
					allocate	: $self.prop('checked'),
					ajax		: ''
				}
			});
		}
	});
	
	$('#ledger_setup-paymentterms-new #PaymentTerm_pl_discount_glaccount_id').live("change", function() {
		
		var $self = $(this),
		pl_discount_glcentre_id = $('#PaymentTerm_pl_discount_glcentre_id').val();
		
		$.uz_ajax({
			target:{
				element	: '#PaymentTerm_pl_discount_glcentre_id',
				selected: {
					value: pl_discount_glcentre_id
				}
			},
			data:{
				module		: 'ledger_setup',
				controller	: 'paymentterms',
				action		: 'getCentres',
				id			: $self.val(),
				ajax		: ''
			}
		});
	});
			
	$('#ledger_setup-paymentterms-new #PaymentTerm_sl_discount_glaccount_id').live("change", function() {
		
		var $self = $(this),
		sl_discount_glcentre_id = $('#PaymentTerm_sl_discount_glcentre_id').val();
		
		$.uz_ajax({
			target:{
				element	: '#PaymentTerm_sl_discount_glcentre_id',
				selected: {
					value: sl_discount_glcentre_id
				}
			},
			data:{
				module		: 'ledger_setup',
				controller	: 'paymentterms',
				action		: 'getCentres',
				id			: $self.val(),
				ajax		: ''
			}
		});
	});
	
	/* ledger_setup -> glparamss -> new */
	
	$('#ledger_setup-glparamss-new #GLParams_paramdesc').live("change", function() {
		
		var $self = $(this);
		
		$('#GLParams_ajaxvalue').uz_ajax({
			data:{
				module		: 'ledger_setup',
				controller	: 'glparamss',
				action		: 'selectlist',
				id			: $self.val(),
				ajax		: ''
			}
		});
			
	});

});

/* FUNCTIONS */

function getPPCurrency () {
	
	$.uz_ajax({
		target:{
			element	:"#PeriodicPayment_currency_id",
			action	:"selected"
		},
		data:{
			module			: 'ledger_setup',
			controller		: 'periodicpayments',
			action			: 'getCurrencyId',
			company_id		: $('#PeriodicPayment_company_id').val(),
			cb_account_id	: $('#PeriodicPayment_cb_account_id').val(),
			source			: $('#PeriodicPayment_source').val(),
			ajax			: ''
		}
	});
	
}

function PPsetProperties () {
	source=$('#PeriodicPayment_source');
	if (source.val()=='SR' || source.val()=='PP') {
		disable=true;
		$('#pp_person').hide();
		$('#pp_account_centre').hide();
		$('#net_tax').hide();
		$('#PeriodicPayment_gross_value').attr('readOnly',false);
		$('#PeriodicPayment_tax_value').attr('disabled',true);
		$('#PeriodicPayment_net_value').attr('disabled',true);
		$('#PeriodicPayment_person_id').attr('disabled',true);
		$('#PeriodicPayment_glaccount_id').attr('disabled',true);
		$('#PeriodicPayment_glcentre_id').attr('disabled',true);
	} else {
		disable=false;
		$('#pp_person').show();
		$('#pp_account_centre').show();
		$('#net_tax').show();
		$('#PeriodicPayment_gross_value').attr('readOnly',true);
		$('#PeriodicPayment_tax_value').attr('disabled',false);
		$('#PeriodicPayment_net_value').attr('disabled',false);
		$('#PeriodicPayment_person_id').attr('disabled',false);
		$('#PeriodicPayment_glaccount_id').attr('disabled',false);
		$('#PeriodicPayment_glcentre_id').attr('disabled',false);
	}
	// ATTENTION: JQI: this needs implementing... can't really be bothered right now :D
	//selectDisabled(disable, to);
}

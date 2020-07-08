/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * sales_ledger.js
 * 
 * 	$Revision: 1.17 $
 * 
 */

$(document).ready(function(){

	/* sales_ledger -> slcustomers -> new */
	
	$("#SLCustomer_company_id","#sales_ledger-slcustomers-new").live("change", function(){
		
		var $self = $(this);
		
		$('#SLCustomer_email_invoice_id, #SLCustomer_email_statement_id').uz_ajax({
			data:{
				module		: 'sales_ledger',
				controller	: 'slcustomers',
				action		: 'getEmailAddresses',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
		$('#SLCustomer_billing_address_id').uz_ajax({
			data:{
				module		: 'sales_ledger',
				controller	: 'slcustomers',
				action		: 'getInvoiceAddresses',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});

	// Set bank account drop-down options that are valid for the selected currency
	$("#sales_ledger-slcustomers-new").on('change', "#SLCustomer_currency_id", function(){

		var $self = $(this);

		$('#SLCustomer_cb_account_id').uz_ajax({
			data:{
				module		: 'sales_ledger',
				controller	: 'slcustomers',
				action		: 'getBankAccounts',
				id			: $self.val(),
				ajax		: ''
			}
		});

	});

	/* sales_ledger -> slcustomers -> allocate */
	
	$("tr input.allocate", "#sales_ledger-slcustomers-allocate").live("click", function() {
		
		var $self	= $(this),
			row		= $self.parents('tr').attr('rel'),
			value	= $self.parents('tr').find('#SLTransaction_os_value'+row),
			target	= $self.parents('table').find('#allocated_total');
		
		$self.setInvoiceTotal(value, target);
		
	});
	
	$("tr input.include_discount", "#sales_ledger-slcustomers-allocate").live("click", function() {
		
		var $self			= $(this),
			row				= $self.parents('tr').attr('rel'),
			allocate_value	= $('#SLTransaction_os_value'+row),
			discount_value	= $('#SLTransaction_settlement_discount'+row),
			addValues;
		
		if ($('#SLTransaction_include_discount'+row).is(':checked')) {
			addValues  = parseFloat($(allocate_value).val())-parseFloat($(discount_value).val());
			$(allocate_value).val(addValues.toFixed(2));
		}
		else
		{
			addValues  = parseFloat($(allocate_value).val())+parseFloat($(discount_value).val());
			$(allocate_value).val(addValues.toFixed(2));
		}
	
		$(allocate_value).trigger("change");
	});
	
	$("tr input.allocation", "#sales_ledger-slcustomers-allocate").live("change", function() {
		
		var $self			= $(this),
			row				= $self.parents('tr').attr('rel'),
			target			= $self.parents('table').find('#allocated_total'),
			error			= false,
			original_value	= $('#SLTransaction_os_value_original'+row).val(),
			copy_val		= $('#SLTransaction_os_value_copy'+row).val(),
			new_value		= $self.val(),
			addValues;
		
		if (!$.isNumeric(new_value)) {
			new_value=0;
			$(this).val(new_value.toFixed(2));
		}
		
		if (parseFloat(original_value) < 0) {
			
			if (parseFloat(new_value) > 0 || parseFloat(new_value) < parseFloat(original_value)) {
				error = true;
			}
			
		} else {
			
			if (parseFloat(new_value) < 0 || parseFloat(new_value) > parseFloat(original_value)) {
				error = true;
			}
			
		}
		
		if (error === true) {
			
			alert('Invalid allocation value');
			$self.val(copy_val);
			return false;
			
		}
		
		if ($('#SLTransaction_allocate'+row).is(':checked')) {
			
			addValues  = parseFloat($(target).val())-parseFloat(copy_val);
			addValues += parseFloat(new_value);
			$(target).val(addValues.toFixed(2));
			
		}
		
		$('#SLTransaction_os_value_copy'+row).val($self.val());
		
	});
	
	$("tr input.discount", "#sales_ledger-slcustomers-allocate").live("change", function() {
	
		var $self			= $(this),
			row				= $self.parents('tr').attr('rel'),
			original_value	= $('#SLTransaction_os_value_original'+row),
			allocate_value	= $('#SLTransaction_os_value'+row),
			discount_value	= $('#SLTransaction_settlement_discount'+row),
			addValues;
	
		if (!$.isNumeric($(discount_value).val())) {
			var zero=0;
			$(discount_value).val(zero.toFixed(2));
		}
		
		if ($('#SLTransaction_include_discount'+row).is(':checked')) {
			addValues  = parseFloat($(original_value).val())-parseFloat($(discount_value).val());
			$(allocate_value).val(addValues.toFixed(2));
			$(allocate_value).trigger("change");
		}
	
	
	});
	
	/* sales_ledger -> slcustomers -> view_ledger_trans */
	
	$("tr input.contra", "#sales_ledger-slcustomers-view_ledger_trans").live("click", function() {
		var row = $(this).data('row-number');
		var value = $('#SLTransaction_os_value'+row);
		var target = $('#contra_total');
		
		$(this).setInvoiceTotal(value,target);
	});
	
	/* sales_ledger -> sldiscounts -> new */
	
	$("#SLDiscount_slmaster_id","#sales_ledger-sldiscounts-new").live("change", function(){
		
		var $self = $(this);
		
		$('#SLDiscount_prod_group_id').uz_ajax({
			data:{
				module		: 'sales_ledger',
				controller	: 'sldiscounts',
				action		: 'getProductGroups',
				slmaster_id	: $self.val(),
				ajax		: ''
			}
		});
		
	});

});
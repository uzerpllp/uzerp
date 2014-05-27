/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * purchase_ledger.js
 * 
 * 	$Revision: 1.17 $
 * 
 */

$(document).ready(function(){

	/* purchase_ledger -> plsuppliers -> new */
	
	$("#PLSupplier_company_id", "#purchase_ledger-plsuppliers-new").live("change", function(){

		var $self = $(this);
		
		$('#PLSupplier_payee_name').uz_ajax({
			data:{
				module		: 'purchase_ledger',
				controller	: 'plsuppliers',
				action		: 'getCompanyName',
				id			: $self.val(),
				ajax		: ''
			}
		});

		$('#PLSupplier_email_order_id, #PLSupplier_email_remittance_id').uz_ajax({
			data:{
				module		: 'purchase_ledger',
				controller	: 'plsuppliers',
				action		: 'getEmailAddresses',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
		$('#PLSupplier_payment_address_id').uz_ajax({
			data:{
				module		: 'purchase_ledger',
				controller	: 'plsuppliers',
				action		: 'getRemittanceAddresses',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	/* purchase_ledger -> plpayments -> select_for_payment */
	
	$("tr input:checkbox.pay", "#purchase_ledger-plpayments-select_for_payment").live("change", function() {
		
		var row = $(this).parents('tr').attr('rel');
		var value = $(this).parents('tr').find('#os_value'+row);
		var target = $(this).parents('table').find('#allocated_total');
		
		$(this).setInvoiceTotal(value,target);
		
		if (($(this).is(':checked') != $('#PLTransaction_discount_'+row).is(':checked'))) {
			$('#PLTransaction_discount_'+row).prop('checked', $(this).is(':checked')).trigger('change');
		}
		
	});
	
	$("tr input:checkbox.discount", "#purchase_ledger-plpayments-select_for_payment").live("change", function() {
		
		var row = $(this).parents('tr').attr('rel');
		var value = $(this).parents('tr').find('#settlement_discount'+row);
		var target = $(this).parents('table').find('#allocated_total');
		
		$(this).setInvoiceTotal(value,target);
		
		if ($(this).is(':checked') && !$('#PLTransaction_for_payment_'+row).is(':checked')) {
			$('#PLTransaction_for_payment_'+row).prop('checked', true).trigger('change');
		}
		
	});
	
	$("input:button.select_all", "#purchase_ledger-plpayments-select_for_payment").live("click", function() {
		$(this).checkAll("tr input:checkbox");
	});
	
	/* purchase_ledger -> plsuppliers -> allocate */
	
	$("tr input.allocate", "#purchase_ledger-plsuppliers-allocate").live("click", function() {
		var row = $(this).parents('tr').attr('rel');
		var value = $(this).parents('tr').find('#PLTransaction_os_value'+row);
		var target = $(this).parents('table').find('#allocated_total');
		$(this).setInvoiceTotal(value,target);
	});
	
	$("tr input.include_discount", "#purchase_ledger-plsuppliers-allocate").live("click", function() {
		
		var $self			= $(this),
			row				= $self.parents('tr').attr('rel'),
			allocate_value	= $('#PLTransaction_os_value'+row),
			discount_value	= $('#PLTransaction_settlement_discount'+row),
			addValues;
		
		if ($('#PLTransaction_include_discount'+row).is(':checked')) {
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
	
	$("tr input.allocation", "#purchase_ledger-plsuppliers-allocate").live("change", function() {
		var row = $(this).parents('tr').attr('rel');
		var target = $(this).parents('table').find('#allocated_total');
		var addValues;
		var error='false';
		var original_value=$('#PLTransaction_os_value_original'+row).val();
		var copy_val=$('#PLTransaction_os_value_copy'+row).val();
		var new_value=$(this).val();
		
		if (!$.isNumeric(new_value)) {
			new_value=0;
			$(this).val(new_value.toFixed(2));
		}
		
		if (parseFloat(original_value)<0) {
			if (parseFloat(new_value)>0 || parseFloat(new_value)<parseFloat(original_value)) {
				error='true';
			}
		} else {
			if (parseFloat(new_value)<0 || parseFloat(new_value)>parseFloat(original_value)) {
				error='true';
			}
		}
		
		if (error=='true') {
			alert('Invalid allocation value');
			$(this).val(copy_val);
			return false;
		}
		
		if ($('#PLTransaction_allocate'+row).is(':checked')) {
			addValues=parseFloat($(target).val())-parseFloat(copy_val);
			addValues+=parseFloat(new_value);
			$(target).val(addValues.toFixed(2));
		}
		
		$('#PLTransaction_os_value_copy'+row).val($(this).val());
		
	});
	
	$("tr input.discount", "#purchase_ledger-plsuppliers-allocate").live("change", function() {
		
		var $self			= $(this),
			row				= $self.parents('tr').attr('rel'),
			original_value	= $('#PLTransaction_os_value_original'+row),
			allocate_value	= $('#PLTransaction_os_value'+row),
			discount_value	= $('#PLTransaction_settlement_discount'+row),
			addValues;
	
		if (!$.isNumeric($(discount_value).val())) {
			var zero=0;
			$(discount_value).val(zero.toFixed(2));
		}
		
		if ($('#PLTransaction_include_discount'+row).is(':checked')) {
			addValues  = parseFloat($(original_value).val())-parseFloat($(discount_value).val());
			$(allocate_value).val(addValues.toFixed(2));
			$(allocate_value).trigger("change");
		}
	
	
	});
	
	/* purchase_ledger -> plsuppliers -> view_ledger_trans */
	
	$("tr input.contra", "#purchase_ledger-plsuppliers-view_ledger_trans").live("click", function() {
		var row = $(this).data('row-number');
		var value = $('#PLTransaction_os_value'+row);
		var target = $('#contra_total');
		
		$(this).setInvoiceTotal(value,target);
	});
	
	/* purchase_ledger -> plpayments -> savePayments */
	
	// Progress Bar for batch PL Payment processing
	
	$('input[type=submit][name=saveform]', '#purchase_ledger-plpayments-selected_payments_list').live('click', function (event) {
		
		var form = $(event.currentTarget).parents('form');
		var $_GET = getQueryParams(form.attr('action'));

		if ($(this).val()=='Pay') {
			
			event.preventDefault();
									
			var progress = [{
							 progress_url	: "/?module=purchase_ledger&controller=plpayments&action=getprogress&monitor_name=create_security_key&ajax="
							,title			: "PL Payment - Creating Security Key"
							},
							{
							 progress_url	: "/?module=purchase_ledger&controller=plpayments&action=getprogress&monitor_name=checking_supplier_details&ajax="
							,title			: "PL Payment - Checking Supplier Details"
							},
							{
							 progress_url	: "/?module=purchase_ledger&controller=plpayments&action=getprogress&monitor_name=creating_pl_transactions&ajax="
							,title			: "PL Payment - Creating PL Transactions"
							},
							{
							 progress_url	: "/?module=purchase_ledger&controller=plpayments&action=getprogress&monitor_name=allocate_payments&ajax="
							,title			: "PL Payment - Allocate Payments"
							}
			];
			
			options = {main_url			: form.attr('action')
					  ,data				: form.serialize()
					  ,type				: 'POST'
					  ,heading			: "Generate PL Payments"
					  ,success_message	: "Generate PL Payments OK"
					  ,fail_message		: "Generate PL Payments Failed"
				  	  ,progress_bars	: progress
			};
			
			uz_progressbar(options);
//			rebind_plugins(document);
		}
		
	});
	
	/* purchase_ledger -> plpayments -> enter_payment_reference */
	
	// Progress Bar for batch PL Payment processing
	
	$('input[type=submit][name=saveform]', '#purchase_ledger-plpayments-enter_payment_reference').live('click', function (event) {
		
		var form = $(event.currentTarget).parents('form');
		var $_GET = getQueryParams(form.attr('action'));

		if ($(this).val()=='Save') {
			
			event.preventDefault();
									
			var progress = [{
							 progress_url	: "/?module=purchase_ledger&controller=plpayments&action=getprogress&monitor_name=update_payment_reference&ajax="
							,title			: ""
							}
			];
			
			options = {main_url			: form.attr('action')
					  ,data				: form.serialize()
					  ,type				: 'POST'
					  ,heading			: "PL Payment - Updating Payment Reference"
					  ,success_message	: "PL Payments - Payment Reference Update Completed"
					  ,fail_message		: "PL Payments - Payment Reference Update Failed"
				  	  ,progress_bars	: progress
			};
			
			uz_progressbar(options);
		}
		
	});
	
	/* purchase_ledger -> plpayments -> output_summary */
	
	// Progress bar for output of remittances
	
	$('a[href*="action=process_output"]', '#purchase_ledger-pltransactions-output_summary').live('click', function (event) {
		
		event.preventDefault();
		
		options = {main_url 		: $(this).attr('href')
				  ,progress_url		: "/?module=costing&controller=stcosts&action=getprogress&monitor_name=print_supplier_remittances&ajax="
				  ,heading			: "Output Remittances"
				  ,success_message	: "Output Remittances Completed OK"
				  ,fail_message		: "Output Remittances Failed"
		};
		
		uz_progressbar(options);
		
	});
	
	
});
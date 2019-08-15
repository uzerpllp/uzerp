/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * purchase_order.js
 * 
 * 	$Revision: 1.45 $
 * 
 */

/* Delegated event handlers - select input initial content for the user, on click*/
$(document).on('click', '#purchase_order-porderlines-new #POrderLine_revised_qty', function() {
	if (Number($(this).val()) == 0) {
		$(this).select();
	}
});

$(document).on('click', '#purchase_order-porderlines-new #POrderLine_price',function() {
	if (Number($(this).val()) == 0) {
		$(this).select();
	}
});

$(document).ready(function() {

	/* planned orders -> createorder */
	$('#purchase_order-poplanned-index').on('change', '#select-all', function() {
		$('input:checkbox.select').not(this).prop('checked', this.checked);
	});

	$('input:checkbox.select').on('click', function() {
		$('input#select-all').prop('checked', false);
	});

	/* purchase_order -> porders -> new */

	$('#default_receive_action', '#purchase_order-porders-new').live('change', function() {
		
		$.uz_ajax({
			target: {
				element	: '#POrder_receive_action',
				action	: 'selected'
			},
			data: {
				module		: 'purchase_order',
				controller	: 'porders',
				action		: 'getReceiveAction',
				id			: $('#POrder_plmaster_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	$('#POrder_plmaster_id', '#purchase_order-porders-new').live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element		:'#default_receive_action',
					field		:'receive_action',
					highlight	:false
				},
				{
					element	:'#notes',
					field	:'notes'
				},
				{
					element	: '#POrder_delivery_term_id',
					field	: "delivery_term",
					action	: "selected"
				}
			],
			data: {
				module			: 'purchase_order',
				controller		: 'porders',
				action			: 'getSupplierData',
				id				: $self.val(),
				product_search	: $('#POrderLine_product_search').val(),
				plmaster_id		: $('#POrder_plmaster_id').val(),
				ajax			: ''
			}
		});
		
	});
	
    $("#POrder_project_id", "#purchase_order-porders-new").on('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#POrder_task_id",
				field	: 'task_id'
		    },
			data:{
				module		: 'purchase_order',
				controller	: 'porders',
				action		: 'getTaskList',
				project_id	: $('#POrder_project_id').val(),
				ajax		: ''
			}
		});
		
	});
    
    $("#purchase_order-porders-new #POrder_project_id").live("change", function(){
		if ($(this).val()=='') {
			$("#task_id").hide();
		}
		else {
			$("#task_id").show();
		}
		
	});
	
	/* purchase_order -> porders -> view */
	
	$('.edit-line a, .add_lines_related a, .add_work_order_purchase_related a' ).live('click', function(event) {
		
		event.preventDefault();
		
		var $self = $(this);
		
		if ($self.parent('li').hasClass('add_lines_related') || $self.parent('li').hasClass('add_work_order_purchase_related')) {
			var title	= 'Add Purchase Order Line';
			var type	= 'add';
		} else {
			var title	= 'Edit Purchase Order Line';
			var type	= 'edit';
		}

		formDialog({
			title		: title,
			calledby	: $('#included_file').find('.content_wrapper').attr('id'),
			id			: 'porderlines',
			url			: $self.attr('href'),
			type		: type,
			height		: 550,
			width		: 550,
			resizable	: true,
			callback	: function() {
			
				var $search = $('#POrderLine_product_search');
			
				if ($search.length) {
					$search.select();
				} else {
					$('#POrderLine_productline_id').focus();
				}
			
			}
		});
		
	});
	
	/* purchase_order -> porderlines -> new */
	
	$('select, input, #purchase_order-porderlines-new, #purchase_order-porderlines-new_wopurchase').live('change',function() {
		
		/* get the field name */
		var $self	= $(this),
			field	= $self.data('field');
		
		switch(field) {

			case 'mf_workorders_id':

				var optionSelected = $("option:selected", this);
				
				$.uz_ajax({
					target:[
						{
							element	: '#POrderLine_productline_id',
							field	: 'productline_id'
						},
						{
							element	: '#POrderLine_revised_qty',
							field	: 'revised_qty'
						}
					],
					data: {
						module			: 'purchase_order',
						controller		: 'porderlines',
						action			: 'getWorkOrderOperationLines',
						plmaster_id		: $('#POrder_plmaster_id').val(),
						mfworkorder_id	: $('#POrderLine_mf_workorders_id').val(),
						ajax			: ''
					}
				});

				break;
		
			case 'product_search':
				
				$('#POrderLine_productline_id').uz_ajax({
					data: {
						module			: 'purchase_order',
						controller		: 'porderlines',
						action			: 'getProductlines',
						product_search	: $self.val(),
						plmaster_id		: $('#POrder_plmaster_id').val(),
						limit			: $('#prod_search_limit').val(),
						ajax			: ''
					}
				});
				
				break;
				
			case 'productline_id':
				
			var optionSelected = $("option:selected", this);
			$('#POrderLine_mf_operations_id').val(optionSelected.data('opid'));

				$.uz_ajax({
					target:[
						{
							element	: '#POrderLine_description',
							field	: 'description'
						},
						{
							element	: '#POrderLine_stuom_id',
							field	: 'stuom_id'
						},
						{
							element	: '#POrderLine_price',
							field	: 'price'
						},
						{
							element	: '#POrderLine_glaccount_id',
							field	: 'glaccount_id'
						},
						{
							element	: '#POrderLine_tax_rate_id',
							field	: 'tax_rate_id'
						},
						{
							element	: '#POrderLine_due_delivery_date',
							field	: 'due_delivery_date'
						}
					],
					data: {
						module			: 'purchase_order',
						controller		: 'porderlines',
						action			: 'getLineData',
						productline_id	: $self.val(),
						plmaster_id		: $('#POrder_plmaster_id').val(),
						product_search	: $('#POrderLine_product_search').val(),
						op_id			: $('#POrderLine_mf_operations_id').val(),
						ajax			: ''
					}
				});
				
				break;
				
			case 'glaccount_id':
				
				$('#POrderLine_glcentre_id').uz_ajax({
					data: {
						module			: 'purchase_order',
						controller		: 'porderlines',
						action			: 'getCentre',
						glaccount_id	: $self.val(),
						productline_id	: $('#POrderLine_productline_id').val(),
						ajax			: ''
					}
				});
				
				break;
				
			case 'item_description':
				
				$('#input_description').val('');
				
				break;
				
			case 'revised_qty':
				
				calcValue('#POrderLine_net_value', $('#POrderLine_revised_qty').val(), $('#POrderLine_price').val());
				
				break;
			
			case 'order_qty':
			
				calcValue('#POrderLine_net_value', $('#POrderLine_revised_qty').val(), $('#POrderLine_price').val());
			
				break;
			
			case 'price':
			
				$(this).val(roundNumber($(this).val(), 4)); 
				calcValue('#POrderLine_net_value', $('#POrderLine_revised_qty').val(), $('#POrderLine_price').val());
			
				break;
			
		}
	});	
	
	/* sales_order -> poproductlineheaders -> new */
	
	$('#POProductlineHeader_prod_group_id', '#purchase_order-poproductlineheaders-new').live('change', function() {
		
		var $self = $(this);
		
		$('#purchase_order-poproductlineheaders-new #POProductlineHeader_stitem_id').uz_ajax({
			data: {
				module			: 'purchase_order',
				controller		: 'poproductlineheaders',
				action			: 'getItems',
				prod_group_id	: $self.val(),
				ajax			: ''
			}
		});
		
	});

	$('#POProductlineHeader_stitem_id', '#purchase_order-poproductlineheaders-new').live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element	: '#POProductlineHeader_stuom_id',
					field	: 'stuom_id'
				},
				{
					element	: '#POProductlineHeader_tax_rate_id',
					field	: 'tax_rate_id'
				},
				{
					element	: '#POProductlineHeader_description',
					field	: 'description'
				},
				{
					element	: '#POProductlineHeader_end_date',
					field	: "end_date"
				}
			],
			data: {
				module			: 'purchase_order',
				controller		: 'poproductlineheaders',
				action			: 'getItemData',
				stitem_id		: $self.val(),
				prod_group_id	: $('#POProductlineHeader_prod_group_id').val(),
				stuom_id		: $('#POProductlineHeader_stuom_id').val(),
				tax_rate_id		: $('#POProductlineHeader_tax_rate_id').val(),
				ajax			: ''
			}
		});
		
	});	
	
	$('#POProductlineHeader_glaccount_id', '#purchase_order-poproductlineheaders-new').live('change', function() {
		
		var $self = $(this);
		
		$('#POProductlineHeader_glcentre_id').uz_ajax({
			data: {
				module		: 'purchase_order',
				controller	: 'poproductlineheaders',
				action		: 'getCentres',
				glaccount_id: $self.val(),
//				selected	: $('#POProductline_default_product_centre').val(),
				ajax		: ''
			}
		});
		
	});
	
	/* purchase_order -> poproductlines -> new */
	
	$('#POProductline_productline_header_id',"#purchase_order-poproductlines-new").live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element	: '#header_data',
					field	: "header_data"
				},
				{
					element	: '#POProductline_description',
					field	: "description"
				},
				{
					element	: '#POProductline_price',
					field	: "price"
				},
				{
					element	: '#POProductline_glaccount_id',
					field	: "glaccount_id",
					action	: "selected"
				},
				{
					element	: '#POProductline_start_date',
					field	: "start_date"
				},
				{
					element	: '#POProductline_end_date',
					field	: "end_date"
				}
			],
			data:{
				module					: 'purchase_order',
				controller				: 'poproductlines',
				action					: 'getHeaderData',
				productline_header_id	: $('#POProductline_productline_header_id').val(),
				slmaster_id				: $('#POProductline_slmaster_id').val(),
				ajax					: ''
			}
		});
		
	});

	$('#POProductline_plmaster_id', '#purchase_order-poproductlines-new').live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target: {
				element	: '#POProductline_currency_id',
				action	: 'selected'
			},
			data: {
				module		: 'purchase_order',
				controller	: 'poproductlines',
				action		: 'getCurrency',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	$('#POProductline_supplier_product_code', '#purchase_order-poproductlines-new').live('change', function() {
		$('#POProductline_description').val($(this).val());
	});
	
	$('#POProductline_glaccount_id',"#purchase_order-poproductlines-new").live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:	[{
						element			: '#POProductline_glcentre_id',
						selected: {
							value: $('#POProductlineHeader_glcentre_id').val()
						}
					}],
			data:{
				module			: 'purchase_order',
				controller		: 'poproductlines',
				action			: 'getCentres',
				glaccount_id	: $self.val(),
				ajax			: ''
			}
		});
		
	});	
		
	/* purchase_order -> porders -> createinvoice */
	
	$('#purchase_order-porders-createinvoice').uz_grid({
		load: function() {
		
			var total = 0;
			
			$('.uz-grid-table').find('.net_value').each(function(id) {
				
				var $self	= $(this),
					row		= $self.parents('tr').attr('rel');
				
				if ($('#POReceivedLine_createinvoice' + row).is(':checked')) {
					total += isNaN(parseFloat($self.val()))?0:parseFloat($self.val());
					total  = parseFloat(total.toFixed(2));
				}
				
			});
			
			$('#invoice_net_total').val(total.toFixed(2));
			
		}
	});
	
	$('tr input.net_value', '#purchase_order-porders-createinvoice').live('change', function() {
		
		var self 		= $(this),
			parent_row 	= self.parents('tr'),
			row_id 		= parent_row.data('row-id'),
			checkbox 	= $('#POReceivedLine_createinvoice'+row_id),
			net_total 	= $('#invoice_net_total'),
			addValues;
		
		if (checkbox.is(':checked')) {
			addValues  = parseFloat(net_total.val()) - parseFloat($('#previousvalue_' + row_id).val());
			addValues += parseFloat(self.val());
			net_total.val(addValues.toFixed(2));
		}
		
		$('#previousvalue_' + row_id).val(self.val());
		
		if (checkbox.is(':checked')) {
			
			// persistent selected row + value
			update_create_purchase_invoice(parent_row);
			
		}
		
	});

	$('tr input:checkbox', '#purchase_order-porders-createinvoice').live('click', function() {
		
		// calculate total
		var self		= $(this),
			parent_row	= self.parents('tr'),
			row_id		= parent_row.data('row-id'),
			net_value	= $('#POReceivedLine_saved_net_value' + row_id),
			net_total	= $('#invoice_net_total');
		
		self.setInvoiceTotal(net_value,net_total);
		
		// persistent selected row + value
		update_create_purchase_invoice(parent_row);

	});
	
	/* purchase_order -> poauthlimits -> new */

	$('#POAuthLimit_username', '#purchase_order-poauthlimits-new').live('change', function() {
		
		$('#POAuthLimit_glcentre_id').uz_ajax({
			data:{
				module		: 'purchase_order',
				controller	: 'poauthlimits',
				action		: 'getcentres',
				username	: $('#POAuthLimit_username').val(),
				ajax		: ''
			}
		});
		
	});

	$('#POAuthLimit_glcentre_id', '#purchase_order-poauthlimits-new').live('change', function() {
		
		var $self = $(this);
		
		$('#gl_accounts').uz_ajax({
			data: {
				module		: 'purchase_order',
				controller	: 'poauthlimits',
				action		: 'getaccounts',
				username	: $('#POAuthLimit_username').val(),
				glcentre_id	: $self.val(),
				ajax		: ''
			}
		});
		
		$('#selected_accounts').uz_ajax({
			data: {
				module		: 'purchase_order',
				controller	: 'poauthlimits',
				action		: 'show_auth_accounts',
				ajax		: ''
			}
		});
		
	});

	/* purchase_order -> poauthlimits -> getaccounts */
	
	$('#save_form', "#purchase_order-poauthlimits-new").live('submit', function(event) {

		var selected_accounts	= [],
			counter				= 0;
		
		$('#selected_accounts').find('li').each(function() {
			selected_accounts[counter++] = $(this).data('id');
		});
		
		var accounts_string = selected_accounts.join('|');
		
		$(this).append('<input type="hidden" name="POAuthLimit[selected_accounts]" value="' + accounts_string + '" />');
		
	});
	
	/* purchase_order -> porders -> accrual */
	
	$('tr input:checkbox', '#purchase_order-porders-accrual').live('click', function() {
		
		var $self = $(this);
		
		$('#selected_count').uz_ajax({
			data:{
				module		: 'purchase_order',
				controller	: 'porders',
				action		: 'updateAccrual',
				id			: $self.parents('tr').data('rowid'),
				value		: $self.prop('checked'),
				ajax		: ''
			}
		});
		
	});

	/* purchase_order -> porders -> grn_write_off */
	
	$('tr input:checkbox', '#purchase_order-porders-grn_write_off').live('click', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			data:{
				module		: 'purchase_order',
				controller	: 'porders',
				action		: 'update_grn_write_off',
				id			: $self.parents('tr').data('rowid'),
				value		: $self.prop('checked'),
				ajax		: ''
			}
		});
		
	});

	/* purchase_order -> porders -> match_invoice */
	
	$('tr select.invoice_number', '#purchase_order-porders-match_invoice').live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			data:{
				module		: 'purchase_order',
				controller	: 'porders',
				action		: 'update_matched_invoices',
				id			: $self.parents('tr').data('row-id'),
				value		: $self.val(),
				ajax		: ''
			}
		});
	});

});

function update_create_purchase_invoice(parent_row) {

	var row_id		= parent_row.data('row-id')
		checkbox	= $('#POReceivedLine_createinvoice' + row_id)
		net_value	= $('#POReceivedLine_saved_net_value' + row_id);

	// save selected row
	$.uz_ajax({
		data:{
			module		: 'purchase_order',
			controller	: 'porders',
			action		: 'update_selected_invoices',
			id			: row_id,
			selected	: checkbox.prop('checked'),
			value		: net_value.val(),
			ajax		: ''
		}
	});
	
}
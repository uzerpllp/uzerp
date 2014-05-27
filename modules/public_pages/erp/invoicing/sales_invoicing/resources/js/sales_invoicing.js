/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * sales_invoicing.js
 * 
 * $Revision: 1.23 $
 * 
 */

$(document).ready(function() {
	
	/* sales_invoicing -> sinvoices -> new */

	$("#SInvoice_slmaster_id", "#sales_invoicing-sinvoices-new").live('change', function(){
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element	: '#SInvoice_company_id',
					field	: "company_id"
				},
			    {
			    	element	: '#SInvoice_person_id',
			    	field	: "person_id"
			    }
			],
			data:{
				module			: 'sales_invoicing',
				controller		: 'sinvoices',
				action			: 'getCustomerData',
				slmaster_id		: $self.val(),
				product_search	: $('#SInvoiceLine_product_search').val(),
				ajax			: ''
			}
		});
		
	});
	
	$("#SInvoice_person_id", "#sales_invoicing-sinvoices-new").live('change', function(){
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element	:'#SInvoice_del_address_id',
					field	:"del_address_id"
				},
				{
					element	:'#SInvoice_inv_address_id',
					field	:"inv_address_id"
				},
				{
					element	:'#notes',
					field	:"notes"
				}
			],
			data:{
				module		: 'sales_invoicing',
				controller	: 'sinvoices',
				action		: 'getPersonData',
				person_id	: $self.val(),
				slmaster_id	: $('#SInvoice_slmaster_id').val(),
				del_type	: $('#shipping_type').val(),
				inv_type	: $('#billing_type').val(),
				ajax		: ''
			}
		});

	});	
	
	$("#SInvoice_del_address_id","#sales_invoicing-sinvoices-new").live('change', function(){
		
		if ($("#SInvoice_inv_address_id").find('option[value=' + $("#SInvoice_del_address_id").val() + ']').length > 0) {
			$("#SInvoice_inv_address_id").val($("#SInvoice_del_address_id").val());
		}
		else {
			$("#SInvoice_inv_address_id").val($("#SInvoice_default_inv_address_id").val());
		}
		
	});
	
	$("#SInvoice_inv_address_id","#sales_invoicing-sinvoices-new").live('change', function(){
		
		if ($("#SInvoice_del_address_id").find('option[value=' + $("#SInvoice_inv_address_id").val() + ']').length > 0) {
			$("#SInvoice_del_address_id").val($("#SInvoice_inv_address_id").val());
		}
		else {
			$("#SInvoice_del_address_id option").each(function() {
				if ($("#SInvoice_inv_address_id").find('option[value=' + $(this).val() + ']').length == 0) {
					$("#SInvoice_del_address_id").val($(this).val());
					return false;
				}
			});
		}
		
	});
	
	/* sales_invoicing -> sinvoicelines -> view */
	
	$(".edit-line a, .add_lines_related a").live('click', function(event){
		
		event.preventDefault();
		
		if ($(this).parent('li').hasClass('add_lines_related')) {
			var title	= 'Add Sales Invoice Line';
			var type	= 'add';
		} else {
			var title	= 'Edit Sales Invoice Line';
			var type	= 'edit';
		}
		
		formDialog({
			title		: title,
			calledby	: $('#included_file').find('.content_wrapper').attr('id'),
			id			: 'sinvoicelines',
			url			: $(this).attr('href'),
			type		: type,
			height		: 650,
			width		: 650,
			resizable	: true,
			callback	: function() {
			
				var $search = $('#SInvoiceLine_product_search');
			
				if ($search.length) {
					$search.select();
				} else {
					$('#SInvoiceLine_productline_id').focus();
				}
			
			}
		});
		
	});
	
	/* sales_invoicing -> sinvoicelines -> new */

	$("select, input", "#sales_invoicing-sinvoicelines-new").live('change',function() {
		
		/* get the field name */

		var $self	= $(this),
			field	= $self.data('field');
				
		switch(field) {
		
			case "product_search":
				
				$('#SInvoiceLine_productline_id').uz_ajax({
					data:{
						module			: 'sales_invoicing',
						controller		: 'sinvoicelines',
						action			: 'getProductLines',
						product_search	: $self.val(),
						slmaster_id		: $('#SInvoice_slmaster_id').val(),
						limit			: $('#prod_search_limit').val(),
						ajax			: ''
					}
				});
				
				break;
				
			case "productline_id":
				
				$.uz_ajax({
					target:[
						{
							element	: '#SInvoiceLine_description',
							field	: "description"
						},
						{
							element	: '#SInvoiceLine_stuom_id',
							field	: "stuom_id"
						},
						{
							element	: '#SInvoiceLine_sales_price',
							field	: "price"
						},
						{
							element	: '#SInvoiceLine_glaccount_id',
							field	: "glaccount_id"
						},
						{
							element	: '#SInvoiceLine_tax_rate_id',
							field	: "tax_rate_id"
						}
					],
					data:{
						module		: 'sales_invoicing',
						controller	: 'sinvoicelines',
						action		: 'getLineData',
						productline_id	: $self.val(),
						slmaster_id	: $('#SInvoice_slmaster_id').val(),
						ajax		: ''
					}
				});
				
				break;
				
			case "description":
				$('#input_description').val('');
				break;
				
			case "sales_qty":
				calcValue('#SInvoiceLine_net_value', $('#SInvoiceLine_sales_qty').val(), $('#SInvoiceLine_sales_price').val());
				break;
				
			case "sales_price":
				$self.val(roundNumber($self.val(), 2)); 
				calcValue('#SInvoiceLine_net_value', $('#SInvoiceLine_sales_qty').val(), $('#SInvoiceLine_sales_price').val());
				// ATTENTION: needs testing
				$('#input_price').val('');
				break;
				
			case "glaccount_id":
				
				$('#SInvoiceLine_glcentre_id').uz_ajax({
					data:{
						module			: 'sales_invoicing',
						controller		: 'sinvoicelines',
						action			: 'getCentre',
						ajax			: '',
						glaccount_id	: $self.val(),
						selected		: $('#input_glcentre').val(),
						productline_id	: $('#SInvoiceLine_productline_id').val()
					}
				});
				
				break;
				
			case "net_value":
				debug.info("Trying to calcTotal");
				calcTotal('.net_value ','.uz-grid-table','#gridform_total');
				break;
					
		}
	});
	
    /* sales_invoicing -> sinvoices -> selectinvoices */
    
    $("tr input:checkbox", "#sales_invoicing-sinvoices-selectinvoices").live("click", function() {
    	
    	var $self		= $(this)
    		row_id		= $self.parents('tr').data('row-id')
    		$buttons	= $self.parents('form').find('input[type=submit]');
    	
        $.uz_ajax({
            data: {
                module		: 'sales_invoicing',
                controller	: 'sinvoices',
                action		: 'update_selected_sales_invoices',
                id			: row_id,
                selected	: $self.prop('checked'),
                status		: $('#SInvoices_status' + row_id).val(),
                ajax		: ''
            },
			block_method: 'sales_invoicing-sinvoices-selectinvoices-buttons',
			block: function() {
            	$buttons.attr('disabled', 'disabled');
			},
			unblock: function() {
				$buttons.removeAttr('disabled');
			}

        });
        
    }); 
	
});
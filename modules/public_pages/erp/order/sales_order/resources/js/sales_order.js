/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * sales_order.js
 * 
 * $Revision: 1.55 $
 * 
 */

function end_price_uplift(form_data) {
	
	$('#included_file').uz_ajax({
		async		:  false,
		type		: 'POST',
		url			: "?module=sales_order&controller=soproductlines&action=end_price_uplift&ajax=",
		data		: form_data+"&",
		dataType	: "html"
	});
	
}

function update_prices(num_pages, form_data) {
	
	var count=0;
	
	$("#updatedialog").dialog('open');
	$("#updateprogressbar").progressbar({ value:0, showText: true, textFormat: 'percentage'});
	
	for (i = 0; i < num_pages; i++) { 
		$.getJSON("?module=sales_order&controller=soproductlines&action=update_prices&page=" + (i + 1) + "&ajax=", function(data) {
			count++;
			progress = count * 100 / num_pages;
			$("#updateprogressbar").progressbar({ value: progress, showText: true, textFormat: 'percentage'});
			if (count == num_pages) {
				$("#updatedialog").dialog('close');
				end_price_uplift(form_data);
			}
		});
	}
	
}

$(document).ready(function() {
	/*  Sales Order - Selector */
	
	/*  SOrdersController -> select_products, 'Select Products View'
	 *
	 *  Trigger search on selection changes
	 */
	
	$(document).on('change', '#search_slmaster_id, #search_prod_group_id', function () {
		$(this).parents('form').find('#search_submit').click();
	});
	
	/* Sales Order - Sidebar Confirmations */
	
	/* SOrdersController -> view, 'New Order with Outstanding' */
	$(document).on('click', 'a#order-from-new-lines', function(event){

		event.preventDefault();
		var $targetUrl = $(this).attr("href");

		$( '<div id="#dialog-order-from-new-lines" title="New Order with Outstanding"> \
			<p>Lines with status \'New\' will be cancelled and moved to a new order.</p> \
			<p><strong>This cannot be undone. Do you want to continue?</strong></p></div>'
		).dialog({
			resizable: false,
			modal: true,
			width: '25%',
			maxWidth: '100%',
		    open: function() {
		    	$(this).siblings('.ui-dialog-buttonpane').find('button:eq(1)').focus();
		    },
			buttons: {
				'Move Lines to New Order': function() {
					$( this ).dialog( 'close' );
					$.blockUI({ message: null });
					var sorder = document.getElementById('sales_order-sorders-view');
					$.ajax({
						type: 'POST',
						async: false,
						url: '/?module=sales_order&controller=sorders&action=move_new_lines',
						data: {
							id: sorder.dataset.id,
							dialog: ''
						},
						success: function(response) {
							window.location.href = response.redirect;
						},
						error: function(xhr){
							$.unblockUI();
							alert('Request Status: ' + xhr.status + ', ' + xhr.statusText + ' - ' + xhr.responseText);
						}
					});
				},
				'Cancel': function() {
					$( this ).dialog( 'close' );
				}
			}
		});
	});

	/* review notes */
    $(document).on('click', 'a.view-inplace', function (event) {
       	event.preventDefault(); // lets prevent the links original action, should this be inside the condition?

       	if (!$(this).hasClass('hidden')) {

               	var $self = $(this);

                update_page($self.attr('href'), $self.parents('form').serialize());

        }
    });

	
	/* sales_order -> sorders -> new */
	
	$("#SOrder_slmaster_id","#sales_order-sorders-new").live('change', function(){
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element	: '#SOrder_company_id',
					field	: "company_id"
				},
				{
					element	: '#SOrder_person_id',
					field	: "person_id"
				},
				{
					element	: '#payment_terms',
					field	: "payment_terms"
				},
				{
					element	: '#SOrder_delivery_term_id',
					field	: "delivery_term",
					action	: "selected"
				},
				{
			    	element	: '#SOrder_project_id',
			    	field	: "project_id"
				}
			],
			data:{
				module			: 'sales_order',
				controller		: 'sorders',
				action			: 'getCustomerData',
				slmaster_id		: $self.val(),
				ajax			: ''
			}
		});
		
	});
	
	$("#SOrder_person_id","#sales_order-sorders-new").live('change', function(){
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element	: '#SOrder_del_address_id',
					field	: "del_address_id"
				},
				{
					element	: '#SOrder_inv_address_id',
					field	: "inv_address_id"
				},
				{
					element	: '#notes',
					field	: "notes"
				}
			],
			data:{
				module		: 'sales_order',
				controller	: 'sorders',
				action		: 'getPersonData',
				person_id	: $self.val(),
				slmaster_id	: $('#SOrder_slmaster_id').val(),
				del_type	: $('#shipping_type').val(),
				inv_type	: $('#billing_type').val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#SOrder_del_address_id","#sales_order-sorders-new").live('change', function(){
		
		$.uz_ajax({
			target:{
				element	: '#SOrder_despatch_action',
				action	: "selected"
			},
			data:{
				asyn		: false,
				module		: 'sales_order',
				controller	: 'sorders',
				action		: 'getDespatchAction',
				slmaster_id	: $('#SOrder_slmaster_id').val(),
				ajax		: ''
			}
		});

		if ($("#SOrder_inv_address_id").find('option[value=' + $("#SOrder_del_address_id").val() + ']').length > 0) {
			$("#SOrder_inv_address_id").val($("#SOrder_del_address_id").val());
		}
		else {
			$("#SOrder_inv_address_id").val($("#SOrder_default_inv_address_id").val());
		}
		
	});
	
	$("#SOrder_inv_address_id","#sales_order-sorders-new").live('change', function(){
		
		if ($("#SOrder_del_address_id").find('option[value=' + $("#SOrder_inv_address_id").val() + ']').length > 0) {
			$("#SOrder_del_address_id").val($("#SOrder_inv_address_id").val());
		}
		else {
			$("#SOrder_del_address_id option").each(function() {
				if ($("#SOrder_inv_address_id").find('option[value=' + $(this).val() + ']').length == 0) {
					$("#SOrder_del_address_id").val($(this).val());
					return false;
				}
			});
		}
		
	});
	
	/* sales_orders -> sorders -> new .... get task list from project */
	$("#SOrder_project_id", "#sales_order-sorders-new").live('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#SOrder_task_id",
				field	: 'task_id'
		    },
			data:{
				module		: 'sales_order',
				controller	: 'sorders',
				action		: 'getTaskList',
				project_id	: $('#SOrder_project_id').val(),
				ajax		: ''
			}
		});
		
	});
    
    
	/* sales_order -> sorders -> view */
	
	$(".edit-line a, .add_lines_related a").live('click', function(event){
		
		event.preventDefault();
		
		var $self = $(this);
		
		if ($self.parent('li').hasClass('add_lines_related')) {
			var title='Add Sales Order Line';
			var type='add';
		} else {
			var title='Edit Sales Order Line';
			var type='edit';
		}

		formDialog({
			title		: title,
			calledby	: $('#included_file').find('.content_wrapper').attr('id'),
			id			: 'sorderlines',
			url			: $self.attr('href'),
			type		: type,
			height		: 550,
			width		: 550,
			resizable	: true,
			callback	: function() {
			
				var $search = $('#SOrderLine_product_search');
			
				if ($search.length) {
					$search.select();
				} else {
					$('#SOrderLine_productline_id').focus();
				}
			
			}
		});
		
	});
	
	/* sales_order -> sorders -> confirm_sale */

	$('#SOrder_person_id', "#sales_order-sorders-confirm_sale").live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
					{
						element	: '#SOrder_del_address_id',
						field	: "del_address_id"
					},
					{
						element	: '#SOrder_inv_address_id',
						field	: "inv_address_id"
					},
					{
						element	: '#SOrder_notes',
						field	: "notes"
					}
				],
			data:{
				module		: 'sales_order',
				controller	: 'sorders',
				action		: 'getPersonData',
				person_id	: $self.val(),
				inv_type	: 'billing',
				del_type	: 'shipping',
				context		: 'confirm_sale',
				slmaster_id	: $('#SOrder_slmaster_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	/* sales_order -> sorderlines -> new */
	
	$(document).on('change', "#sales_order-sorderlines-new input, #sales_order-sorderlines-new select", function() {

		var $self	= $(this),
			field	= $self.data('field');

		switch(field) {
			case "product_search":
				$('label.info').remove(); /* remove any form errors */
				if ($self.val() !='' && $self.val().length >= 3){ /* don't search if empty or we'll return 1000's of product lines to the select */
					$('#SOrderLine_sales_stock').html('-');
					$('#SOrderLine_productline_id').uz_ajax({
						data:{
							module			: 'sales_order',
							controller		: 'sorderlines',
							action			: 'getProductlines',
							product_search	: $self.val(),
							slmaster_id		: $('#SOrder_slmaster_id').val(),
							limit			: $('#prod_search_limit').val(),
							ajax			: ''
						}
					});
				} else {
					$('<label class="info">3 or more characters needed for search</label>').insertAfter($self);
				}
				
				break;
				
			case "productline_id":
				
				$.uz_ajax({
					target:[
						{
							element	: '#SOrderLine_description',
							field	: "description"
						},
						{
							element	: '#SOrderLine_item_description',
							field	: "description"
						},
						{
							element	: '#SOrderLine_sales_stock',
							field	: "sales_stock"
						},

						{
							element	: '#SOrderLine_stuom_id',
							field	: "stuom_id"
						},
						{
							element	: '#SOrderLine_price',
							field	: "price"
						},
						{
							element	: '#SOrderLine_glaccount_id',
							field	: "glaccount_id"
						},
						{
							element	: '#SOrderLine_tax_rate_id',
							field	: "tax_rate_id"
						}
					],
					data:{
						module		: 'sales_order',
						controller	: 'sorderlines',
						action		: 'getLineData',
						productline_id	: $self.val(),
						slmaster_id	: $('#SOrder_slmaster_id').val(),
						ajax		: ''
					}
				});
				
				break;

			case "description":
				if ($('#SOrderLine_description').val()=='') {
					$('#SOrderLine_description').val($('#SOrderLine_item_description').val());
				}
				break;
				
			case "item_description":
				$('#input_description').val('');
				break;
				
			case "revised_qty":
				calcValue('#SOrderLine_net_value', $('#SOrderLine_revised_qty').val(), $('#SOrderLine_price').val());
				break;
				
			case "order_qty":
				calcValue('#SOrderLine_net_value', $('#SOrderLine_order_qty').val(), $('#SOrderLine_price').val());
				break;
				
			case "price":
				$(this).val(roundNumber($(this).val(), 2)); 
				calcValue('#SOrderLine_net_value', $('#SOrderLine_revised_qty').val(), $('#SOrderLine_price').val());
				$('#input_price').val('');
				break;
				
			case "glaccount_id":
				
				$('#SOrderLine_glcentre_id').uz_ajax({
					data:{
						module			: 'sales_order',
						controller		: 'sorderlines',
						action			: 'getCentre',
						glaccount_id	: $self.val(),
						productline_id	: $('#SOrderLine_productline_id').val(),
						ajax			: ''
					}
				});
				break;
				
		}
	});
	
	/* sales_order -> soproductlineheaders -> new */
	
	$('#SOProductlineHeader_prod_group_id',"#sales_order-soproductlineheaders-new").live('change', function() {
		
		var $self = $(this);
		
		$('#sales_order-soproductlineheaders-new #SOProductlineHeader_stitem_id').uz_ajax({
			data:{
				module			: 'sales_order',
				controller		: 'soproductlineheaders',
				action			: 'getItems',
				prod_group_id	: $self.val(),
				ajax			: ''
			}
		});
		
	});

	$('#SOProductlineHeader_stitem_id',"#sales_order-soproductlineheaders-new").live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element	: '#SOProductlineHeader_stuom_id',
					field	: "stuom_id"
				},
				{
					element	: '#SOProductlineHeader_tax_rate_id',
					field	: "tax_rate_id"
				},
				{
					element	: '#SOProductlineHeader_description',
					field	: "description"
				},
				{
					element	: '#SOProductlineHeader_end_date',
					field	: "end_date"
				}
			],
			data:{
				module			: 'sales_order',
				controller		: 'soproductlineheaders',
				action			: 'getItemData',
				stitem_id		: $self.val(),
				prod_group_id	: $('#SOProductlineHeader_prod_group_id').val(),
				stuom_id		: $('#SOProductlineHeader_stuom_id').val(),
				tax_rate_id		: $('#SOProductlineHeader_tax_rate_id').val(),
				ajax			: ''
			}
		});
		
	});

	$('#SOProductlineHeader_glaccount_id',"#sales_order-soproductlineheaders-new").live('change', function() {
		
		var $self = $(this);
		
		$('#SOProductlineHeader_glcentre_id').uz_ajax({
			data:{
				module			: 'sales_order',
				controller		: 'soproductlineheaders',
				action			: 'getCentres',
				glaccount_id	: $self.val(),
				ajax			: ''
			}
		});
		
	});	
	
	/* sales_order -> soproductlines -> new */
	
	$('#SOProductline_productline_header_id',"#sales_order-soproductlines-new").live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element	: '#header_data',
					field	: "header_data"
				},
				{
					element	: '#SOProductline_description',
					field	: "description"
				},
				{
					element	: '#SOProductline_discount',
					field	: "discount"
				},
				{
					element	: '#SOProductline_price',
					field	: "price"
				},
				{
					element	: '#SOProductline_glaccount_id',
					field	: "glaccount_id",
					action	: "selected"
				},
				{
					element	: '#SOProductline_start_date',
					field	: "start_date"
				},
				{
					element	: '#SOProductline_end_date',
					field	: "end_date"
				}
			],
			data:{
				module					: 'sales_order',
				controller				: 'soproductlines',
				action					: 'getHeaderData',
				productline_header_id	: $('#SOProductline_productline_header_id').val(),
				slmaster_id				: $('#SOProductline_slmaster_id').val(),
				ajax					: ''
			}
		});
		
	});

	$('#SOProductline_slmaster_id',"#sales_order-soproductlines-new").live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:{
				element	: "#SOProductline_currency_id",
				action	: "selected"
			},
			data:{
				module		: 'sales_order',
				controller	: 'soproductlines',
				action		: 'getCurrency',
				slmaster_id	: $self.val(),
				ajax		: ''
			}
		});
		
		$.uz_ajax({
			target:{
				element	: "#SOProductline_so_price_type_id",
				action	: "selected"
			},
			data:{
				module			: 'sales_order',
				controller		: 'soproductlines',
				action			: 'get_price_type',
				slmaster_id		: $('#SOProductline_slmaster_id').val(),
				ajax			: ''
			}
		});
	
		$('#SOProductline_discount').uz_ajax({
			data:{
				module			: 'sales_order',
				controller		: 'soproductlines',
				action			: 'getPriceDiscount',
				prod_group_id	: $('#SOProductlineHeader_prod_group_id').val(),
				stitem_id		: $('#SOProductlineHeader_stitem_id').val(),
				slmaster_id		: $('#SOProductline_slmaster_id').val(),
				ajax			: ''
		}
		});
	
	});

	$('#SOProductline_customer_product_code',"#sales_order-soproductlines-new").live('change', function() {
		$('#SOProductline_description').val($(this).val());
	});
	
	$('#SOProductline_price',"#sales_order-soproductlines-new").live('change', function() {
		$('#SOProductline_net_price').val($('#SOProductline_price').val()*(1-($('#SOProductline_discount').val()/100))); //.trigger("change");
		$('#SOProductline_price').val(roundNumber($(this).val(), 2));
		$('#SOProductline_net_price').trigger("change");
		// ATTENTION: does the trigger change need to be on the last or first line?
	});

	$('#SOProductline_discount',"#sales_order-soproductlines-new").live('change', function() {
		var discount	= $('#SOProductline_discount').val();
		discount		= isNaN(parseFloat(discount))?0:parseFloat(discount);
		$('#SOProductline_discount').val(discount.toFixed(2));
		$('SOProductline_net_price').val($('#SOProductline_price').val()*(1-($('#SOProductline_discount').val()/100))).trigger("change");	
	});	
	
	$('#SOProductline_net_price',"#sales_order-soproductlines-new").live('change', function() {
		$(this).val(roundNumber($(this).val(), 2));
	});	
		
	$('#SOProductline_glaccount_id',"#sales_order-soproductlines-new").live('change', function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:	[{
						element			: '#SOProductline_glcentre_id',
						field			: "glcentre_id",
						selected: {
							value: $('#SOProductlineHeader_glcentre_id').val()
						}
					}],
			data:{
				module			: 'sales_order',
				controller		: 'soproductlines',
				action			: 'getCentres',
				glaccount_id	: $self.val(),
				ajax			: ''
			}
		});
		
	});	
	
	/* sales_order -> soproductlines -> price_uplift */
	
	$('table.price_uplift input', "#sales_order-soproductlines-price_uplift").live('change', function() {
		
		var $self	= $(this),
			row		= $self.data('row');
		
		$.uz_ajax({
			data:{
				module			: 'sales_order',
				controller		: 'soproductlines',
				action			: 'adjust_price_uplift',
				id				: row,
				new_price		: $('#SOProductline_new_price'+row).val(),
				select			: $('#SOProductline_select'+row).is(':checked'),
				current_page	: $('#current_page').val(),
				ajax			: ''
			}
		});
		
	});
	
	$('#saveprices',"#sales_order-soproductlines-price_uplift").live('click', function(event) {
		
		event.preventDefault();
		
		$("#dialog").dialog({ autoOpen: false })
		$("#updatedialog").dialog({ autoOpen: false })
		
		// get form elements and variable
		var form		= $(this).parents('form'),
			form_data	= form.serialize() + "&ajax=''",
			result		= 'false';
		
		// fire the ajax to output the document
		$.uz_ajax({
			async		: false,
			type		: 'POST',
			url			: form.attr('action') + "&ajax=",
			data		: form_data + "&",
			dataType	: "html", // ATTN: html for numeric data? JSON please
			success: function(data) {
				num_pages = data;
			}
		});
				
		if (num_pages !== 'false') {
			
			var count = 0;
			
			$("#dialog").dialog('open');
			$("#dialogprogressbar").progressbar({ value:0, showText: true, textFormat: 'percentage'});
			
			for (i=0; i<num_pages; i++) { 
				$.getJSON("?module=sales_order&controller=soproductlines&action=save_price_uplift_pages&page="+(i+1)+"&ajax=", function(data) {
					count++;
					progress=count*100/num_pages;
					$("#dialogprogressbar").progressbar({ value: progress, showText: true, textFormat: 'percentage'});
					if (count>=num_pages) {
						$("#dialog").dialog('close');
						update_prices(num_pages, form_data);
					}
				});
			}
			
		} else {
		
			end_price_uplift(form_data);

		}
	});

	/* sales_order -> soproductlines -> delete_selected */
	
	// Progress Bar for deleting unused soproductlines
	
	$('input[type=submit][name=save]', '#sales_order-soproductlines-paging_select').live('click', function (event) {
		
		var form = $(event.currentTarget).parents('form');
		var $_GET = getQueryParams(form.attr('action'));

		if ($_GET.action=='delete_selected') {
			
			event.preventDefault();
			
			options = {main_url 		: form.attr('action')
					  ,data				: form.serialize()
					  ,type				: 'POST'
				  	  ,progress_url		: "/?module=sales_order&controller=soproductlines&action=getprogress&monitor_name=soproductline_delete_unused&ajax="
					  ,heading			: "Delete Unused SO Product Lines"
					  ,success_message	: "Delete Unused SO Product Lines OK"
					  ,fail_message		: "Delete Unused SO Product Lines Failed"
			};
			
			uz_progressbar(options);
		}
		
	});
	
	/* sales_order -> sorders -> select_products */
	
	$('input:checkbox',"#sales_order-sorders-select_products").live('click', function() {
		$('#products_text').val($(this).attr('rel')+'='+$(this).prop('checked')).trigger("change");
	});

	$('#products_text',"#sales_order-sorders-select_products").live('change', function() {
		
		var $self = $(this);
		
		$('#products').uz_ajax({
			data:{
				module		: 'sales_order',
				controller	: 'sorders',
				action		: 'showProducts',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});

	/* sales_order -> sorders -> select_products -child-> showproducts */
	
	$('button.remove',"#sales_order-sorders-showproducts").live('click', function(event) {
		event.preventDefault();
		$('#checkbox_'+$(this).attr('rel')).removeAttr('checked');
		$('#products_text').val($(this).attr('rel')+'=false').trigger("change");
	});
		
	/* sales_order -> sorders -> confirm_pick_list */
	
	$('input.select_all',"#sales_order-sorders-confirm_pick_list").live('click', function() {
		checkAll($(this),$("input[type=checkbox]",$(this).parents('form')));
	});
	
	$('select',"#sales_order-sorders-confirm_pick_list").live('change', function() {
		
		var $self	= $(this),
			line_id	= $self.parents('tr').data('line');

		if ($self.attr('id') == 'SOrderLine_whlocation_id'+line_id) {
			
			$('#SOrderLine_balance'+line_id).uz_ajax({
				highlight: false,
				data:{
					module:'sales_order',
					controller:'sorders',
					action:'getBalance',
					stitem_id:$('#SOrderLine_stitem_id'+line_id).val(),
					location_id:$self.val(),
					ajax:''
				}
			});
			
		}
		
	});

	$('input',"#sales_order-sorders-confirm_pick_list").live('change', function() {
		
		var line_id=$(this).parents('tr').data('line');

		switch($(this).attr('id')) {
			case 'SOrderLine_balance'+line_id:
				var balance=parseFloat($('#SOrderLine_balance'+line_id).val());
				var os_qty=parseFloat($('#SOrderLine_os_qty'+line_id).val());
				var del_qty=parseFloat($('#SOrderLine_del_qty'+line_id).val());
			
				if (balance>=os_qty) {
					$('#SOrderLine_del_qty'+line_id).val($('#SOrderLine_os_qty'+line_id).val());
				} else if (balance<os_qty) {
					$('#SOrderLine_del_qty'+line_id).val($('#SOrderLine_balance'+line_id).val());
				}
				break;
			case 'SOrderLine_del_qty'+line_id:
				var balance=parseFloat($('#SOrderLine_balance'+line_id).val());
				var os_qty=parseFloat($('#SOrderLine_os_qty'+line_id).val());
				var del_qty=parseFloat($('#SOrderLine_del_qty'+line_id).val());
			
				if (balance<del_qty) {
					alert('Trying to pick '+del_qty+' but only '+balance+' available at this location');
					$('#SOrderLine_del_qty'+line_id).val($('#SOrderLine_balance'+line_id).val());
				} else if (os_qty<del_qty) {
					alert('Trying to pick '+del_qty+' but outstanding qty is '+os_qty);
					$('#SOrderLine_del_qty'+line_id).val($('#SOrderLine_os_qty'+line_id).val());
				} else if (del_qty<0) {
					alert('picked qty cannot be negative');
					if (balance<os_qty) {
						$('#SOrderLine_del_qty'+line_id).val($('#SOrderLine_balance'+line_id).val());
					} else {
						$('#SOrderLine_del_qty'+line_id).val($('#SOrderLine_os_qty'+line_id).val());
					}	
				}
				break;
		}
	});

	/* sales_order -> sorders -> unconfirm_pick_list */
	
	$('input.select_all',"#sales_order-sorders-unconfirm_pick_list").live('click', function() {
		checkAll($(this),$("input[type=checkbox]",$(this).parents('form')));
	});
	
	/* sales_order -> sorders -> pro_forma */
		
	$('form','#sales_order-sorders-pro_forma').live('submit', function(event) {
		
		// just make sure we're not clicking the cancel button
		if (!$(this).find('#cancelform').length) {
			
			event.preventDefault();
			
			var self = $(this);
			
			// check to make sure at least one line is selected
			if($('#order_lines input[type=checkbox]:checked').length==0) {
				alert("Please select at least one line to be printed");
				return false;
			}
			
			// set the url
			var url = self.attr('action')+'&id='+$('#SOrder_id').val();
			
			// envoke the print dialog box
			// ATTENTION: is this going to work, havn't we changed the dialog name?
			uz_print_dialog({url:url,data:self.serialize()});
		
		}
		
	});
	
	/* sales_order -> sorders -> select_print_item_labels */
	
	$('input.select_all', '#sales_order-sorders-select_print_item_labels').on('click', function() {
		$.fn.checkAll($("input[type=checkbox]",$(this).parents('form')));
	});
	
	$('input.select_picked', '#sales_order-sorders-select_print_item_labels').on('click', function() {
		$("input[type=checkbox]",$(this).parents('form')).each(function() {
			// clear before selecting picked lines
			if ($(this).is(':checked')) {
				$(this).attr('checked', false).trigger("change");
				$(this).data("status", false)
			}
			$.fn.checkAll($("td[data-line_status='S'] input[type=checkbox]",$(this).parents('form')));
		});
	});
	
	$('form','#sales_order-sorders-select_print_item_labels').on('submit', function(event) {
		
		// just make sure we're not clicking the cancel button
		if (!$(this).find('#cancelform').length) {
			
			event.preventDefault();
			
			var self = $(this);
			
			// check to make sure at least one line is selected
			if($('#view_data_bottom input[type=checkbox]:checked').length==0) {
				$(function() {
					$( '#selection-warning' ).dialog({
						modal: true,
						buttons: {
							Ok: function() {
								$( this ).dialog( "close" );
							}
						}
					});
				});
				return false;
			} else {
				this.submit();
			}
		}
	});
	
	/* sales_order -> sorders -> viewpacking_slips */
	
	$('form input[type=submit]', '#sales_order-sorders-viewpacking_slips').live('click', function(event) {
	
		event.preventDefault();

		var $self	= $(this),
			form	= $self.parents('form');
		
		// check to make sure at least one line is selected
		if($('input[type=checkbox]:checked',form).length==0) {
			alert("Please select at least one line to be printed");
			return false;
		}
		
		// set the url
		var url = form.attr('action')+'&id='+$('#SOrder_id').val();
		
		// envoke the print dialog box
		uz_print_dialog({
			url		: url,
			data	: form.serialize()
		});
		
	});
	
});

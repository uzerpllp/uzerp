 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * sales_order.uzlet.js
 * 
 * $Revision: 1.1 $
 * 
 */


$(document).ready(function() {

	/* Prevent submit on price check uzlet */
	$('div#main_without_sidebar').on('submit', 'div#sales_order-soproductlines-sales_price_check form', function(){
		return false;
	});

	/* sales_order -> soproductlines -> sales_price_check */
	
	$("div#main_without_sidebar").on('change', "#sales_order-soproductlines-sales_price_check #SOProductline_product_search", function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:{
				element	: '#SOProductline_productline_id'
			},
			data:{
				module				: 'sales_order',
				controller			: 'soproductlines',
				action				: 'getProductLines',
				product_search		: $('#SOProductline_product_search').val(),
				slmaster_id			: $('#SOProductline_slmaster_id').val(),
				so_price_type_id	: $('#SOProductline_so_price_type_id').val(),
				limit				: '50',
				ajax				: ''
			}
		});

	});

	$("div#main_without_sidebar").on('change', "#sales_order-soproductlines-sales_price_check #SOProductline_productline_id", function() {
			
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element	: '#SOProductline_currency',
					field	: "currency"
				},
				{
					element	: '#SOProductline_product_price',
					field	: "product_price"
				},
		        {
					element	: '#SOProductline_discount_percent',
					field	: "discount_percent"
				},
		        {
					element	: '#SOProductline_discount_value',
					field	: "discount_value"
				},
		        {
					element	: '#SOProductline_price',
					field	: "net_price"
				},
		        {
					element	: '#SOProductline_vat',
					field	: "vat"
				},
		        {
					element	: '#SOProductline_gross',
					field	: "gross"
				},
                        {
                                        element : '#SOProductline_sales_stock',
                                        field   : "sales_stock"
                                }

			],
			data:{
				module			: 'sales_order',
				controller		: 'soproductlines',
				action			: 'get_price',
				productline_id	: $('#SOProductline_productline_id').val(),
				slmaster_id		: $('#SOProductline_slmaster_id').val(),
				ajax			: ''
			}
		});

	});

	$("div#main_without_sidebar").on('change', "#sales_order-soproductlines-sales_price_check #SOProductline_slmaster_id", function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:{
				element	: '#SOProductline_so_price_type_id',
//				field	: "price_type",
				action	: "selected"
			},
			data:{
				module		: 'sales_order',
				controller	: 'soproductlines',
				action		: 'get_price_type',
				slmaster_id	: $('#SOProductline_slmaster_id').val(),
				ajax		: ''
			}
		});
		
		$.uz_ajax({
			target:{
				element	: '#SOProductline_productline_id'
			},
			data:{
				module				: 'sales_order',
				controller			: 'soproductlines',
				action				: 'getProductLines',
				product_search		: $('#SOProductline_product_search').val(),
				slmaster_id			: $('#SOProductline_slmaster_id').val(),
				so_price_type_id	: $('#SOProductline_so_price_type_id').val(),
				limit				: '50',
				ajax				: ''
			}
		});

	});
	
	$("div#main_without_sidebar").on('change', "#sales_order-soproductlines-sales_price_check #SOProductline_so_price_type_id", function() {
		
		var $self = $(this);
		
		$.uz_ajax({
			target:{
				element	: '#SOProductline_productline_id'
			},
			data:{
				module				: 'sales_order',
				controller			: 'soproductlines',
				action				: 'getProductLines',
				product_search		: $('#SOProductline_product_search').val(),
				slmaster_id			: $('#SOProductline_slmaster_id').val(),
				so_price_type_id	: $('#SOProductline_so_price_type_id').val(),
				limit				: '50',
				ajax				: ''
			}
		});

	});
	
});

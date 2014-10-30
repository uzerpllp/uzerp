/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * purchase_invoicing.js
 * 
 * $Revision: 1.13 $
 * 
 */

$(document).ready(function() {
	
	/* purchase_invoicing -> pinvoices -> new */
	
	$("#PInvoice_plmaster_id", "#purchase_invoicing-pinvoices-new").live('change', function(){
		
		var $self = $(this);
		
		$('#notes').uz_ajax({
			data:{
				module			: 'purchase_invoicing',
				controller		: 'pinvoices',
				action			: 'getNotes',
				supplier_id		: $self.val(),
				ajax			: ''
			}
		});
		
	});
	
	/* purchase_invoicing -> pinvoices -> new .... get task list from project */
	
	$("#PInvoice_project_id", "#purchase_invoicing-pinvoices-new").live('change',function(){
		
		$.uz_ajax({
			target:{
				element	: "#PInvoice_task_id",
				field	: 'task_id'
		    },
			data:{
				module		: 'purchase_invoicing',
				controller	: 'pinvoices',
				action		: 'getTaskList',
				project_id	: $('#PInvoice_project_id').val(),
				ajax		: ''
			}
		});
		
	});

	
	/* purchase_invoicing -> pinvoices -> view */
	
	$(".edit-line a, .add_lines_related a").live('click',function(event){
		
		event.preventDefault();
		
		if ($(this).parent('li').hasClass('add_lines_related')) {
			var title='Add Purchase Invoice Line';
			var type='add';
		} else {
			var title='Edit Purchase Invoice Line';
			var type='edit';
		}
		
		formDialog({
			title		: title,
			calledby	: $('#included_file').find('.content_wrapper').attr('id'),
			id			: 'pinvoicelines',
			url			: $(this).attr('href'),
			type		: type,
			height		: 550,
			width		: 550,
			resizable	: true
		});
		
	});
	
	/* purchase_invoicing -> pinvoicelines -> new */

	$("select, input","#purchase_invoicing-pinvoicelines-new").live('change',function() {
		
		var $self	= $(this),
        	field	= $self.data('field');
	
		switch(field) {
		
			case "net_value":
			case "tax_value":
				$(this).val(roundNumber($(this).val(), 2)); 
				addValues($('#PInvoiceLine_net_value'),$('#PInvoiceLine_tax_value'), $('#PInvoiceLine_gross_value'));
				$('#PInvoiceLine_gross_value').trigger("change");
				break;
				
			case "glaccount_id":

				$('#PInvoiceLine_glcentre_id').uz_ajax({
					data:{
						module		: 'purchase_invoicing',
						controller	: 'pinvoicelines',
						action		: 'getCentres',
						id			: $self.val(),
						ajax		: ''
					}
				});
				break;
				
		}
		
	});

});
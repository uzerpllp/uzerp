/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * engineering.js
 * 
 * $Revision: 1.1 $
 * 
 */

$(document).ready(function() {
	
	/* engineering -> stitems -> new */

	$("#STItem_comp_class","#engineering-stitems-new").live('change',function() {
		if($(this).val()=='B') {
			$('#latest_mat_container').show();
		} else {
			$('#latest_mat_container').hide();
		}
	});
	
	/* engineering -> workschedules -> issues_returns */

	$("#STTransaction_whaction_id, #engineering-workschedules-issues_returns #STTransaction_stitem_id, #engineering-workschedules-issues_returns #STTransaction_from_whlocation_id, #engineering-workschedules-issues_returns #STTransaction_from_whbin_id, #engineering-workschedules-issues_returns #STTransaction_to_whlocation_id", "#engineering-workschedules-issues_returns").live('change',function(){
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element			: '#STTransaction_from_whlocation_id',
					field			: "from_whlocation_id",
					force_change	: false,
					selected: {
						value: $('#STTransaction_from_whlocation_id').val()
					}
				},
				{
					element			: '#STTransaction_from_whbin_id',
					field			: "from_whbin_id",
					force_change	: false,
					selected: {
						value: $('#STTransaction_from_whbin_id').val()
					}
				},
				{
					element	: '#STTransaction_uom_name',
					field	: "uom_name"
				},
				{
					element	: '#STTransaction_balance',
					field	: "balance"
				},
				{
					element			: '#STTransaction_to_whlocation_id',
					field			: "to_whlocation_id",
					force_change	: false
				},
				{
					element	: '#STTransaction_to_whbin_id',
					field	: "to_whbin_id"
				},
				{
					element	: '#STTransaction_issued_qty',
					field	: "issued_qty"
				},
				{
					element	: '#STTransaction_used_qty',
					field	: "used_qty"
				},
				{
					element	: '#STTransaction_qty',
					field	: "required_qty"
				},
				{
					element	: '#STTransaction_issues_list',
					field	: "sttransactions"
				}
			],
			data:{
				module				: 'engineering',
				controller			: 'workschedules',
				action				: 'getTransferDetails',
				entry_point			: $self.attr('id'),
				whaction_id			: $('#STTransaction_whaction_id').val(),
				type_text			: $('#STTransaction_type_text').val(),
				work_order_id		: $('#STTransaction_process_id').val(),
				stitem_id			: $('#STTransaction_stitem_id').val(),
				from_whlocation_id	: $('#STTransaction_from_whlocation_id').val(),
				from_whbin_id		: $('#STTransaction_from_whbin_id').val(),
				to_whlocation_id	: $('#STTransaction_to_whlocation_id').val(),
				ajax				: ''
			}
		});
		
	});

	/* engineering -> sttransactions -> new */

	$("#STTransaction_stitem_id, #STTransaction_from_whlocation_id, #STTransaction_from_whbin_id, #STTransaction_to_whlocation_id", "#engineering-sttransactions-new").live('change',function(){
		
		var $self = $(this);
		
		$.uz_ajax({
			target:[
				{
					element			: '#STTransaction_from_whlocation_id',
					field			: "from_whlocation_id",
					force_change	: false,
					selected: {
						value: $('#STTransaction_from_whlocation_id').val()
					}
				},
				{
					element			: '#STTransaction_from_whbin_id',
					field			: "from_whbin_id",
					force_change	: false,
					selected: {
						value: $('#STTransaction_from_whbin_id').val()
					}
				},
				{
					element	: '#STTransaction_uom_id',
					field	: "uom_id"
				},
				{
					element	: '#STTransaction_balance',
					field	: "balance"
				},
				{
					element			: '#STTransaction_to_whlocation_id',
					field			: "to_whlocation_id",
					force_change	: false
				},
				{
					element	: '#STTransaction_to_whbin_id',
					field	: "to_whbin_id"
				}
			],
			data:{
				module				: 'engineering',
				controller			: 'sttransactions',
				action				: 'getTransferDetails',
				entry_point			: $self.attr('id'),
				whaction_id			: $('#STTransaction_whaction_id').val(),
				stitem_id			: $('#STTransaction_stitem_id').val(),
				from_whlocation_id	: $('#STTransaction_from_whlocation_id').val(),
				from_whbin_id		: $('#STTransaction_from_whbin_id').val(),
				to_whlocation_id	: $('#STTransaction_to_whlocation_id').val(),
				ajax				: ''
			}
		});
		
	});

});
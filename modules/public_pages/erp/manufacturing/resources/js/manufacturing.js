/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * manufacturing.js
 * 
 * $Revision: 1.19 $
 * 
 */

$(document).ready(function(){
    if ( $("#STItem_comp_class","#manufacturing-stitems-new").val() != 'M') {
        $('#cost_basis_container').hide();
    };


    /* manufacturing -> stitems -> clone_item */
    
    // Ensure copy product is selected with copy product lines
    $('#STItem_copy_so_product_prices').on('click', function() {
        if(this.checked) {
            $('#STItem_copy_so_products').attr('checked', true);
        }
    });
    
    // Uncheck copy product lines when copy product us unchecked
    $('#STItem_copy_so_products').on('click', function() {
        if($('#STItem_copy_so_product_prices').prop("checked")) {            
            $('#STItem_copy_so_product_prices').attr('checked', false);
        }
    });

	/* manufacturing -> stitems -> new */

	$("#STItem_comp_class","#manufacturing-stitems-new").live('change',function() {
		if($(this).val()=='B') {
			$('#latest_mat_container').show();
		} else {
			$('#latest_mat_container').hide();
		}
	});
	
	$("#STItem_comp_class","#manufacturing-stitems-new").live('change',function() {
		if($(this).val()=='M') {
			$('#cost_basis_container').show();
		} else {
			$('#cost_basis_container').hide();
		}
	});

	/* manufacturing -> mfwostructures -> new */
	
	$("#MFWOStructure_ststructure_id","#manufacturing-mfwostructures-new").live('change',function(){
		
		var $self = $(this);
		
		$('#MFWOStructure_uom_id').uz_ajax({
			data:{
				module		: 'manufacturing',
				controller	: 'STItems',
				action		: 'getUomList',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	/* manufacturing -> works orders -> new */
	
	$("#MFWorkorder_stitem_id","#manufacturing-mfworkorders-new").live('change',function(){
		
		var $self = $(this);
		
		$('#MFWorkorder_stuom_id').uz_ajax({
			data:{
				module		: 'manufacturing',
				controller	: 'mfworkorders',
				action		: 'getUomList',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
 
	$("#MFWorkorder_order_id","#manufacturing-mfworkorders-new").live('change',function(){
		
		var $self = $(this);
		
		$('#MFWorkorder_orderline_id').uz_ajax({
			data:{
				module			: 'manufacturing',
				controller		: 'mfworkorders',
				action			: 'getOrderLines',
				order_id		: $self.val(),
				orderline_id	: $('#MFWorkorder_current_orderline_id').val(),
				ajax			: ''
			}
		});
		
	});
 
	/* manufacturing -> works orders -> issues */

	$("#STTransaction_whaction_id, #manufacturing-mfworkorders-issues_returns #STTransaction_stitem_id, #manufacturing-mfworkorders-issues_returns #STTransaction_from_whlocation_id, #manufacturing-mfworkorders-issues_returns #STTransaction_from_whbin_id, #manufacturing-mfworkorders-issues_returns #STTransaction_to_whlocation_id", "#manufacturing-mfworkorders-issues_returns").live('change',function(){
		
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
				module				: 'manufacturing',
				controller			: 'mfworkorders',
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

	/* mfworksorders -> book over / under */
	
	$(".manufacturing-mfworkorders-bookoverunder #from_location").live('change',function(){
		
		var $self = $(this);
		
		$('#to_location').uz_ajax({
			data:{
				module			: 'manufacturing',
				controller		: 'mfworkorders',
				action			: 'getToLocations',
				whlocation_id	: $self.val(),
				whaction_id		: $('#MFWorkorder_whaction_id').val(),
				ajax			: ''
			}
		});
		
		$('#from_bin').uz_ajax({
			data:{
				module			: 'manufacturing',
				controller		: 'mfworkorders',
				action			: 'getBinList',
				whlocation_id	: $self.val(),
				ajax			: ''
			}
		});
		
	});
	
	$(".manufacturing-mfworkorders-bookoverunder #to_location").live('change',function(){
		
		var $self = $(this);
		
		$('#to_bin').uz_ajax({
			data:{
				module			: 'manufacturing',
				controller		: 'mfworkorders',
				action			: 'getBinList',
				whlocation_id	: $self.val(),
				ajax			: ''
			}
		});
		
	});

	/* manufacturing -> whtransfers -> new */
	
	$('#manufacturing-whtransfers-new').uz_grid({
		rowAdded: function(row) {
			legacyForceChange('#stitems_'+row);
		},
		rowRemoved: function() {}
	});
	
	$("#WHTransfer_transfer_action","#manufacturing-whtransfers-new").live('change',function(){
		
		var $self = $(this);
		
		$('#WHTransfer_from_whlocation').uz_ajax({
			data:{
				module		: 'manufacturing_setup',
				controller	: 'whtransferrules',
				action		: 'getFromLocations',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#WHTransfer_from_whlocation","#manufacturing-whtransfers-new").live('change',function() {
		
		var $self = $(this);
		
		$('#WHTransfer_to_whlocation').uz_ajax({
			data:{
				module		: 'manufacturing_setup',
				controller	: 'whtransferrules',
				action		: 'getToLocations',
				id			: $self.val(),
				whaction_id	: $('#WHTransfer_transfer_action').val(),
				ajax		: ''
			}
		});
		
		$('#stitems').uz_ajax({
			data:{
				module		: 'manufacturing',
				controller	: 'stitems',
				action		: 'getStockAtLocation',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
		
	$("#to_location","#manufacturing-whtransfers-new").live('change',function(){
		
		var $self = $(this);
		
		$('#to_bin').uz_ajax({
			data:{
				module		: 'manufacturing',
				controller	: 'mfworkorders',
				action		: 'getBinList',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	/* manufacturing -> whtransfers -> new -> lines */
	
	$(".uz-grid-table tr select, .uz-grid-table tr input","#manufacturing-whtransfers-new").live('change',function(){
		
		var $self	= $(this),
			field	= $self.data('field'),
			row		= 'row' + $(this).parents('tr').data('row-number');
		
		switch(field) {
			case "stitem_id":

				$.uz_ajax({
					target:[
						{
							element	: '#stuom_id_'+row,
							field	: "stuom_id"
						},
						{
							element	: '#uom_name_'+row,
							field	: "uom_name"
						},
						{
							element	: '#available_qty_'+row,
							field	: "available_qty"
						}
					],
					data:{
						module			: 'manufacturing',
						controller		: 'STItems',
						action			: 'getWhtransfersLineData',
						id				: $self.val(),
						whlocation_id	: $('#WHTransfer_from_whlocation').val(),
						ajax			: ''
					}
				});
				
				break;					
		}
		
	});
	
	/* manufacturing -> sttransactions -> new */

	$("#STTransaction_stitem_id, #STTransaction_from_whlocation_id, #STTransaction_from_whbin_id, #STTransaction_to_whlocation_id", "#manufacturing-sttransactions-new").live('change',function(){
		
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
				module				: 'manufacturing',
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

	/* manufacturing -> mfstructures -> new */
		
	$("#MFStructure_stitem_id", "#manufacturing-mfstructures-new").live('change',function() {
		
		$.uz_ajax({
			target:{
				element:"#MFStructure_ststructure_id",
				selected: {
					value: $('#MFStructure_ststructure_id').val()
				}
			},
			data:{
				module		: 'manufacturing',
				controller	: 'mfstructures',
				action		: 'getItems',
				date		: encodeURIComponent($('#MFStructure_start_date').val()),
				stitem_id	: $('#MFStructure_stitem_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#MFStructure_start_date", "#manufacturing-mfstructures-new").live('change',function() {
		
		var $self			= $(this),
			selected_option = $self.val();
		
		$.uz_ajax({
			target:{
				element:"#MFStructure_ststructure_id",
				selected: {
					value: $('#MFStructure_ststructure_id').val()
				}
			},
			data:{
				module		: 'manufacturing',
				controller	: 'mfstructures',
				action		: 'getItems',
				date		: encodeURIComponent($self.val()),
				stitem_id	: $('#MFStructure_stitem_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#MFStructure_ststructure_id","#manufacturing-mfstructures-new").live('change',function(){
		
		var $self = $(this);
		
		$('#MFStructure_uom_id').uz_ajax({
			data:{
				module		: 'manufacturing',
				controller	: 'STItems',
				action		: 'getUomList',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});	
	
	/* manufacturing -> mfstructures -> view */
	
	$("#MFStructure_start_date", "#manufacturing-mfstructures-view").live('change',function() {
		
		var $self			= $(this),
			selected_option = $self.val();

		$.uz_ajax({
			target:{
				element:"#MFStructure_ststructure_id",
				selected: {
					value: $('#MFStructure_ststructure_id').val()
				}
			},
			data:{
				module		: 'manufacturing',
				controller	: 'mfstructures',
				action		: 'getItems',
				date		: encodeURIComponent($self.val()),
				ajax		: ''
			}
		});
		
	});
	
	$("#MFStructure_ststructure_id","#manufacturing-mfstructures-view").live('change',function(){
		
		var $self = $(this);
		
		$('#MFStructure_uom_id').uz_ajax({
			data:{
				module		: 'manufacturing',
				controller	: 'STItems',
				action		: 'getUomList',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});	

	/* manufacturing -> mfoperations -> new */
	
	$("#MFOperation_stitem_id", "#manufacturing-mfoperations-new").live('change',function() {
		
		$.uz_ajax({
			target:[
					{
						element	: "#MFOperation_volume_uom_id",
						field	: 'uom_list'
					},
					{
						element	: "#show_parts",
						field	: 'show_parts'
					}
			],
			data:{
				module		: 'manufacturing',
				controller	: 'mfoperations',
				action		: 'getItemData',
				stitem_id	: $('#MFOperation_stitem_id').val(),
				ajax		: ''
			}
		});
		
	});

	$( "#manufacturing-mfoperations-new form #MFOperation_type" ).on( "change", function() {

		if($(this).val() == 'O') {
			$(".all-type").hide();
			$(".o-type").show();
		} else {
			$(".all-type").show();
			$(".o-type").hide();
		}
		
	});
	
});

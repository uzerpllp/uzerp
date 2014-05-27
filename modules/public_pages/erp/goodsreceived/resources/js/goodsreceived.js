/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * goodsreceived.js
 * 
 * $Revision: 1.1 $
 * 
 */

$(document).ready(function() {
		
	/* goodsreceived -> poreceivedlines -> print_label */
	
	$("input.pallet_count", "#goodsreceived-poreceivedlines-print_label").live('change', function(){
		
		var rownum 		= $(this).data('row-number')
			,count		= $(this).val()
			,quantity	= $("#POReceivedLine_received_qty"+rownum).val()
			,dec_pl		= $("#POReceivedLine_qty_decimals"+rownum).val();
		
		count = isNaN(parseFloat(count))?0:parseFloat(count);
		quantity = isNaN(parseFloat(quantity))?0:parseFloat(quantity);
		
		var pallet_qty = 0;
		
		if (count>0) {
			pallet_qty = quantity / count;
		}
		
		$("#POReceivedLine_pallet_qty"+rownum).val(pallet_qty.toFixed(dec_pl));
		
	});
	
	$("input.item_count", "#goodsreceived-poreceivedlines-print_label").live('change', function(){
		
		var rownum 		= $(this).data('row-number')
			,count		= $(this).val()
			,quantity	= $("#POReceivedLine_received_qty"+rownum).val()
			,dec_pl		= $("#POReceivedLine_qty_decimals"+rownum).val();
		
		count = isNaN(parseFloat(count))?0:parseFloat(count);
		quantity = isNaN(parseFloat(quantity))?0:parseFloat(quantity);
		
		var item_qty = 0;
		
		if (count>0) {
			item_qty = quantity / count;
		}
		
		
		$("#POReceivedLine_item_qty"+rownum).val(item_qty.toFixed(dec_pl));
		
	});
	
});
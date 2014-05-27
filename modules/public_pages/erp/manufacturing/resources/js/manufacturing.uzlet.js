 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * manufacturing.js
 * 
 * $Revision: 1.1 $
 * 
 */

$(document).ready(function(){

	/* eglet -> manufacturing -> multi_bin_balances_print */
	
	$("#WHStore_store", "#eglet-manufacturing-multi_bin_balances_print").live('change', function() {
		
		var $self	= $(this),
			$eglet	= $self.parents('.eglet');
		
		$('#WHLocation_location').uz_ajax({
			data:{
				module		: 'manufacturing',
				controller	: 'Whstores',
				action		: 'getBinLocationList',
				id			: $self.val(),
				ajax		: ''
			},
			block_method: 'block_multi_bin_balances_print',
			block: function() {
				$eglet.block({ message: null });
			},
			unblock: function() {
				$eglet.unblock();
			}
		});

	});
	
	$("#WHLocation_location", "#eglet-manufacturing-multi_bin_balances_print").live('change', function() {
		
		var $self	= $(this),
			$eglet	= $self.parents('.eglet');
		
		$('#WHBin_bins').uz_ajax({
			data:{
				module		: 'manufacturing',
				controller	: 'Whlocations',
				action		: 'getBinList',
				id			: $self.val(),
				ajax		: ''
			},
			block_method: 'block_multi_bin_balances_print',
			block: function() {
				$eglet.block({ message: null });
			},
			unblock: function() {
				$eglet.unblock();
			}
		});
		
	});

});

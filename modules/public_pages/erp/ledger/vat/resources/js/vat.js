 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * vat.js
 * 
 * 	$Revision: 1.7 $
 *
 */

$(document).ready(function(){

	/* vat -> vat -> enter_journal */
	
	$("#Vat_glaccount_id", "#vat-vat-enter_journal").live("change", function(){
		
		var $self = $(this);
		
		$('#Vat_glcentre_id').uz_ajax({
			data:{
				module		: 'vat',
				controller	: 'vat',
				action		: 'getCentres',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});	
		
});

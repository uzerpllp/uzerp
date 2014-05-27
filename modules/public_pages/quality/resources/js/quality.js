/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * quality.js
 * 
 * $Revision: 1.8 $
 * 
 */

$(document).ready(function(){

	/* quality -> sdcomplaints -> new */
	
	$("#RRComplaint_complaint_code_id","#quality-rrcomplaints-new").live("change", function(){
		
		var $self = $(this);
		
		$('#RRComplaint_supplementary_code_id').uz_ajax({
			data:{
				module		: 'quality',
				controller	: 'rrcomplaints',
				action		: 'getSuppComplaintCodes',
				id			: $self.val(),
				ajax		: ''
			},
			block_method: 'quality-save-button',
			block: function() {
				$('input[type=submit]').attr('disabled', 'disabled');
			},
			unblock: function() {
				$('input[type=submit]').removeAttr('disabled');
			}
		});
		
	});

	/* quality -> sdcomplaints -> new */
	
	$("#SDComplaint_complaint_code_id","#quality-sdcomplaints-new").live("change", function(){
		
		var $self = $(this);
		
		$('#SDComplaint_supplementary_code_id').uz_ajax({
			data:{
				module		: 'quality',
				controller	: 'sdcomplaints',
				action		: 'getSuppComplaintCodes',
				id			: $self.val(),
				ajax		: ''
			},
			block_method: 'quality-save-button',
			block: function() {
				$('input[type=submit]').attr('disabled', 'disabled');
			},
			unblock: function() {
				$('input[type=submit]').removeAttr('disabled');
			}
		});
		
	});

});
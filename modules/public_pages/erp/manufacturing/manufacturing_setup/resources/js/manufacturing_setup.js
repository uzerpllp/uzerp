 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * manufacturing.js
 * 
 * $Revision: 1.8 $
 * 
 */

$(document).ready(function(){

	/* manufacturing_setup -> whlocations -> new */
	
	$("#WHLocation_glaccount_id","#manufacturing_setup-whlocations-new").live('change', function(){
		
		var $self = $(this);
		
		$('#WHLocation_glcentre_id').uz_ajax({
			data:{
				module		: 'manufacturing_setup',
				controller	: 'whlocations',
				action		: 'getCentres',
				id			: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	$("#WHLocation_has_balance","#manufacturing_setup-whlocations-new").live('change', function(){
		$('#WHLocation_supply_demand').prop('checked',$('#WHLocation_has_balance').prop('checked'));
	});

	/* manufacturing_setup -> mfcentres -> new */
	
	$("#MFCentre_mfdept_id","#manufacturing_setup-mfcentres-new").live('change', function(){
		
		var $self = $(this);
		
		$('#MFDept_production_recording').uz_ajax({
			data:{
				module		: 'manufacturing_setup',
				controller	: 'mfcentres',
				action		: 'allow_production_recording',
				mfdept_id	: $self.val(),
				ajax		: ''
			}
		});
		
		$('#MFDept_production_recording').trigger('change');
		
	});

	$("#MFDept_production_recording","#manufacturing_setup-mfcentres-new").live('change', function(){
		
		if ($('#MFDept_production_recording').val()=='t') {
			$('#MFCentre_production_recording_label').show();
		} else {
			$('#MFCentre_production_recording_label').hide();
			$('#MFCentre_production_recording').prop('checked', false);
		}
		
	});

});

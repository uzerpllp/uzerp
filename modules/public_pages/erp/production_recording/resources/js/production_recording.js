/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * production_recording.js
 * 
 * $Revision: 1.9 $
 * 
 */

$(document).ready(function(){
	
	/* production_recording -> mfshifts -> new */
	
	$("#MFShift_mf_dept_id","#production_recording-mfshifts-new").live('change', function(){
		
		var $self = $(this);
		
		$('#MFShift_mf_centre_id').uz_ajax({
			data:{
				module		: 'production_recording',
				controller	: 'mfshifts',
				action		: 'getCentres',
				mf_dept_id	: $self.val(),
				ajax		: ''
			}
		});
		
	});
	
	/* production_recording -> mfshiftoutputs -> new */
	
	$(" #MFShiftOutput_stitem_id","#production_recording-mfshiftoutputs-new").live('change',function(){
		
		var $self = $(this);
/*		
		$('#MFShiftOutput_run_time_speed').uz_ajax({
			data:{
				module		: 'production_recording',
				controller	: 'mfshiftoutputs',
				action		: 'getRunTimeSpeed',
				stitem_id	: $self.val(),
				mfcentre_id	: $('#MFShift_mf_centre_id').val(),
				ajax		: ''
			}
		});
		
		$('#MFShiftOutput_uom_id').uz_ajax({
			data:{
				module		: 'production_recording',
				controller	: 'mfshiftoutputs',
				action		: 'getUomList',
				stitem_id	: $self.val(),
				ajax		: ''
			}
		});
		
		$('#MFShiftOutput_work_order_id').uz_ajax({
			data:{
				module		: 'production_recording',
				controller	: 'mfshiftoutputs',
				action		: 'getWorkordersList',
				stitem_id	: $self.val(),
				ajax		: ''
			}
		});
*/		
		
		$.uz_ajax({
			target:[
					{
						element	: '#MFShiftOutput_run_time_speed',
						field	: "run_time_speed",
					},
					{
						element	: '#MFShiftOutput_uom_id',
						field	: "uom_list"
					},
					{
						element	: '#MFShiftOutput_work_order_id',
						field	: "works_orders"
					}
					],
			data:{
				module		: 'production_recording',
				controller	: 'mfshiftoutputs',
				action		: 'getItemData',
				stitem_id	: $self.val(),
				mfcentre_id	: $('#MFShiftOutput_mf_centre_id').val(),
				ajax		: ''
			}
		});

	});
 
	/* production_recording -> mfshiftwastes -> new */
	
	$("#MFShiftWaste_mf_centre_waste_type_id","#production_recording-mfshiftwastes-new").live('change',function(){
		
		var $self = $(this);
		
		$('#MFShiftWaste_uom_name').uz_ajax({
			data:{
				module					: 'production_recording',
				controller				: 'mfshiftwastes',
				action					: 'getWasteUom',
				mf_centre_waste_type_id	: $self.val(),
				ajax					: ''
			}
		});
		
	});
 
});
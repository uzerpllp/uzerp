 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * edi.js
 * 
 * $Revision: 1.2 $
 * 
 */
$(document).ready(function(){

	/* edi -> datamappingdetails -> new */


	$("#DataMappingDetail_parent_id", "#edi-datamappingdetails-new").live("change", function(){
		
		$.uz_ajax({
			target: {
				element	: "#included_file",
			},
			data: {
				module					: 'edi',
				controller				: 'datamappingdetails',
				action					: 'new',
				parent_id				: $("#DataMappingDetail_parent_id").val(),
				external_code			: $("#DataMappingDetail_external_code").val(),
				data_mapping_rule_id	: $("#DataMappingDetail_data_mapping_rule_id").val(),
				ajax					: ''
			}
		});
		
	});
	
	/* edi -> datadefinitions -> upload_file */


	$("#DataDefinition_id", "#edi-datadefinitions-upload_file").live("change", function(){
		
		$.uz_ajax({
			target: [
			         {
			        	 element	: '#DataDefinition_local_name',
			        	 field		: "local_name"
			         },
			         {
			        	 element	: '#DataDefinition_working_folder',
			        	 field		: "working_folder",
			         }
			],
			data: {
				module		: 'edi',
				controller	: 'datadefinitions',
				action		: 'getDefinitionDetail',
				datadef_id	: $("#DataDefinition_id").val(),
				ajax		: ''
			}
		});
		
	});
	
});

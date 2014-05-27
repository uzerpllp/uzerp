/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * developer.js
 * 
 * $Revision: 1.5 $
 * 
 */

$(document).ready(function() {
	
	bindSortable();
	
	$("#delete_field").hide();

	/* developer -> datasets -> view */
	
	$("#fields_list li", "#developer-datasets-view").live('click', function(event){
		
		event.preventDefault();
		
		var $self = $(this);
		
		$("#DatasetField_id").val($self.data('field-id'));
		$("#DatasetField_module_component_id").val($self.data('field-module-component-id'));
		$("#DatasetField_name").val($self.data('field-name'));
		$("#DatasetField_title").val($self.data('field-title'));
		$("#datasetfield_description").text($self.data('field-description'));
		$("#DatasetField_type").val($self.data('field-type'));
		$("#DatasetField_length").val($self.data('field-length'));
		$("#DatasetField_default_value").val($self.data('field-default-value'));
		$("#DatasetField_mandatory").val($self.data('field-mandatory'));
		$("#DatasetField_searchable").val($self.data('field-searchable'));
		
		if ($self.data('field-mandatory') == 't') {
			$("#DatasetField_mandatory").prop('checked', true);
		}
		else {
			$("#DatasetField_mandatory").prop('checked', false);
		}
		
		if ($self.data('field-searchable') == 't') {
			$("#DatasetField_searchable").prop('checked', true);
		}
		else {
			$("#DatasetField_searchable").prop('checked', false);
		}
		
		if ($self.data('field-display-in-list') == 't') {
			$("#DatasetField_display_in_list").prop('checked', true);
		}
		else {
			$("#DatasetField_display_in_list").prop('checked', false);
		}
		
		$("#delete_field").show();

	});
	
	$("#new_field, #cancel_field", "#developer-datasets-view").live('click', function(event){
		
		event.preventDefault();
		
		$("#DatasetField_id").val('');
		$("#DatasetField_module_component_id").val('');
		$("#DatasetField_name").val('');
		$("#DatasetField_title").val('');
		$("#datasetfield_description").val('');
		$("#DatasetField_type option:first").attr('selected','selected');
		$("#DatasetField_length").val('');
		$("#DatasetField_default_value").val('');
		$("#DatasetField_mandatory").prop('checked', false);
		$("#DatasetField_searchable").prop('checked', false);
		$("#DatasetField_display_in_list").prop('checked', false);
		
		$("#DatasetField_title").focus();
		
		$("#delete_field").hide();

	});
	
	$("#delete_field", "#developer-datasets-view").live('click', function(event){
		
		event.preventDefault();
		
		if ((confirm("Are you sure you wish to delete this field?"))) {
			
			$('#included_file').uz_ajax({
				url: '/?module=developer&controller=datasets&action=delete_field&ajax=',
				data: { 
					id				: $('#DatasetField_id').val(),
					name			: $('#DatasetField_name').val(),
					dataset_id		: $('#DatasetField_dataset_id').val(),
					dataset_name	: $('#DatasetField_dataset_name').val()
				},
				type: 'POST'
			});
		
		}
		

	});
	
	$("#DatasetField_module_component_id", "#developer-datasets-view").live('change', function(event){
		
		$("#DatasetField_title").val($("#DatasetField_module_component_id option:selected").text());
		
		if ($("#DatasetField_module_component_id").val() == '') {
			$('#hide_on_link').show();
			$("#DatasetField_title").val('');
		}
		else {
			$('#hide_on_link').hide();
		}
		
		$("#DatasetField_title").focus();
		
	});
	
	
	$(".connectedSortable", '#developer-datasets-view').on( "sortstop", function(event, ui) {
		
		var $item		= $(ui.item);
		
		// update item's position if it has moved
		if ($item.data('field-position') != ($item.index() + 1))
		{
			$.uz_ajax({
				url: '/?module=developer&controller=datasets&action=update_position',
				data: {
					field_id			: $item.data('field-id'),
					new_position		: $item.index() + 1,
					current_position	: $item.data('field-position')
				},
				type: 'POST',
				success: function() {
					$( "#fields_list li" ).each(function(index) {
						// Make sure the each item's position is correct
						$(this).data('field-position', index + 1);
					});
				}
			});
			
			
		}
		
	});

});

function bindSortable() {
	$(".connectedSortable").sortable({
		connectWith: '.connectedSortable',
		placeholder: 'ui-state-highlight'
	}).disableSelection();
}

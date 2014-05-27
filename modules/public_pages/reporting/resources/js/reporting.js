 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * reporting.js
 * 
 * $Revision: 1.14 $
 * 
 */
	
var properties_changed	= false;
var properties_text		= "<p>Double click a field to see it's options</p>";

$(document).ready(function(){
	
	bindSortable();
	
	/* reporting -> reports -> new */
	
	$('#row_fields li', '#reporting-reports-new').live('dblclick', function() {
		
		// set a few vars
		var self			= $(this),
			field_label		= self.data('field-label'),
			field_type		= $('#field_type_' + field_label).val(),
			field_data_type	= self.data('field-type');
		
		// don't bother if we're already highlighted
		if(self.hasClass('highlight')) {
			return false;
		}	
		
		// clean up properties window
		resetProperties();

		// add highlight class
		self.addClass('highlight');
		
		// place options in properties window
		$('#properties')
			.data('field_data_type', 'row')
			.data('field_label', field_label)
			.html('<h3>Field: ' + self.html() + '</h3>' + $('#row_options').html());
		
		// NEED TO DISCOVER ALL THE AVAILABLE BREAK FIELDS
		
		var reached_current_field	= false,
			field_total_levels		= [];
		
		$('#row_fields li').each(function() {
			
			var $current_field		= $(this),
				current_field_label	= $current_field.data('field-label');
			
			// detect of we've hit the field we've selected yet
			if (!reached_current_field && current_field_label === self.data('field-label')) {
				reached_current_field = true;
			}
			
			// if we haven't continue to process the fields into the total levels array
			if (!reached_current_field) {

				if ($('#normal_break_on_' + current_field_label).val() === 'true') {
					
					$('#normal_total').find(':last-child').before('<option value="' + current_field_label + '">' + current_field_label + '</p>');
					
				}
				
			}
			
		});
		
		// get column methods
		$('#normal_method').uz_ajax({
			async	: false,	// make sure this ajax is finished before proceding
			cache	: true,		// we don't need to worry too much about cached results
			data	: {
				module		: 'reporting',
				controller	: 'reports',
				action		: 'getAggregateMethods',
				type		: field_data_type,
				ajax		: ''
			}
		});
		
		// get search operators
		$('#search_type').uz_ajax({
			async	: false,	// make sure this ajax is finished before proceding
			cache	: true,		// we don't need to worry too much about cached results
			data	: {
				module		: 'reporting',
				controller	: 'reports',
				action		: 'getSearchTypes',
				type		: field_data_type,
				ajax		: ''
			}
		});

		// show the field options pane
		$('.field_options').show();
		
		// restore the field options
		$('#normal_display_field').restoreOption('#normal_display_field_' + field_label).trigger('change');
		$('#normal_field_label').restoreOption('#normal_field_label_' + field_label);
		$('#normal_break_on').restoreOption('#normal_break_on_' + field_label);
		$('#normal_thousands_seperator').restoreOption('#normal_thousands_seperator_' + field_label);
		$('#normal_red_negative_numbers').restoreOption('#normal_red_negative_numbers_' + field_label);
		$('#normal_enable_formatting').restoreOption('#normal_enable_formatting_' + field_label).trigger('change');
		$('#normal_decimal_places').restoreOption('#normal_decimal_places_' + field_label);
		$('#normal_method').restoreOption('#normal_method_' + field_label).trigger('change');
		$('#normal_total').restoreOption('#normal_total_' + field_label);
		$('#normal_justify').restoreOption('#normal_justify_' + field_label);
		$('#normal_enable_search').restoreOption('#normal_enable_search_' + field_label).trigger('change');
		$('#search_type').restoreOption('#search_type_' + field_label);
		$('#search_default_value').restoreOption('#search_default_value_' + field_label);
		
		// we will have triggered a change at this point... so lets reset the changed status
		properties_changed = false;
		
		// disable the connected lists
		$(".connectedSortable").sortable("disable");
		
	});

	$('.filter','#reporting-reports-new').live('click', function(event) {

		event.preventDefault();
		
		// open the dialog
		$('#filter_dialog').dialog({
			autoOpen: true,
			height: 225,
			width: 670,
			resizable: false,
			modal: true,
			title: 'Filter',
			open: function() {
			
				$('.filter_field').uz_ajax({
					async: false,
					data: {
						module		: 'reporting',
						controller	: 'reports',
						action		: 'getFilterData',
						tablename	: $('#Report_tablename').val(),
						ajax:''
					},
					complete: function() {
						
						$('#filter_1_field').restoreOption('#filter_1_field_filter');
						$('#filter_1_condition').restoreOption('#filter_1_condition_filter');
						$('#filter_1_value').restoreOption('#filter_1_value_filter');
						
						$('#filter_2_operator').restoreOption('#filter_2_operator_filter');
						$('#filter_2_field').restoreOption('#filter_2_field_filter');
						$('#filter_2_condition').restoreOption('#filter_2_condition_filter');
						$('#filter_2_value').restoreOption('#filter_2_value_filter');
						
						$('#filter_3_operator').restoreOption('#filter_3_operator_filter');
						$('#filter_3_field').restoreOption('#filter_3_field_filter');
						$('#filter_3_condition').restoreOption('#filter_3_condition_filter');
						$('#filter_3_value').restoreOption('#filter_3_value_filter');
						
					}
				});				
				
			},
			buttons: {
				Ok: function() {

					$('.option_filter','#options').remove();

					saveOption('field_type','filter','filter');
					
					$('#filter_1_field').saveOption('filter_1_field','filter');
					$('#filter_1_condition').saveOption('filter_1_condition','filter');
					$('#filter_1_value').saveOption('filter_1_value','filter');
						
					$('#filter_2_operator').saveOption('filter_2_operator','filter');
					$('#filter_2_field').saveOption('filter_2_field','filter');
					$('#filter_2_condition').saveOption('filter_2_condition','filter');
					$('#filter_2_value').saveOption('filter_2_value','filter');
					
					$('#filter_3_operator').saveOption('filter_3_operator','filter');
					$('#filter_3_field').saveOption('filter_3_field','filter');
					$('#filter_3_condition').saveOption('filter_3_condition','filter');
					$('#filter_3_value').saveOption('filter_3_value','filter');
						
					$(this).dialog('close');
				},
				Cancel: function() {

					$(this).dialog('close');
				},
				Clear: function() {
					
					if(!(confirm("Are you sure you want to remove all filter lines?"))) {
						return false;
					}

					$('#filter_dialog').find('input, select').val('');
					
				}
			},
			close: function() {
			}
		});

	});

	$('.remove-filter','#reporting-reports-new').live('click', function(event) {
		
		event.preventDefault();
		
		if(!(confirm("Are you sure you want to remove this filter line?"))) {
			return false;
		}
		
		var self			= $(this),
			current_index	= self.parents('tr').index(),
			tbody			= self.parents('tbody'),
			last_index		= tbody.find('tr:last-of-type').index(),
			iterations		= last_index-current_index;
		
		var i;
		
		for (i = current_index; i <= last_index; i++) {
			
			// row index = 0,1,2 ... field references = 1,2,3
			
			var c = i + 1;
			var r = i + 2;
			
			// i = current index row (0)
			// c = current html row (1)
			// r = next row
			
			if( i !== last_index ) {
								
				if( i !== 0 ) {
					$('#filter_' + c + '_operator').val( $('#filter_' + r + '_operator').val() );
				}
				
				$('#filter_' + c + '_field').val( $('#filter_' + r + '_field').val() );
				$('#filter_' + c + '_condition').val( $('#filter_' + r + '_condition').val() );
				$('#filter_' + c + '_value').val( $('#filter_' + r + '_value').val() );

			} else {
				
				$('#filter_' + c + '_operator').val('');
				$('#filter_' + c + '_field').val('');
				$('#filter_' + c + '_condition').val('');
				$('#filter_' + c + '_value').val('');
				
			}
			
		}

	});
	
	/* properties behaviour */
	
	$('#properties input, #properties select, #properties textarea','#reporting-reports-new').live('change', function(event) {
		properties_changed=true;
	});
	
	$('input[type=radio][name=field_type]','#reporting-reports-new').live('change', function() {
		
		var self = $(this);
		
		// ATTN: TODO: must check to make sure we aren't losing any unsaved data
		
		// hide all option panes
		$('.options_pane').hide();
		
		switch(self.val()) {
			case "normal":
				$('.field_options').show();
				break;
			
			case "search":
				$('.search_options').show();
				break;
				
		}
		
	});
	
	$(".save","#reporting-reports-new").live('click', function(event) {
		// please excuse all of the looping... will have to figure out some monster function to make code more effecient
		event.preventDefault();
		
		// no point in saving if we're currently editing a field
		if($('#row_fields li.highlight').length) {
			alert("Cannot save whilst editing field options");
			return false;
		}
	
		// set a few vars
		var self				= $(this),
			sequence_counter	= 0,
			$row_fields			= $('#row_fields'),
			$options			= $('#options');
		
		// add temp class to field options
		$('input', $options).addClass('remove_field_option');
		
		// loop through each field... checking/setting options as we go
		$('li', $row_fields).each(function() {

			sequence_counter++;
			
			// set a few vars
			var field		= $(this),
				field_label	= field.data('field-label'),
				field_type	= $('#field_type_' + field_label).val();
			
			// remove the temp 'remove_field_option' class
			$('.option_' + field_label).removeClass('remove_field_option');
			
			saveOption('position', field_label, sequence_counter);
			
		});
		
		// we need to exclude the filter options from the delete
		$('.option_filter').removeClass('remove_field_option');
		
		// remove any field options that are no longer selected
		$('.remove_field_option').remove();
		
		// ajax the data, returns an error to prevent having to repaint the screen
		$.uz_ajax({
			url				: '/?module=reporting&controller=reports&action=save&ajax=&',
			type			: 'post',
			dataType		: "json",
			data			: $('#save_form').serialize(),
			success	: function(data) {
				if(data.status === false) {
					$('#flash').html('<ul id="errors">' + data.message + '</ul>');
				} else {
					window.location.href = data.redirect;
				}
				$('#main_without_sidebar').scrollTo( { top:0, left:0 }, 1000 );
			}
		});
		
	});
	
	$('.delete, .save-another').live('click', function(event) {
		$(this).attr('disabled','disabled');
	});
	
	// apply field properties
	$('button.apply', '#reporting-reports-new').live('click', function(event) {
		event.preventDefault();

		var $properties = $('#properties'),
			field_label = $properties.data('field_label');
		
		// ATTN: needs altering for filter?
		// remove all existing options for this field... unless it's a filter
		//if(field_type==='filter') {
		//	$('.option_filter','#options').remove();
		//	$('.option_'+field_label,'#options').remove();
		//} else {
			$('.option_'+field_label,'#options').remove();
		//}
		
		// save field properties
		$('#normal_field_label').saveOption('normal_field_label', field_label);
		$('#normal_display_field').saveOption('normal_display_field', field_label);
		$('#normal_break_on').saveOption('normal_break_on', field_label);
		$('#normal_method').saveOption('normal_method', field_label);
		$('#normal_total').saveOption('normal_total', field_label);
		$('#normal_enable_formatting').saveOption('normal_enable_formatting', field_label);
		$('#normal_decimal_places').saveOption('normal_decimal_places', field_label);
		$('#normal_red_negative_numbers').saveOption('normal_red_negative_numbers', field_label);
		$('#normal_thousands_seperator').saveOption('normal_thousands_seperator', field_label);
		$('#normal_justify').saveOption('normal_justify', field_label);
		$('#normal_enable_search').saveOption('normal_enable_search', field_label);
		$('#search_type').saveOption('search_type', field_label);
		$('#search_default_value').saveOption('search_default_value', field_label);

		// set the properties status to unchanged 
		properties_changed = false;
		
		// clear the properties pane
		resetProperties(true);
		
	});
	
	$('button.cancel', '#reporting-reports-new').live('click', function(event) {
		
		event.preventDefault();
		resetProperties();
		
	});
	
	$("#Report_tablename", "#reporting-reports-new").live('change', function() {
		
		var $self = $(this);
		
		$('#pivot_table').uz_ajax({
			data:{
				module		: 'reporting',
				controller	: 'reports',
				action		: 'pivot_table',
				id			: $('#Report_id').val(),
				tablename	: $self.val(),
				description	: $('#Report_description').val(),
				ajax		: ''
			},
			complete: function() {
				bindSortable();
				// clear out the options
				$('#options').html('');
				$(window).trigger('resize');
			}
		});
	});
	
	
	
	$('#properties #normal_display_field', "#reporting-reports-new").live('change', function(event) {

		var self	= $(this),
			checked	= self.is(':checked');

		$('.normal_display_field','#properties').each(function() {

			if(checked) {
				$(this).removeClass('disabled').find('input, select, textarea').removeAttr('disabled');
				$('#normal_enable_formatting').trigger('change');
			} else {
				$(this).addClass('disabled').find('input, select, textarea').attr('disabled','disabled');
			}
			
		});
	
		
	});
	
	$('#properties #normal_enable_formatting', "#reporting-reports-new").live('change', function(event) {

		var self	= $(this),
			checked	= self.is(':checked');

		$('.normal_formatting','#properties').each(function() {

			if(checked) {
				$(this).removeClass('disabled').find('input, select, textarea').removeAttr('disabled');
			} else {
				$(this).addClass('disabled').find('input, select, textarea').attr('disabled','disabled');
			}
			
		});
	
	});
	
	$('#properties #normal_enable_search', "#reporting-reports-new").live('change', function(event) {

		var self	= $(this),
			checked	= self.is(':checked');

		$('.normal_search','#properties').each(function() {

			if(checked) {
				$(this).removeClass('disabled').find('input, select, textarea').removeAttr('disabled');
			} else {
				$(this).addClass('disabled').find('input, select, textarea').attr('disabled','disabled');
			}
			
		});
	
	});
	
	$('#properties #normal_method', "#reporting-reports-new").live('change', function(event) {

		var self = $(this);

		if(self.val() === 'dont_total') {
			$('#normal_total').parents('tr').addClass('disabled');
		} else {
			$('#normal_total').parents('tr').removeClass('disabled');
		}
	
	});
	
	
	/* reporting -> reports -> run */
	
	$('#search_print', '#reporting-reports-run').die().live('click', function(event) {
		
		event.preventDefault();

		// get the query string as an object
		var $_GET = getQueryParams(window.location.href);
		
		// rebuild link
		$_GET.action		= 'printDialog';
		$_GET.printaction	= 'run';
		
		// remove the pid to stop access errors
		delete $_GET.pid;
		
		var link = '/?'+makeQueryString($_GET)+'&ajax=';
	
		// fire print dialog
		uz_print_dialog({
			url: link,
			data: {
				search_id: $('#search_id').val()
			}
		});
	
	});

});


 //****************
// FUNCTIONS

$.fn.restoreOption = function(field) {
	
	return this.each(function() {
		
		var self	= $(this),
			source	= $(field)
			value	= source.val();
		
		// we're not interested in undefined values
		if (value === undefined) {
			return;
		}
		
		// change value on element type
		if(self.is(':checkbox')) {
			if (value === 'true') {
				self.attr('checked','checked');
			} else if (value === 'false') {
				self.removeAttr('checked');
			}
		} else {
			self.val( value );
		}
		
	});

};

//jQuery function, interfaces saveOption for tidier element saving

$.fn.saveOption = function(option,field) {
	
	return this.each(function() {
		
		var self = $(this);
		
		// change value on element type
		if(self.is(':checkbox')) {
			value = self.is(':checked');
		} else {
			value = self.val();
		}
		
		saveOption(option,field,value);
		
	});
	
};

// JavaScript funtion for normal variables etc
function saveOption(option,field,value) {

	var $options	=	$('#options');
	var _field		=	'';
		
	if(field!=='') {
		_field='_'+field;
	}
	
	$options.append('<input type="hidden" class="field_option option'+_field+'" id="'+option+_field+'" name="Report[options]['+field+']['+option+']" value="'+value+'" data-field-name="'+field+'" />');
	
}

function resetProperties(force_reset) {
	
	// default param
	if (force_reset === null) { force_reset = false; }
	 
	if(properties_changed && force_reset!==true) {
		if(!(confirm("You have unsaved changes, are you sure you want to cancel?"))) {
			return false;
		}
	}
	
	// remove any existing highlight
	$('.highlight').removeClass('highlight');
	
	
	// revert the properties box back to the original text
	$('#properties').html(properties_text);
	
	// reset the properties_changed status
	properties_changed=false;
	
	$(".connectedSortable").sortable("enable");
	
	$(window).trigger('resize');
	
}

function bindSortable() {
	$(".connectedSortable").sortable({
		connectWith: '.connectedSortable',
		placeholder: 'ui-state-highlight'
	}).disableSelection();
}

/*
 * uzERP print dialog 
 * 
 * print_dialog.js
 *
 * $Revision: 1.20 $
 *
 * uz_print_dialog is a wrapper function that enabled to use of a print dialog box.
 * 
 *      (c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *      Released under GPLv3 license; see LICENSE.
 * 
 */

function uz_print_dialog(args) {
	
	// define default options
	var defaults = {
		url: '',
		data: {}
	};
	
	// merge the passed options with the defaults
	var options = $.extend({}, defaults, args);
	
	// we just want to make sure #print_dialog doesn't already exist
	$('#print_dialog').remove();

	// clone the original ajax_stage, change its ID so we never touch the "original" print dialog
	$("#original_print_dialog").clone().attr('id', "print_dialog").appendTo("body");
	
	// we're going to be each unique ajax staging element more than once, so lets apply it to a var
	var $print_dialog = $('#print_dialog');
	
	// construct the print dialog
	$print_dialog.dialog({
		title		: 'Output Document',
		height		: 320,
		width		: 450,
		resizable	: false,
		modal		: true,
		close		: function () {
			$(this).dialog("destroy");
		}
	});
	
	// display the wait screen
	$('.print_wait', $print_dialog).show();
	
	// parse the url
	var $_GET = getQueryParams(options.url);
	
	// check if an alternate print action has been set
	if ($('#alternate_print_action').length) {
		$_GET.printaction = $('#alternate_print_action').val();
	}
	
	// remove the pid to stop access errors
	delete $_GET.pid; 

	// rebuild the url
	options.url = '/?' + makeQueryString($_GET);
	
	// the data variable is a string, rename the submit token variable so it isn't used
	if (typeof options.data === 'string') {
		options.data = options.data.replace("submit_token=", "_IGNORE_submit_token=");
	}
	
	// hit the specified url
	// we're not using uz_ajax here, so aren't protected against logouts
	
	$.ajax({
		type		: 'POST',
		url			: options.url + '&ajax=',
		data		: options.data,
		dataType	: "html",
		success		: function (data) {
			// ATTN: *sigh* as we're dealing with html here we cannot return out loverly JSON errors, instead
			// if the first 7 characters are "FAILURE", anything after the 8th character is the errors
			if (data.substr(0, 7) === "FAILURE") {
				var error_text;
				if (data.substr(8) !== '') {
					error_text = data.substr(8);
				} else {
					error_text = "An error occured";
				}
				$('.print_wait', $print_dialog).hide();
				$('.print_failure', $print_dialog).show().children('.wait_message').html(error_text);
			} else {
				$('.print_wait', $print_dialog).hide();
				$print_dialog.append(data);
				$('#printtype, #printaction', $print_dialog).trigger('change');
				
				bind_print_dialog_buttons($print_dialog);
				
			}
		}
	});

}

function uz_print_dialog_generate($print_dialog, url, data) {
	
	
	// get form elements and variable
//	var form = $(this).parents('form');
//	var form_data = form.serialize() + "&ajax=''";
	
	// get column widths... html/css is great for calculating this stuff, FOP isn't
	var col_widths='';
	$('#datagrid1 thead tr th, .tablescroll thead tr th').each(function () {
		var self=$(this);
		if (self.data('column') !== undefined) {
//			alert('getting column width for '+self.data('column')+' '+self.width());
			col_widths=col_widths+self.data('column')+'='+self.width()+'|';
		}
	});
			
	// fire the ajax to output the document
	$.uz_ajax({
		type	: 'POST',
		url		: url+"&ajax=&",
		data	: data+"&ajax=''"+
			'&session_key='+$('input[name=index_key]').val() +
			'&search_id='+$('#search_id').val() +
			'&col_widths='+col_widths +
			'&ajax_print='+''
		,
		dataType: "json", // we MUST get a json array back
		success	: function (data) {
			uz_print_dialog_response($print_dialog, data);
		}
	});

}

function uz_print_dialog_response($print_dialog, data) {
	
	$('.print_wait', $print_dialog).hide();
	// sometimes the server might return some non-json data (if the PHP isn't 
	// coded correctly, like exiting the script), check if data is defined before
	// we continue
	if (data !== undefined && data !== null) {
		if (data.status==true) {
			if (data.redirect!=undefined && data.redirect!='') {
				$('.print_wait', $print_dialog).show();
				window.location.href=data.redirect;
			} else {
				if (data.refresh_page!=undefined && data.refresh_page) {
					refresh_current_page();
				}
				// if pdf preview is enabled, make the dialog bigger and re-position it
				if (data.pdf_preview===true && data.filetype==='pdf') {
					$print_dialog.dialog({
						height: 395,
						width: 700
					}).dialog( "option", "position", $print_dialog.dialog( "option", "position" ) );
				}
				
				// populate the success box with the data
				$('.print_success', $print_dialog).html(data.html).show();
				
				bind_print_dialog_buttons($print_dialog);
											
				$print_dialog.dialog( "option", "title", 'Document Generated' );
				
				// get pdf preview
				if (data.pdf_preview) {
					pdf_preview(data);
				}
				
			}
		} else {
			$('.print_failure', $print_dialog).show().children('.wait_message').html(data.message);
		}
	} else {
		$('.print_failure', $print_dialog).show().children('.wait_message').html('No response from server');
	}

}

function pdf_preview (data) {

	$.ajax({
		url: data.pdf_preview_build_link,
		type: 'POST',
		data: {
			filename: data.pdf_preview_location,
			location: data.location
		},
		success: function (data) {
			$('.pdf-preview').html(data);
		}
	});

}

$(document).ready(function () {
	
	// PRINT BUTTON IN DIALOG
	$('button.output, a.output','#print_dialog').live('click', function (event) {
		event.preventDefault();
		
		var $print_dialog = $('#print_dialog');
		
		// swap the dialog for the wait screen
		$('.print_wait', $print_dialog).show();
		$('.print_dialog', $print_dialog).hide();
		
		// check if the index_link input element exist
		if ($('input[name=index_link]').length>0) {
			$.ajax({
				async		: false,
				url			: Base64.decode($('input[name=index_link]').val())+'&search_id='+$('#search_id').val(),
				dataType	: "html",
				data		: {
					session_key	: $('input[name=index_key]').val(),
					ajax_print	: ''
				},
				success: function (data) {
					debug.info("Print: index xml / xsl saved to session");
				},
				type: "POST"
			});
		}
		
		var form = $(this).parents('form');
		var form_data = form.serialize();

		uz_print_dialog_generate($print_dialog, form.attr('action'), form_data);
		
	});
	
	$('a.print').live('click',function (event) {
		event.preventDefault();
		
		var iframe = document.getElementById('PDFtoPrint');
		
		iframe.focus();
		iframe.contentWindow.print();
		
	});

	$('a.close').live('click',function (event) {
		event.preventDefault();
		
		// Refresh the whole page to ensure any sidebar options are rest correctly
		location.reload();
		
	});
	
	$('.print_success .tick, .print_success .cross').live('click', function() {
		
		// Refresh the whole page to ensure any sidebar options are rest correctly
		location.reload();
		
	});
	
	$('#report_type', '#print_dialog').live('change',function (event) {
		
		var form = $(this).parents('form');
		
		var $_GET = getQueryParams(form.attr('action'));
		
		$('#report').uz_ajax({
			data:{
				module			: $_GET.module,
				controller		: $_GET.controller,
				action			: 'getReportsByType',
				report_type_id	: $(this).val(),
				ajax			: ''
			}
		});
		
	});

});

function bind_print_dialog_buttons(context) {

	$("button, .button", context || document).button();
	
	$('.button.options', context || document).button({
		icons: {
			primary: "ui-icon-gear"
		},
		text: false
	});

}
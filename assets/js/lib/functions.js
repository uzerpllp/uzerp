/* FUNCTIONS
 * 
 * functions.js
 *
 * $Revision: 1.63 $
 * 
 *      (c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *      Released under GPLv3 license; see LICENSE.
 *
 */     

/*
 * Ensure that the CSRF token header is added to all non-safe ajax requests
 */
function csrfSafeMethod(method) {
    // these HTTP methods do not require CSRF protection
    return (/^(GET|HEAD|OPTIONS|TRACE)$/.test(method));
}

$.ajaxPrefilter(function (options, originalOptions, xhr) {
        if (!csrfSafeMethod(options.type) && !this.crossDomain) {
            xhr.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr("content"));
        }
});

/* 
 * ATTENTION: we're using legacyForceChange() so we can be lazy and not convert 
 * all the controllers until we have to, a grep on legacyForceChange will 
 * highlight all areas that require converting after the jquery rule has been 
 * implemented, a log warning will also occur whenever this function is used
 */

function legacyForceChange(element) {
	debug.warn("legacyForceChange() --> Forcing change on element "+element);
	$(element).trigger("change");
}

/*
 * Checks / unchecks all checkboxes based on given target
 */
function checkAll(element,target) {
	
	debug.warn("This is a legacy function and should be deprecated ASAP, if this is used, explore removing it for $.fn.checkAll");
	
	if ($(element).data("status") === undefined || $(element).data("status") == false) {
		$(target).attr('checked',true).trigger("change");
		$(element).data("status",true);
	} else {
		$(target).removeAttr('checked').trigger("change");
		$(element).data("status",false);
	}
	
}

// lets make a jquery function for this, tidier code
$.fn.checkAll = function(target) {
	
	var $this = $(target);
	
	if ($this.data("status") === undefined || $this.data("status") === null || $this.data("status") == false) {
		
		$(target).each(function() {
			
			if (!$(this).is(':checked')) {
				$(this).attr('checked', true).trigger("change");
			}
			
		});
		
		$this.data("status", true);
		
	} else {
		
		$(target).each(function() {
			
			if($(this).is(':checked')) {
				$(this).removeAttr('checked').trigger("change");
			}
			
		});
		
		$this.data("status",false);
		
	}
	
};

function calcValue (target, quantity, price) {
	
	var quantity	= isNaN(parseFloat(quantity))?0:parseFloat(quantity),
		price		= isNaN(parseFloat(price))?0:parseFloat(price),
		value		= quantity * price;
	
	value = value.toFixed(2);
	
	$(target).val(value).trigger("change");
	
}

function calcTotal (elements, parent, target) {
	
	var total = 0;
	
	$(parent).find(elements).each(function(id) {
		total += isNaN(parseFloat($(this).val()))?0:parseFloat($(this).val());
		total  = parseFloat(total.toFixed(2));
	});
	
	$(target).val(total.toFixed(2));
	
}

function check_if_table_needs_scroll_bar() {
	// make datagrid table scrollable if it exceeds page size
	if ($('#datagrid1').attr('id')!==undefined && $('#footer').attr('id')!==undefined && $('#footer').offset().top>$('#datagrid1').offset().top && $('#footer').offset().top<($('#datagrid1').offset().top+$('#datagrid1').height())) {
		$('#included_file').parent().scrollTop(0);
		$('#included_file').parent().scrollLeft(0);
		
		if ($('#included_file .tablescroll_wrapper').height()!==null) {
			$('#datagrid1').tableScroll({height:$('#datagrid1 tbody').height()});
		}
		
		var	 datagrid_base = $('#datagrid1 tbody').position().top+$('#datagrid1 tbody').height()
			,included_base = $('#included_file').position().top+$('#included_file').height()
			,margin_base = included_base-datagrid_base+10;
		
		if (($('#datagrid1 tbody').position().top+margin_base+100) < $('#footer').offset().top) {
			// The viewable size of the datagrid1 body region is more than 100 pixels
			// so hide the vertical scrollbar of the included parent div
			// height of datagrid scrollbar is difference between datagrid top position and footer top position
			// less any bottom margin for the included_file div
			$('#datagrid1').tableScroll({height:($('#footer').position().top-$('#datagrid1 tbody').position().top-margin_base)});
			$('#included_file').parent().css({ 'overflow-y': 'hidden' });
		}
		else
		{
			// The viewable size of the datagrid1 body region is less than 100 pixels
			// so do not hide the vertical scrollbar of the included parent div
			// and make the table scrollbar area the size of the included_file div less the table heading height
			var heading_height = $('#datagrid1 tbody').position().top-$('#datagrid1').position().top;
			$('#datagrid1').tableScroll({height:($('#footer').position().top-$('#included_file').position().top)-heading_height});
			$('#included_file').parent().css({ 'overflow-y': 'auto' });
		}
	}
	
}

function refresh_current_page (url) {
	
	if (url=="" || url==undefined) {
		url = window.location.href;
	}
	
	$('#included_file').uz_ajax({
		async		: false, // make sure this ajax is finished before proceding
		url			: url,
		lock_screen	: false,
		highlight	: false
	});
	
}

function dialogRefreshPage (options, action) {

	calledby	= $('#'+options.calledby);

	form_data	= calledby.find('#save_form').serialize();
	
	$_GET		= calledby.data();
	
	// If refresh is true, the underlying form needs to be refreshed to repopulate
	// the drop down lists by calling the controller's refresh function
	// otherwise refresh the underlying the page by calling with the original action
	if (options.refresh) {
		$_GET.action = 'refresh';
		delete $_GET.pid;
	}
	
	if ($('#'+options.calledby).parent().attr('id')=='included_file' && form_data=='' && action!='saveadd') {
		// Top level view - refresh page to refresh any sidebar actions
		window.location.href=window.location.href;
	
	} else {
	
		$.ajax({
			async		: false, // make sure this ajax is finished before proceding
			type:		'POST',
			url:		'/?'+makeQueryString($_GET),
			data:		form_data,
			dataType:	"html",
			success:	function(data) {
							if (data !== undefined && data !== null) {
								$('#'+calledby.parent().attr('id')).html(data);
							}
						},
			complete:	function() {
							options.callback();
						}
		});
	
		rebind_plugins('#'+options.calledby);
		
		$('#flash #messages')
		.delay(3000)
		.hide("blind", {}, 800);
	
	}
	
}

function dialogButton (options) {
	// rebuild the url
	var $_GET		= getQueryParams(options.url),
		form_dialog	= $('#'+options.id+'form_dialog'),
		form_data	= form_dialog.find('#save_form').serialize();
	
	$_GET.action	= options.action;
	options.url		= makeQueryString($_GET);
	
	if (options.refresh) {
		// If refresh is required, add this parameter to the url;
		// this will override any sendTo action in the controller code
		// so that the dialog is closed and returns to the calling form
		options.url += 'refresh=';
	}
	
	// disable the dialog buttons to prevent double-click
	$('.ui-dialog.ui-dialog-buttons .ui-button').attr("disabled", true);
	
	$('.ui-dialog #flash').html('<div class="ui-dialog-uzloading">Processing...</div>');
	
	$.ajax({
		type:'POST',
		url: '/?' + options.url + '&ajax=&dialog=',
		data: form_data,
		dataType: "html",
		success: function(data) {
			if (data !== undefined && data !== null) {
				
				if (data.substr(0, 1) == '{') {
					var jsondata = JSON.parse(data);
					
					if (jsondata.status==true) {
						
						if(jsondata.redirect!=undefined && jsondata.redirect!='') {
//							window.location.href=jsondata.redirect;
							// This is a pop-up dialog; ignore the redirect
							// and resdisplay the calling page
							window.location.href=window.location.href;
						}
						else
						{
							form_dialog.dialog('close');
						}
						
					}
					
				} else {
					// if data is empty then close the dialog, otherwise
					// probably had an error so just need to refresh the current dialog
					// with the returned html data
					if (data=="") {
						form_dialog.dialog('close');
					}
					else {
						form_dialog.html(data);
						$('.ui-dialog.ui-dialog-buttons .ui-button').attr("disabled", false);
					}
				}
				
			}
			
			if (options.saveadd) {
				
				var buttons = dialogButtonSetup(
					options,
					{
						saveaddButton: true,
						saveButton: true,
						cancelButton: true,
					}
				);
				
				form_dialog.dialog("option", "buttons", buttons);
				
				dialogRefreshPage (options, 'saveadd');
				
			}
			
			rebind_plugins(form_dialog);
			
		}
	
	});

	return options;
	
}

function dialogButtonSetup (options, buttonArgs) {

	var buttons	= {},
		$_GET = getQueryParams($('#'+options.id+'form_dialog').find('#save_form').attr('action'));
	
	options.action	= $_GET.action;
	
	delete $_GET.action; 
	delete $_GET.saveAnother; 
	
	options.url = makeQueryString($_GET);
	
	if (buttonArgs.deleteButton) {
		$.extend(buttons, {
			'Delete': function() {
				options.action = 'delete';
				dialogButton(options);
			}
		})
	}
	
	if (buttonArgs.saveaddButton) {
		$.extend(buttons, {
			'Save and Add Another': function() {
				options.url += '&saveAnother=';
				options.saveadd = true;
				dialogButton(options);
			}
		})
	}
	
	if (buttonArgs.saveButton) {
		$.extend(buttons, {
			Save: function() {
				dialogButton(options);
			}
		})
	}
	
	if (buttonArgs.cancelButton) {
		$.extend(buttons, {
			Cancel: function() {
				$('#'+options.id+'form_dialog').html('');
				$(this).dialog('close');
			}
		})
	}
	
	if (buttonArgs.closeButton) {
		$.extend(buttons, {
			Close: function() {
				$('#'+options.id+'form_dialog').html('');
				$(this).dialog('close');
			}
		})
	}
	
	return buttons;
	
}

function formDialog(optionArgs) {
	
	var defaults = {
		title		: '',
		height		: 550,
		width		: 550,
		resizable	: true,
		type		: 'add',
		refresh		: false,
		callback	: function() {}
	};
	
	// merge the passed options with the defaults
	var options = $.extend({}, defaults, optionArgs);

	$('#additional_components').append('<div id="'+options.id+'form_dialog"></div>');
	var $form_dialog = $('#'+options.id+'form_dialog');
	
	$form_dialog.uz_ajax({
		url			: options.url + '&ajax=&dialog=',
		highlight	: false,
		data		: options.data,
		type		: "POST",
		success		: function(response, base) {
			if (response !== undefined && response !== null) {
			
				// Check the response; the called form may have reported an error
				// or other response that requires a redirect
				if (response.status!== undefined && response.status==true) {
				
					if(response.redirect!=undefined && response.redirect!='') {
						window.location.href=response.redirect;
					}
				}
				else
				{
					// Output the response to the dialog box
					base.processResponse(response);
					$form_dialog.dialog('open');
				}
			}
		},
		complete	: function() {
			options.callback();
		}
	});

	$form_dialog.dialog({
		title		: options.title,
		height		: options.height,
		width		: options.width,
		resizable	: options.resizable,
		modal		: true,
		autoOpen	: false,
		open		: function() {
			if (options.type == 'edit') {
				
				var buttons = dialogButtonSetup(
					options,
					{
						deleteButton	: true,
						saveaddButton	: true,
						saveButton		: true,
						cancelButton	: true,
					}
				);
				
			} else {
				var buttons = dialogButtonSetup(
					options,
					{
						saveaddButton	: true,
						saveButton		: true,
						cancelButton	: true,
					}
				);
			}
			
			$(this).dialog("option", "buttons", buttons);
			
		},
		close: function() {
			$(this).dialog('destroy').remove();
			dialogRefreshPage (options, 'close');
			$('.page_title').trigger('change');
		}

	});
	
}

function roundNumber(num, dec) {
	var result = Math.round( Math.round( num * Math.pow( 10, dec + 1 ) ) / Math.pow( 10, 1 ) ) / Math.pow(10,dec);
	return result.toFixed(dec);
}

function addValues(element1, element2, target) {
	// ATTENTION: this is too easy *smug mode* love method chaining
   	$(target).val(roundNumber(parseFloat($(element1).val())+parseFloat($(element2).val()), 2)).trigger("change");
}

$.fn.appendAttr = function(attrName, suffix) {
	this.attr(attrName, function(i, val) { return val + suffix; });
};

$.fn.replaceAttr = function(attrName, find, replace) {
	
	var $self = $(this);
	
	if ($self.attr(attrName) !== undefined)
	{	
		this.attr(attrName, function(i, val) { 
			return val.replace(find, replace);
		});
	}
	
};

/* prettify */
$.fn.prettify = function() {
	var string = $(this).val();
	return string.replace('_', ' '); 
};

/* toUpperCase */
/*$.fn.toProperCase = function(attrName, find, replace) {
	this.attr(attrName, function(i, val) { 
		return val.replace(find, replace); 
	});
	
	toProperCase: function(){
		return this.toLowerCase().replace(/\w+/g,function(s){
			return s.charAt(0).toUpperCase() + s.substr(1);
		})
	}
};*/

$.fn.setInvoiceTotal = function(value, target) {
	
	if ($(this).is(':checked')) {
		var total = roundNumber(parseFloat($(target).val())+parseFloat($(value).val()), 2);
		$(target).val(total);
	} else {
		var total = roundNumber(parseFloat($(target).val())-parseFloat($(value).val()), 2);
		$(target).val(total);
	}
	
};

String.prototype.startsWith = function(str) {
	return (this.toLowerCase().match("^"+str.toLowerCase())==str.toLowerCase());
}

/*
 * After an AJAX request any dynamically added content will need
 * plugins rebound to it. Passing through context allows binding
 * to be limited to a single element and it's children.
 */
function rebind_plugins(context) {
	
	$(".datefield", context || document).datepicker({
		showAnim	: 'fadeIn',
		dateFormat	: 'dd/mm/yy',
		changeMonth	: true,
		changeYear	: true
	});
	
	$("select.multiselect", context || document).multiSelect();
	
	$('ul.collapsible_tree', context || document).collapsibleCheckboxTree({
		checkParents	: true, // When checking a box, all parents are checked (Default: true)
		checkChildren	: true, // When checking a box, all children are checked (Default: false)
		uncheckChildren	: true, // When unchecking a box, all children are unchecked (Default: true)
		initialState	: 'collapse' // Options - 'expand' (fully expanded), 'collapse' (fully collapsed) or default
	});
	
	$("textarea.code_editor", context || document).tabby();

	$(".uz-autocomplete", context || document).uz_autocomplete();

	$( ".uz-constrains", context || document ).uz_constrains();

	$('.uz-connected-lists', context || document).sortable({
		connectWith: ".uz-connected-lists"
	}).disableSelection();
	
	$('.pagination', context || document).each(function() {
		
		var $self = $(this);
		
		$self.jqPagination({
			paged: function(page) {
				update_page($self.find('input').data('url') + '&page=' + page + '&ajax=', $self.parents('form').serialize());
			}
		});
		
	});
	
}

function update_page(url, data) {

	var $target = $('#included_file');
	
	$target.uz_ajax({
		url:  url,
		type: "POST",
		data: data,
		complete: function () {
			$('#ajax_title').remove();
			if ($target.find('h3').length) {
				$('.page_title').after('<h1 id="ajax_title"> - ' + $target.find('h3').html() + "</h1>");
				$target.find('h3:first').remove();
			}
			$('.page_title').trigger("change");
			check_if_table_needs_scroll_bar();
		},
		scrollTo: function() {
			$('#main_with_sidebar, #main_without_sidebar').scrollTo( { top:0, left:0 }, 1000 );
		}
	});
	
}

function getQueryParams(qs) {
	
	var queryString = {};
	qs.replace(
		new RegExp("([^?=&]+)(=([^&]*))?", "g"),
		function($0, $1, $2, $3) { queryString[$1] = $3; }
	);
	
	
	
	return queryString;
}

function makeQueryString(qs) {
	var queryString = '';
	for(var key in qs) {
		// this is to make sure we don't include the domain... needs a beter solution
		if(key.substr(0,7)!='http://' && key.substr(0,1)!='/') {
			queryString+=key+'='+qs[key]+'&';
		}
		
	}
	return queryString;
}

// toggle_print_elements
// ATTENTION: JQI: I think this needs to be revisited, a toggleClass would probably be sufficient here
function toggle_print_elements (source, target) {
	debug.info("Trying to toggle print something :S");

	if(!$.isArray(target)) {
		target = new Array(target);
	}
	
	for ( var i in target ) {
		//debug.info($(source).val()+" "+target[i]);
		debug.debug($('#'+target[i]).hide());
		$('#'+target[i]).hide();
		if ($(source).val()==target[i]) {
			$('#'+target[i]).show();
		}
	}
}

/* date format functions... because they don't f***ing exist in JavaScript */

function formatDay(day) {
	if(day < 10) {
		day = "0" + day;
	}
	return day;
}
function formatMonth(month) {
	month++;
	if(month < 10) {
		month = "0" + month;
	}
	return month;
}
function formatHour(hour) {
	if(hour < 10) {
		hour = "0" + hour;
	}
	return hour;
}
function formatMinute(minute) {
	if(minute < 10) {
		minute = "0" + minute;
	}
	return minute;

}

/* this function is a straight bodge from the old scripts.js, there is
 * functionality that depends on it.
 */
function selectDisabled (disable, target) {
	debug.warn("MISSING FUNCTIONALITY, FUNCTION 'selectDisabled'");
};

/*
 * Progress Bar 
 */
function get_progress (singleton, main_job_finished, main_job_success) {
	
	if (singleton) {
		$.ajax({
			async		: false,
			url			: options.progress_url,
			dataType	: "json",
			success: function(data) {
				if (data=='null') {
					progress = 0;
				}
				else {
					progress = data;
				}
				
				if (progress < 0) {
					main_job_success = false;
				}
				
				if (main_job_finished) {
					if (progress < 100 || !main_job_success) {
						main_job_success = false;
						// Add fail icon to progressbar
						$('#dialogprogressbar').addClass('fail');
					}
					else {
						// Add success icon to progressbar
						$('#dialogprogressbar').addClass('success');
					}
				}
				
				$('#dialogprogressbar').progressbar({value: progress} );
			}
		});
	}
	else {
		$.each(options.progress_bars, function(index, options) {
			$.ajax({
				async		: false,
				url			: options.progress_url,
				dataType	: "json",
				success: function(data) {
					if (data=='null') {
						progress = 0;
					}
					else {
						progress = data;
					}
					
					if (progress < 0) {
						main_job_success = false;
					}
					
					if (main_job_finished) {
						if (progress < 100 || !main_job_success) {
							main_job_success = false;
							// Add fail icon to progressbar
							$('#dialogprogressbar'+index).addClass('fail');
						}
						else
						{
							// Add success icon to progressbar
							$('#dialogprogressbar'+index).addClass('success');
						}
					}
					
					$('#dialogprogressbar'+index).progressbar({value: progress} );
				}
			});
		});
	}
	
	return main_job_success;
}

function uz_progressbar (options) {
	
	var singleton = (options.progress_bars==undefined);
	
	if (options.heading==undefined) {
		options.heading = '';
	}
	
	if (options.title==undefined) {
		options.title = '';
	}
	
	$('#additional_components').append('<div id="dialog"></div>');

	if (singleton) {
		$('#dialog').append('<span id="dialogtitle">'+options.title+'</span>');
		$('#dialog').append('<div id="dialogprogressbar"></div>');
		$('#dialogprogressbar').progressbar({value: 0} );
	}
	else {
		$.each(options.progress_bars, function(index, options) {
			$('#dialog').append('<span id="dialogtitle'+index+'">'+options.title+'</span>');
			$('#dialog').append('<div id="dialogprogressbar'+index+'"></div>');
			$('#dialogprogressbar'+index).progressbar({value: 0} );
		});
	}
	
	$('#dialog').dialog({title: options.heading});

	var main_job_finished	= false,
	    main_job_success	= false;
	
	if (options.type==undefined) {
		options.type = 'GET';
	}
	
	if (options.data==undefined) {
		options.data = '';
	}
	
	var refreshId = setInterval(function() {
		main_job_success = get_progress (singleton, main_job_finished, main_job_success);
	}, 2500);
	
	$("#included_file").uz_ajax({
		async		: true,
		url			: options.main_url,
		data		: options.data,
		type		: options.type,
		complete	: function() {
						
						clearInterval(refreshId);
						
						main_job_finished = true;
						
						main_job_success = get_progress (singleton, main_job_finished, main_job_success);

						// Display Success/Fail message and icon
						if (main_job_success) {
							$('#dialog').append('<div id="dialogfinish" class="center">'+options.success_message+'</div>');
							$('#dialogfinish').append('<img id="dialogsuccess" style="display:block; margin-left: auto; margin-right: auto" src="/assets/graphics/small_tick.png"/>');
						} else {
							$('#dialog').append('<div id="dialogfinish" class="center">'+options.fail_message+'</div>');
							$('#dialogfinish').append('<img id="dialogfail" style="display:block; margin-left: auto; margin-right: auto" src="/assets/graphics/small_error.png"/>');
						}
						
						// Add a close button which, when clicked, will close and remove the dialog
						$('#dialog').dialog("option", "buttons", {
								Close: function() {
									$(this).dialog('close');
									$(this).remove();
								}
						});
						
						check_if_table_needs_scroll_bar();
						
		},
		success		: function(response, base) {
						main_job_success = true;
						base.processResponse(response);
		}
	});

};

/**
*
*  Javascript trim, ltrim, rtrim
*  http://www.webtoolkit.info/
*
**/
 
function trim(str, chars) {
	return ltrim(rtrim(str, chars), chars);
}
 
function ltrim(str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}
 
function rtrim(str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

function randomString() {
	var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var string_length = 8;
	var randomstring = '';
	for (var i=0; i<string_length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		randomstring += chars.substring(rnum,rnum+1);
	}
	return randomstring;
}

/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/
 
var Base64 = {
 
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
 
	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = Base64._utf8_encode(input);
 
		while (i < input.length) {
 
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
 
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
 
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
 
			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
 
		}
 
		return output;
	},
 
	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
 
		while (i < input.length) {
 
			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));
 
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
 
			output = output + String.fromCharCode(chr1);
 
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
 
		}
 
		output = Base64._utf8_decode(output);
 
		return output;
 
	},
 
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	},
 
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
 
		while ( i < utftext.length ) {
 
			c = utftext.charCodeAt(i);
 
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
 
		}
 
		return string;
	}
 
}

// oc = object converter
// http://snook.ca/archives/javascript/testing_for_a_v
function oc(a) {
	var o = {};
	for(var i=0;i<a.length;i++)  {
		o[a[i]]='';
	}
	return o;
}

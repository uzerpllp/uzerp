/*
 * Global Rules
 * 
 * rules.js
 *
 * $Revision: 1.77 $
 * 
 *      (c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *      Released under GPLv3 license; see LICENSE.
 *
 */

// force a window resize on load
$(window).load(function () {

	$(this).trigger('resize');
	
});

$(document).ready(function () {
	
	 //***********
	//  VARIABLES
	
	// set element vars
	$window = $(window);
	
	// set global vars
	data_changed = false;
	
	
	 //****************************
	//  PAGE / WINDOW / GENERIC UI
	
	// invoke window resize
	$window.trigger('resize');
	
	// this shouldn't be a plugin as such, due to ajax knobness
	$(window).resize(function () {

		resize_window();
		
		resize_resizable();
		
	});
	
	// set a default title
	document.title = "uzERP";
	
	// set the title to the main .page_title element
	$('.page_title').live('change', function () {
		
		var $self		= $(this),
			$ajax_title	= $('#ajax_title'),
			delimiter	= ' - ',
			title		= 'uzERP';
		
		if ($self.length > 0) {
			
			if ($ajax_title.length) {
				document.title = trim($self.html()) + trim($ajax_title.html()) + delimiter + title;
			} else {
				document.title = trim($self.html()) + delimiter + title;
			}
			
		} else {
			document.title = title;
		}
		
	})
	.trigger("change");
		
	// bind common elements
	rebind_plugins();
	
	// apply superfish to navigation
	$("ul.nav").superfish({
		delay		: 800,
		animation	: {opacity: 'show', height: 'show'},
		speed		: 'fast',
		dropShadows	: false
	});
	
	// flash message
	$('#flash #messages')
		.delay(3000)
		.hide("blind", {}, 800);
	
	$('#errors_button, #warnings_button, #messages_button').live('click', function (event) {
		
		var link = $('#'+$(this).data('id'));
		
		if (link.is(':visible')) {
			link.addClass('closed');
		} else {
			link.addClass('open');
		}

		link.slideToggle( 500 );
		
	});
	
	 //************
	//  VALIDATION
	
	// form validation
	$('form').uz_validation();
	
	
	 //*****************
	//  DATA PROTECTION

	// protect unsaved data before window unload
	$window.unbind("beforeunload").bind("beforeunload", function (event) {
			
		// if the event is a save button click, ignore it
		if (event.originalTarget !== undefined && !$(event.originalTarget.activeElement).hasClass('formsubmit')) {
			
			if (data_changed === true) {
				return "You have unsaved changed, do you wish to continue?";
			}
			
		}
	});

	// confirm action on delete links
	$('li.delete a').live('click', function (event) {
		
		if (!(confirm("Are you sure you wish to delete this record?"))) {
			event.preventDefault();
		}
		
	});
    
	// Show a dialog to confirm an action (e.g. sidebar delete links)
	//
	// Uses a POST request to call the action.
	//
	$(document).on('click', 'a.confirm', function(event){
		event.preventDefault();
		var message = 'Are you sure?|';

		if ($( this ).data('uz-confirm-message') !== undefined) {
			message = $( this ).data('uz-confirm-message');
		}
		message = message.split('|');
		
		var targetUrl = $(this).attr("href");
		var actionID = $(this).data('uz-action-id');
		
		$( '<div id="#confirm-dialog" title="Confirm Action"><p><strong>' + message[0] + '</strong></p>\
		<p><em>' + (message[1] !== undefined ? message[1] : '') + '</em></p></div>'
			).dialog({
				resizable: false,
				modal: true,
				buttons: {
                    "Yes": function() {
                        if ( typeof actionID != 'undefined' || actionID != null) {
                            $.uz_ajax({
                                async       : false,
                                type        : 'POST',
                                url         : targetUrl,
                                data: {
                                    id      : actionID,
                                    dialog  : true,
                                    ajax    : true
                                },
                                success: function(data) {
                                    if (typeof data.redirect != 'undefined' || data.redirect != null) {
                                        window.location.href = data.redirect;
                                    } else {
                                        //uzERP returned empty or unexpected response
                                        $('#flash').append("<ul id='errors'><li>Action failed</li></ul>");
                                    }
                                },
                            });
                            $( this ).dialog( "close" );
                        } else {
                            //Using GET requests for actions is deprecated, use POST!
                            window.location.href = targetUrl;
                        }
					},
					Cancel: function() {
                        $( this ).dialog( "close" );
					}
				}
			});
	});
	
	// Use a POST request on links that perform actions (e.g. sidebar delete links)
	//
	// This is good practice for security. Any UI interaction that will change data
	// must use a POST request.
	//
	$(document).on('click', 'a.protected', function(event){
		event.preventDefault();
				
		var targetUrl = $(this).attr("href");
		var actionID = $(this).data('uz-action-id');

		if ( typeof actionID != 'undefined' || actionID != null) {
			$.uz_ajax({
				async       : false,
				type        : 'POST',
				url         : targetUrl,
				data: {
					id      : actionID,
					dialog  : true,
					ajax    : true
				},
				success: function(data) {
					if (typeof data.redirect != 'undefined' || data.redirect != null) {
						window.location.href = data.redirect;
					} else {
						//uzERP returned empty or unexpected response
						$('#flash').append("<ul id='errors'><li>Action failed</li></ul>");
					}
				},
			});
			$( this ).dialog( "close" );
		} else {
			//Using GET requests for actions is deprecated, use POST!
			window.location.href = targetUrl;
		}
	});
	
	 //*****************************
	//  GENERIC / NON-SPECIFIC AJAX

	/* related items, order by, sorting, paging... */
	
	$('#sidebar_open_close').live('click', function (event) {
		
		$("#sidebar").toggle('slide', {}, 500 );
		
		var img = $(this).find('img');
		var img_src=img.attr('src');
		if (img_src.indexOf('expand') != -1) {
			img_src=img_src.replace('expand', 'contract');
		} else {
			img_src=img_src.replace('contract', 'expand');
		}
		img.attr('src', img_src);
		
	});
	
	// need a description as to what this does... as it can prevent binding of new events
	$('#sidebar_related_items a').live('click', function (event) {
		
		if (!$(this).hasClass('new_link')) {
			event.preventDefault();
			$('#included_file').addClass('ajax_related_item');
		}
		
	});

	//$('#sidebar_related_items a, .paging a, thead a, a.related_link').live('click', function (event) {
	//	if (!$(this).hasClass('newtab')) {
	$('#sidebar_related_items a, .paging a, thead a,').live('click', function (event) {
		
		if (!$(this).hasClass('newtab') && !$(this).hasClass('new_link') && !$(this).hasClass('dont-sort')) {
			
			event.preventDefault(); // lets prevent the links original action, should this be inside the condition?
			
			if (!$(this).hasClass('hidden')) {
				
				var $self = $(this);
				update_page($self.attr('href') + '&ajax=', $self.parents('form').serialize());
				// Add a location hash when the related items are loaded.
				document.location.hash = "show_related";
			}
			
		}
		
	});

	// Listen for location hash changes
	window.addEventListener('hashchange', function (event) {
		var hasRelatedSidebar = document.getElementById("sidebar_related_items");

		// Support the back button when viewing related items that are displayed using ajax.
		// I.e., remove the hash and reload the page to display the main view.
		// If we don't do this then the back button will go back too far, from the users viewpoint.
		// Obviously, using the forward button won't return to the ajaxed related items. To achieve
		// that, we would need to adjust uzERP to respond to a url containing a 'something_related'
		// hash location, generating the page to be viewed.
		if (location.hash == '' && hasRelatedSidebar) {
			window.location.replace(window.location.href.split('#')[0]);
		}
	}, false);
	
	$('#data_grid_search .uz_breadcrumbs a').live('click', function (event) {
		
		if (!$(this).hasClass('newtab') && !$(this).hasClass('new_link') && !$(this).hasClass('dont-sort')) {
			
			event.preventDefault(); // lets prevent the links original action, should this be inside the condition?
			
			if (!$(this).hasClass('hidden')) {
				
				var $self = $(this);
				
				update_page($self.attr('href') + '&ajax=');
				
			}
			
		}
		
	});
	
	$(document).on('click', 'a[href*="printDialog"], a[href*="printdialog"]', function(event){
		
		event.preventDefault();
		
		var $this	= $(this);
		
		if ($this.parent().hasClass('output_detail_related')) {
			
			// Get the fields on screen - determines print order
			var fields	= {}
				, field	= ''
				,$_GET	= getQueryParams($this.attr('href'));
			
			$("dd[id^='"+$_GET.data_object+"_']").each(function() {
				var temp = this.id.substr($_GET.data_object.length+1);
				fields[temp] = document.getElementById(this.id).previousSibling.innerHTML;
			});
					
			uz_print_dialog({url: $(this).attr('href'), data: {fields: fields} });
		}
		else {
			uz_print_dialog({url: $(this).attr('href')});
		}
		
	});
	
	$('.paging input').live('change', function (event) {
		
		event.preventDefault();
		
		var $self			= $(this),
			$included_file	= $('#included_file'),
			$page_title		= $('.page_title');
		
		$included_file.uz_ajax({
			url: $('#paging_url').val(),
			data: {
				search_id	: $('#search_id').val(),
				page		: $self.val(),
				ajax		: ''
			},
			complete: function () {
				
				$('#ajax_title').remove();
				
				if ($included_file.find('h3').length) {
					$page_title.after("<h1 id='ajax_title'> - " + $('#included_file').find('h3').html() + "</h1>");
					$included_file.find('h3:first').remove();
				}
				
				$page_title.trigger("change");
				
			},
			highlight: false
		});
		
	});

	// cancel button on ajaxed page
	$('.ajax_cancel').live('click', function (event) {
		
		event.preventDefault();
		
		$('#included_file').uz_ajax({
			url			: window.location.href,
			highlight	: false,
			complete	: function() {
				check_if_table_needs_scroll_bar();	
			}
		});
		
	});
	
	//**********************
	//  popup dialog for fk

	
	$('.dialog').live('click', function (event) {
		
		event.preventDefault();
		
		var $self = $(this);
		var element = $self.attr('parentid');
		
		var title='Add Reference Data';

		// This is instantiated when clicking the symbol next to a drop down list
		// on a form to add new items to the list;
		// then need to refresh the underlying form (refresh : true) to repopulate
		// the drop down list by calling the controller's refresh function
		calledby	= $self.parents('.content_wrapper').attr('id');
		
		var form_data = {};

		$('#'+calledby+" [id]").each(function() { 
			var field = $(this).data('field');
			if (field !== undefined && $(this).val()!='') {
				form_data[field] = $(this).val();
			}
		});
		
		formDialog({
			title		: title,
			calledby	: calledby,
			id			: element,
			refresh		: true,
			url			: $self.attr('href'),
			data		: form_data,
			type		: 'add',
			height		: 550,
			width		: 550,
			resizable	: true,
			callback	: function() {
			

			}
		});
		
	});
		
	//**********
	//  PRINTING

	/* 
	 * these rules are specific to the legacy function printAction, these rules will be
	 * maintained as certain areas rely on them where a dialog is not appropriate.
	 */
	
	// original print action
	$('#printtype', '#print_action').live('change', function () {
		toggle_print_elements(this, ["csv"]);
	});
	
	$('#printaction', '#print_action').live('change', function () {
		toggle_print_elements(this, ["Print", "Save", "Email"]);
	});
	
	// new print dialog
	$('#printtype', '#print_dialog').live('change', function () {
		$('.output_options').hide();
		$('.' + $(this).val() + '_options').show();
	});
	
	$('#printaction', '#print_dialog').live('change', function () {
		$('.action_options').hide();
		$('.' + $(this).val() + '_options').show();
	});
	

	 //********
	//  SEARCH

	// advanced search
	$('#show_advanced_search').live('click', function () {
		
		var $self				= $(this),
			$advanced_search	= $('#advanced_search');
		
		/* removed blind animation as jQuery wasn't */
		if ($advanced_search.is(":visible")) {
			$advanced_search.hide();
			$self.val('+');
		} else {
			$advanced_search.show();
			$self.val('-');
		}
		
	});
	
	// AJAXed search button
	$('#submit_holder #search_submit, #submit_holder #search_clear, .ajax_related_item form input[type=submit][name=saveform]').live('click', function (event) {
		
		event.preventDefault();
		
		var form = $(event.currentTarget).parents('form');
		
		// take the selected fields and generate hidden fields for them
		$(".selected_fields li").each(function () {
			form.append('<input type="hidden" name="Search[display_fields]['+$(this).attr('id')+']" value="' + $(this).text() + '" />'); 
		});
		
		// if we want to set other rules for the form, include the ignore_rules class
		if (!form.hasClass('ignore_rules')) {

			// jQuery will not serialize submit buttons, so append the button name and value to the data
			var form_data = form.serialize() + "&" + $(this).attr("name") + "=" + $(this).attr("value") + "&ajax=''";
			
			$('#included_file').uz_ajax({
				type		: 'POST',
				url			: form.attr('action') + "&ajax=",
				data		: form_data,
				highlight	: false,
				complete	: function() {
					check_if_table_needs_scroll_bar();	
					
					drag_drop_fields();
						
				}
			});
			
		}
		
	});
	
	drag_drop_fields();
		
	// breadcrumb automatic search
	$('.uz_breadcrumbs select').live('change', function () {
		$(this).parents('form').find('#search_submit').click();
	});
	
	// PRINT BUTTON IN SEARCH BOX
	$('#search_print').live('click', function (event) {
				
		event.preventDefault();
		
		// get the query string as an object
		var $_GET = getQueryParams($('#save_form').attr('action'));
		
		// set a few vars
		var form = $(this).parents('form');
	
		// get form elements and variables
		var form_data = form.serialize() + "&ajax=''";

		// if we're dealing with the index search...
		if ($_GET.action === 'index' || $_GET.action === undefined || $_GET.action === '' || $('#print_force_index').length) {
			
			// we don't want to use the form action, modify it to use action=PrintCollection
			// at the end of the replace put a &, we don't want and existing action to append to the end
			// e.g. http://example.com/?action=index --> http://example.com/?action=PrintCollectionindex
			//						   adding a & to the end of our find / replace prevents this ^
			
			if ($_GET.printaction === undefined || $_GET.printaction === '')
			{
				// no printaction defined so redirect via printDialog
				$_GET.printaction	= 'PrintCollection';
				$_GET.action		= 'printDialog';
			}

			var link = '/?' + makeQueryString($_GET) + '&ajax=';
			uz_print_dialog({
				url: link,
				data: form_data + "&index_key=" + randomString() + "&index_link=" + Base64.encode(form.attr('action') + '&ajax=') + "&Search[print]=print"
			});
			
		} else {
			
			// if we're not on an index search we need to do something a bit different
			// by definition, other pages w/ search handle it locally, not in the controller like index
			
			if ($_GET.printaction === undefined || $_GET.printaction === '')
			{
				// no printaction defined so redirect via printDialog
				$_GET.printaction	= $_GET.action;
				$_GET.action		= 'printDialog';
			}

			var link = '/?' + makeQueryString($_GET);
						
			uz_print_dialog({
				url: link,
				data: form_data
			});
			
		}
		
	});
	

	 //********
	//  EGLETS

	// dragging and dropping of available to selected eglets
	$(".available_eglets, .selected_eglets").sortable({
		items: "> li:not(.none)", 
		placeholder: 'ui-state-highlight',
		connectWith: '.connectedSortable',
		cursor: 'move',
		activeclass: 'sortableactive',
		hoverclass: 'sortablehover',
		helperclass: 'sorthelper',
		opacity: 0.5,
		fit: false,
		stop: function (event, ui) {
			if ($('.selected_eglets').children('li').size() > 0) {
				// ATTENTION: fadeOut doesn't appear to be working
				$('.selected_eglets li.none').fadeOut().remove();
			} else {
				$('.selected_eglets').append('<li class="none">None Currently Selected</li>');
			}
		}
	}).disableSelection();
		
	// take the selected eglets and generate hidden fields for them
	$("#select_eglets_footer input[type=submit]").click(function () {
		
		var form = $("#select_eglets_footer form");
		
		$(".selected_eglets li").each(function () {
			form.append('<input type="hidden" name="eglets[]" value="' + $(this).attr('id') + '" />'); 
		});
		
	});	
	
	// we could use CSS for this, just base it on class:hover
	$('.eglet img.eglet_open').live('click', function (event) {
		var element = $(event.target);
		element.parents('div.eglet').find('.eglet_body').hide('blind', {}, '300');
		var img_src = element.attr('src').replace('open', 'closed');
		element.removeClass('eglet_open').addClass('eglet_closed').attr('src', img_src);
	});
	
	$('.eglet img.eglet_closed').live('click', function (event) {
		var element = $(event.target);
		element.parents('div.eglet').find('.eglet_body').show('blind', {}, '300');
		var img_src = element.attr('src').replace('closed', 'open');
		element.removeClass('eglet_closed').addClass('eglet_open').attr('src', img_src);
	});
	
	// we cannot use CSS to hover, because CSS cannot handle img src... we also won't have any control over the theme
	$('.eglet h2 img').live('mouseover', function (event) {
		var element = $(event.target);
		var img_src = element.attr('src').replace('nofocus', 'focus');
		element.attr('src', img_src);
	});
	
	$('.eglet h2 img').live('mouseout', function (event) {
		var element = $(event.target);
		var img_src = element.attr('src').replace('focus', 'nofocus');
		element.attr('src', img_src);
	});
	
	// eglet a.ajax
	$('.eglet a.ajax').live('click', function (event) {
		event.preventDefault();
		
		var $self = $(this),
			$_GET = getQueryParams($self.attr('href'));
		
		if ($_GET._target !== null && $_GET._target !== undefined) {
			var $target = $("#" + $_GET._target);
		} else {
			var $target = $self.parents('div.eglet_include').parent();
		}
		
		$target.uz_ajax({
			url			: $self.attr('href'),
			highlight	: false
		});
		
	});
	
	// eglet orders_type
	$('.eglet #orders_summary #orders_type').live('change', function (event) {
		
		var $self = $(this);
		
		$('#orders_summary').uz_ajax({
			data: {
				module		: '',
				submodule	: 'sales_order',
				controller	: 'sorders',
				action		: 'sorders_summary',
				type		: $self.val(),
				ajax		: ''
			}
		});
	});
	
	// eglet invoice_type
	$('.eglet #invoices_summary #invoices_type').live('change', function (event) {
		
		var $self = $(this);
		
		$('#invoices_summary').uz_ajax({
			data: {
				module		: '',
				submodule	: 'sales_invoicing',
				controller	: 'sinvoices',
				action		: 'sorders_summary',
				type		: $self.val(),
				ajax		: ''
			}
		});
	});

	
	 //***************
	//  SEARCH MATRIX

	$('a.clone_matrix').live('click', function () {
		$('.matrix_field:last', '#matrix_parent_id').clone().appendTo('#matrix_parent_id');
		$('input,select,textarea', '.matrix_field:last').val('');
		
	});

	$('a.remove_matrix').live('click', function (event) {
		event.preventDefault();
		if ($(this).parents('#matrix_parent_id').children('.matrix_field').length > 1) {
			$(this).parents('p').remove();
		}
	});	
	
	
	 //**************
	//  SELECT ITEMS

	// select all items, common-select_items
	$('a.select_all', '.common-select_items').live('click', function (event) {
		event.preventDefault();
		// set default value for hidden field
		$('#targets_text').val('');
		// loop through available checkboxes
		$('.item_select[type=checkbox]').each(function () {
			// select checkbox
			$(this).prop('checked', true);
			// get the interogated data
			var line_data = $(this).parents('tr').children('input.item_data').val();
			// append the id and line data to the targets field
			$('#targets_text').val($('#targets_text').val() + "^" + line_data.replace("__REPLACE__", 'true'));
		});
		// force change on hidden field
		$('#targets_text').trigger("change");
	});
	
	// remove all items, common-select_items
	$('a.remove_all', '.common-select_items').live('click', function (event) {
		event.preventDefault();
		// set default value for hidden field
		$('#targets_text').val('');
		// loop through remove buttons
		$('button.item_remove', '.common-select_items').each(function () {
			// deselect checkbox if it exists
			$('#checkbox_' + $(this).attr('rel')).removeAttr('checked');
			// append the id and false to the targets field
			$('#targets_text').val($('#targets_text').val() + "^" + $(this).attr('rel') + "=false");
		});
		// force change on hidden field
		$('#targets_text').trigger("change");
	});
	
	// select single item, common-select_items
	$('input.item_select', '.common-select_items').live('change', function (event) {
		var line_data = $(this).parents('tr').children('input.item_data').val();
		$('#targets_text').val(line_data.replace("__REPLACE__", $(this).prop('checked'))).trigger('change');
	});
	
	// remove single item, common-select_items
	$('button.item_remove', '.common-select_items').live('click', function (event) {
		event.preventDefault();
		$('#checkbox_' + $(this).attr('rel')).removeAttr('checked');
		$('#targets_text').val($(this).attr('rel') + '=false').trigger("change");
	});

	
	 //***************
	//  SELECT TARGET

	// select all items, common-selector_list_target
	$('a.select_all', '.common-selector_list_target').live('click', function (event) {
		event.preventDefault();
		// set default value for hidden field
		$('#targets_text').val('');
		// loop through available checkboxes
		$('.target_select[type=checkbox]').each(function () {
			// select checkbox
			$(this).prop('checked', true);
			// get the interogated data
			var line_data = $(this).parents('tr').children('input.item_data').val();
			// append the id and line data to the targets field
			$('#targets_text').val($('#targets_text').val() + "^" + line_data.replace("__REPLACE__", 'true'));
		});
		// force change on hidden field
		$('#targets_text').trigger("change");
	});
	
	// remove all items, common-selector_list_target
	$('a.remove_all', '.common-selector_list_target').live('click', function (event) {
		event.preventDefault();
		// set default value for hidden field
		$('#targets_text').val('');
		// loop through remove buttons
		$('button.remove_target').each(function () {
			// deselect checkbox if it exists
			$('#checkbox_' + $(this).attr('rel')).removeAttr('checked');
			// append the id and false to the targets field
			$('#targets_text').val($('#targets_text').val() + "^" + $(this).attr('rel') + "=false");
		});
		// force change on hidden field
		$('#targets_text').trigger("change");
	});
	
	// select single item, common-selector_list_target
	$('input.target_select', '.common-selector_list_target').live('change', function (event) {
		var line_data = $(this).parents('tr').children('input.item_data').val();
		$('#targets_text').val(line_data.replace("__REPLACE__", $(this).prop('checked'))).trigger('change');
	});
	
	$('button.remove_target', '.common-selector_list_target').live('click', function (event) {
		event.preventDefault();
		$('#checkbox_' + $(this).attr('rel')).removeAttr('checked');
		$('#targets_text').val($(this).attr('rel') + '=false').trigger("change");
	});
	
	// item selector generic
	$('.common-select_items #targets_text, .common-selector_list_target #targets_text').live('change', function () {
		
		var $self = $(this);
		
		$('#targets').uz_ajax({
			url: '/?' + $('#target_link').val(),
			data: {
				id		: $self.val(),
				ajax	: ''
			}
		});
		
	});
	
	
	 //************************
	//  SELECT FOR OUTPUT RULE

	// set bind for elements 
	// cannot use context here, as ajax is fired in places where it shouldn't :: 
	$('.select-for-output input, .select-for-output select').live('change', function () {
		
		var $self	= $(this),
			value	= $self.val(),
			field	= $self.data('field'),
			row		= $self.data('row-number');
		
		if ($self.is(':checkbox')) {
			value = $self.is(':checked');
		}
		
		$('#selected_count').uz_ajax({
			url: '/?' + $('#link').val() + '&' + field + '=' + value,
			data: {
				id		: row,
				ajax	: ''
			},
			block_method: 'select-for-output',
			block: function() {
				$('input[type=submit]').attr('disabled', 'disabled');
			},
			unblock: function() {
				$('input[type=submit]').removeAttr('disabled');
			}
		});
		
	});
	
	
	 //************************
	//  PAGING SELECT RULE

	$('.paging-select input').live('change', function () {
		
		var $self	= $(this),
			total = isNaN(parseFloat($('#selected_count').val()))?0:parseFloat($('#selected_count').val());;
		
		if ($self.is(':checked')) {
			total += 1;
		}
		else
		{
			total -= 1
		}
		
		$('#selected_count').val(total);
		
	});
	
	
	 //********
	//  OTHERS
	
	$('#company_selector #company').live('change', function () {
		$(this).parents('form').submit();
	});
	
	// get user confirmation before deleting
	$('a[href*="action=delete"]').not('a.confirm').live('click', function (event) {

		var answer = confirm("Are you sure you want to delete this item?");
		if (!answer) {
			return false; 
		}
	 
	});

	// View Section show-hide
	
	$('.expand.heading').live('click', function() {
		
		var $self	= $(this),
		$next	= $self.next();
		
		if ($self.hasClass('closed'))
		{
			$self.removeClass('closed');
			$self.addClass('open');
		} else {
			$self.removeClass('open');
			$self.addClass('closed');
		}
	
		$next.slideToggle( 500 );
		
	});
	
	 //********
	// SORTING
	
	$('.ul-sort').live('click', function(event) {
		
		event.preventDefault();

		var $ul = $('#' + $(this).data('sort-element')),
			$li	= $ul.find('li');
		
		$li.tsort();
		
	});
	
	 //**********************
	// DASHBOARD COLLAPSABLE
	
	$.fn.hideCollapsible = function(speed, easing, callback) {
		return this.animate({opacity: '0', marginTop: '-'+this.outerHeight() }, speed, easing, callback);
	};
	
	$.fn.showCollapsible = function(speed, easing, callback) {
		return this.animate({opacity: '1', marginTop: '0'}, speed, easing, callback);
	};
	
	$('ul.collapsible > li div:first-of-type span').live('click',function() {
		
		var $collapsible	= $(this).parents('li'),
			$last_div		= $collapsible.find('div:last-of-type');


		if ($collapsible.hasClass('collapsible-hidden')) {
			
			// preset the top margin to prevent non-animation
			$last_div.css('marginTop', parseInt('-' + $last_div.outerHeight(), 10));

			$collapsible.removeClass('collapsible-hidden');
			
			$last_div.showCollapsible();
			
		} else {
			
			$collapsible.find('div:last-of-type').hideCollapsible('', '', function() {
				$collapsible.addClass('collapsible-hidden');
			});
			
		}

	});
	
});

function drag_drop_fields() {
	
	// dragging and dropping of available to selected fields
	$(".available_fields, .selected_fields").sortable({
		items: "> li:not(.none)", 
		placeholder: 'ui-state-highlight',
		connectWith: '.connectedSortable',
		cursor: 'move',
		activeclass: 'sortableactive',
		hoverclass: 'sortablehover',
		helperclass: 'sorthelper',
		opacity: 0.5,
		fit: false,
		stop: function (event, ui) {
			if ($('.selected_fields').children('li').size() > 0) {
				// ATTENTION: fadeOut doesn't appear to be working
				$('.selected_fields li.none').fadeOut().remove();
			} else {
				$('.selected_fields').append('<li class="none">None Currently Selected</li>');
			}
		}
	}).disableSelection();
		
}
function resize_window() {
	
	if (document.getElementById("mainNav") === null) {
		var mainNav = $("#primary-nav");
	} else {
		var mainNav = $("#mainNav");
	}

	if (mainNav.length !== 0) {
		var footer = $("#footer");
		var content_height = $(this).height() - ((mainNav.position().top + mainNav.height()) + footer.height() + 30 + (2 * 15) + 2);
		var sidebar_height = $(this).height() - ((mainNav.position().top + mainNav.height()) + footer.height() + 30 + 10);

		$('#main_without_sidebar').css('height', content_height + 'px');
		$('#main_with_sidebar').css('height', content_height + 'px');
		$('#sidebar').css('height', sidebar_height + 'px');
		
		check_if_table_needs_scroll_bar();		
		
	}
	
}

function resize_resizable() {
	
	$('.uz-resizable').each(function () {
		
		// get self and parent elements
		var self	= $(this);
		var parent	= $(self.data('uz-resizable-parent'));
		
		// set default width and height
		var width		= 0;
		var height		= 0;
		var padding_x	= 0;
		var padding_y	= 0;
		
		if (!self.data('uz-resizable-ignore-siblings')) {
			self.siblings().each(function () {
				width	+= $(this).outerWidth();
				height	+= $(this).outerHeight();
			});
		}
		
		// calculate padding, assuming we're working with pixels
		padding_x	= parseInt(self.css("padding-right"))
					+ parseInt(self.css("padding-left"));
		padding_y	= parseInt(self.css("padding-top"))
					+ parseInt(self.css("padding-bottom"));

		// set calculated width
		self.width(parent.width()-width-padding_x-1);

	});
	
}

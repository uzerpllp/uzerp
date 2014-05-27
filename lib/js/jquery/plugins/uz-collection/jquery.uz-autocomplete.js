/**
 * jquery.uz-autocomplete.js
 *
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
(function($) {
	
	$.uz_autocomplete = function(el, options) {
		
		// To avoid scope issues, use 'base' instead of 'this'
		// to reference this class from internal events and functions.
		
		var base = this;
		
		// Access to jQuery and DOM versions of element
		base.$el = $(el);
		base.el = el;
		
		// Add a reverse reference to the DOM object
		base.$el.data("uz_autocomplete", base);
		
		base.init = function() {
			
			base.options = $.extend({},$.uz_autocomplete.defaultOptions, options);
			
			if (base.$el.parents('tr').attr('id') == "rowtemplate") {
				return;
			}
			
			// cache the hidden field selector
			base.$hidden = $("#" + base.$el.data("id"));
			
			
			// set input watermark
			base.$el.watermark('Too many entries - please start typing');
			$.watermark.options.useNative = false;
			
			var static_data = {
					'module'			: base.$el.parents(".content_wrapper").data("module") ,
					'controller'		: base.$el.parents(".content_wrapper").data("controller"),
					'action'			: base.$el.data("action"),
					'autocomplete'		: true,
					'ajax'				: '',
					'field'				: base.$el.data('attribute'),
					'use_collection'	: base.$el.data('use_collection')
				},
				var_data = [];
			
			if (base.$el.data("identifierfield") !== undefined) {
				
				var var_identifier_field = '';
				
				for (i = 0; i < base.$el.data("identifierfield").length; i++) {
					
					if (i == 0) {
						var_identifier_field = base.$el.data("identifierfield")[i];
					} else {
						var_identifier_field += ',' + base.$el.data("identifierfield")[i];
					}
					
				}
				
				static_data['identifierfield'] = var_identifier_field;
				
			}
			
			if (base.$el.data("depends") !== undefined) {
				
				var var_depends	= '',
					model		= base.$hidden.attr('name');
				
				model = model.substr(0,model.indexOf('['));
				
				for (i = 0; i < base.$el.data("depends").length; i++) {
					
					var current	= base.$el.data("depends")[i],
						key		= '',
						id		= '';
					
					if (current.indexOf(':') > -1) {
						
						key	=current.substr(0, current.indexOf(':'));
						id	=model + '_' + current.substr(current.indexOf(':') + 1);
						
					} else {
						
						key	= current;
						id	= model + '_' + key;
						
					}
					
					id				= id.replace('_REPLACE_', '_' + base.$el.parents('tr').attr('id'));
					var_data[key]	= id;
					
					if (i == 0) {
						var_depends = key;
					} else {
						var_depends += ',' + key;
					}
					
				}
				
			}
			
			base.$el.bind('focus mouseup', function(event) {
				
				// select entire contents of field on entry

				// if event === focus, select all text...
				if (event.type === 'focusin' || event.type === 'focus') {
		 		   $(this).select();
				}

				// if event === mouse up, return false. Fixes Chrome bug
				if (event.type === 'mouseup') {
					return false;
				}

			});
			
			var original_id		= base.$hidden.val(),
				original_value	= base.$el.val();
			
			base.$el.blur(function() {
				
				if (original_value != base.$el.val() && original_id == base.$hidden.val()) {
					base.$el.val(original_value);
				}
				
			});
			
			base.$el.autocomplete({
				source: function( request, response ) {
			
					if (base.$el.data("action") == "array") {
					
						response($.map(eval(window[base.$el.data('id')]), function(item) {
							
							if (item.value.startsWith(request.term)) {
								return {
									label: item.value,
									value: item.id
								}
							}
							
						}));
						
					} else {
						
						if (base.$el.attr('readonly')) {
							return false;
						}
						
						var ajax_data = static_data;
						
						ajax_data['id'] = base.$el.val();
						
						for (var key in var_data) {
							ajax_data[key] = $('#' + var_data[key]).val();
						}
						
						if (base.$el.data("depends") !== undefined) {
							ajax_data['depends'] = var_depends;
						}
						
						// fetch the ajax request from the element data
						// it's stored as .data() to stop contaimation with other fields
						
						var xhr = base.$el.data('ajax_request');

						// if we've got an existing xhr, abort it
						// we do this instead of setting async to false because that locks the browser
						
						if (xhr !== undefined && xhr !== null) {
							xhr.abort()
						}
				
						var ajax_request = $.ajax({
							url			: '/',
							dataType	: "json",
							data		: ajax_data,
							success		: function(data) {
							
								// set the current ajax request to null
								base.$el.data('ajax_request', null)
								
								response( $.map( data, function( item ) {
									return {
										label: item.value,
										value: item.id
									}
								}));
								
							},
							error: function() {
								
								// if we've aborted this ajax, pass an empty array to response
								// this prevents autocomplete not unsetting the loading class
								
								response([]);
								
							}
						});
						
						// set the last ajax request back to the element
						base.$el.data('ajax_request', ajax_request);
						
					}
				},
				minLength: 2,
				focus: function( event, ui ) {
					return false;
				},
				select: function( event, ui ) {
					base.$el.val( ui.item.label );
					base.$hidden.val(ui.item.value).trigger('change');
					return false;
				},
				open: function( event, ui ) {
					
					base.$hidden.val(original_id);
					
					var autocomplete = $(this).data("autocomplete");
					
					menu = autocomplete.menu;

	 				if (menu.element.children().length == 1) {
	 					
						var item = menu.element.children().data( "item.autocomplete" );
						
						autocomplete.selectedItem = item;
						autocomplete._trigger( "select", event, { item: autocomplete.selectedItem } );
						
					}

				},
				close: function( event, ui ) {
					
					if (base.$hidden.val() == "") {
						base.$el.val('');
					}
					
				}
			});
			
		};
		
		// Run initializer
		base.init();
		
	};
	
	$.uz_autocomplete.defaultOptions = {
	};
	
	$.fn.uz_autocomplete = function(options) {
		
		return this.each(function() {
			(new $.uz_autocomplete(this, options));
		});
		
	};
	
})(jQuery);

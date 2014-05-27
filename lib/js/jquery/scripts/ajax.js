/**
 * ajax.js
 *
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

;(function ($) {
	
	/* $Revision: 1.37 $ */
	
	var ajax_count			= 0;
		block_count			= [],
		output_debug		= false,
		last_logout_check	= 0;
	
	// uz_ajax_base is never called directly, always instantiated via a wrapper function
	$.uz_ajax_base = function (options, el) {
		
		// To avoid scope issues, use 'base' instead of 'this'
		// to reference this class from internal events and functions.

		var base = this;
		
		// we only want to set the base elements + reverse reference 
		// if we've applied this plugin against a selector
		
		if (el !== undefined) {
			
			// Access to jQuery and DOM versions of element
			base.$el = $(el);
			base.el = el;

			// Add a reverse reference to the DOM object
			base.$el.data("uz_ajax", base);
			
		}
		
		base.init = function () {

			base.options = $.extend({}, $.uz_ajax.defaultOptions, options);

			// If there is only one target, get the field name if defined
			// and get the depend attribute of the target, if defined
			debug.info('Checking for target');
			debug.debug(base.options);
			if (base.options.target.length) {
				debug.info('Target exists and has length '+base.options.target.length);
				if($.isArray(base.options.target) && base.options.target.length==1) {
					if (base.options.target[0].field!==undefined) {
						base.options.data.field = base.options.target[0].field;
					}
					if (base.options.target[0].element!==undefined && $(base.options.target[0].element).data('depends')!==undefined) {
						var varDepends='';
						var dataDepends=$(base.options.target[0].element).data('depends');
						for (i=0; i<dataDepends.length; i++) {
							var current=dataDepends[i];
							var key='';
							if (current.indexOf(':')>-1) {
								key=current.substr(0,current.indexOf(':'));
							} else {
								key=current;
							}
							if (i==0) {
								varDepends=key;
							} else {
								varDepends+=','+key;
							}
						}
						base.options.data.depends = varDepends;
					}
					debug.info('Target element is '+base.options.target[0].element);
					$(base.options.target[0].element).siblings().each(function () {
						if($(this).hasClass('uz-autocomplete')) {
							base.options.data.autocomplete = true;
							debug.info('enabling autocomplete');
						}
					});
				}
			}
	
			$.ajax({
				
				// method variables
				async		: base.options.async,
				cache		: base.options.cache,
				data		: base.options.data,
				dataType	: base.options.dataType,
				type		: base.options.type,
				url			: base.options.url,
				
				// method callbacks
				beforeSend: function() {
				
					// check if the user is logged in, no point in continuing if not
					// we do this check here so we can return false to stop the main request
					
					if ($.uz_ajax.isLoggedIn() === false) {
						base.options.loggedOut();
						return false;
					}
					
					// increment the generic ajax count
					ajax_count += 1;
					
					// display the loading data element
					if (ajax_count === 1) {
						$('.loading').fadeIn(200);
					}
					
					base.block();
					
				},
				success: function(response) {

					// either fire the callback or use the standard function					
					if (base.options.success) {
						base.options.success(response, base);
					} else {
						base.processResponse(response);
					}
					
				},
				complete: function () {

					// decrement the generic ajax count
					ajax_count -= 1;
					
					// hide the loading data element
					if (ajax_count === 0) {
						$('.loading').stop().fadeOut(200);
					}
					
					base.unblock();
					
					if (base.options.complete) {
						base.options.complete();
					}
					
				}
			});
			
		};
				
		base.processResponse = function(response) {
			
			base.debug('uz_ajax: success callback', 'info');
			
			var response_type		= typeof(response),
				plugin_selector		= false;
			
			// if the base element has been set we need a way to force the loop (below), but 
			// also a flag so we no to grab that element
			
			if (base.$el !== undefined) {
				
				// if we're dealing with selectors, set target as a blank array
				// ATTN: why should we have to do this? need to investigate, scope issues
				//       without this ajax request targets are can stray between requests!
				
				base.options.target = [];
				
				// if the base.$el is set set a counter, loop through the elements
				// and apply to the base.options.target array
				
				var counter = 0;
				
				plugin_selector = true;
				
				base.$el.each(function() {
					base.options.target[counter++] = $(this);
				});
				
			} else {
				
				// no base element, get targets from options
				if (!$.isArray(base.options.target)) {
					base.options.target = [base.options.target];
				}
				
			}
			
			if (base.options.target.length) {
							
				// create and cache the staging area element
				// do this outside of the loop to prevent multiple instances occuring
				
				var $ajax_staging = $.uz_ajax.createStagingArea();
				
				// apply the response to a staging element, it's just so much easier to 
				// work with the data from jQuery when it exists within the DOM.
				
				$ajax_staging.html(response);
				
				// if the response has a cancel button, add a class to it so we can apply a rule to it
				$ajax_staging.find('#cancelform').addClass('ajax_cancel');
								
				var targets = base.options.target;
							
				var defer_trigger_change = [];
				
				for ( var i in targets ) {
					
					// by default we want to set the target value, set this to false to prevent this action
					var output_response = true,
						temp_response,
						target = {};
					
					// fill in the gaps, apply any missing options from target_defaults
					target.options = $.extend({}, $.uz_ajax.targetOptions, targets[i]);
					
					// cache the target element
					// what we cache will depend on where it's being set
					
					// cache different sources depending on how plugin was called
					if (plugin_selector === true) { 
						target.$el = targets[i]; 
					} else {
						target.$el = $(target.options.element);
					}
					target.$rebind=target.$el;
					
					// set the tag name for future use
					if (target.$el.get(0).tagName !== undefined) {
						target.tagName = target.$el.get(0).tagName.toLowerCase();
					}
					
					// message in log of the element we're targeting
					base.debug('uz_ajax() --> Applying contents to the following element:');
					base.debug(target.$el, 'debug');
					
					// if a field has been set on the target, pick that field out of the response
				    var response_type = 'html';

				    if (target.options.field !== null) {

				        // we're looking for part of a multiple response

				        var $staging_field = $('#ajax_' + target.options.field, $ajax_staging),
				            $current_field = $staging_field;

				        // decide whether to use text or html from the response
				        if (target.$el.is("input")) {
				            temp_response = $staging_field.text();
				        } else {
				            temp_response = $staging_field.html();
				        }

				        // if a field is specified but the response field doesn't exist...
				        if (target.options.field !== null && !$('#ajax_' + target.options.field, $ajax_staging).length) {

				            // ... don't output the response
				            output_response = false;

				        }

				    } else {

				        // the response comprises of a single field only
				        temp_response = $ajax_staging.html();

				        var $current_field = $ajax_staging;

				    }

				    // autocomplete check

				    var autocomplete = false

				    // NOT STAGING, but common ground
				    $current_field.children().each(function () {

				        if ($(this).hasClass('uz-autocomplete')) {

				            autocomplete = true;
				            debug.info('enabling autocomplete');

				        }

				    });

				    var $first_element = $current_field.children(':first-child')

				    if (($first_element.get(0) !== undefined && $first_element.get(0).tagName == 'SELECT') || autocomplete) {

				        // so we do want to use the replace action
				        target.options.action = 'replace';

				        // to be safe, reset temp_response
				        // only because we may have set this as text in the condition above
				        temp_response = $current_field.html();

				    }

				    // trim the response, but only if not null
					if (temp_response !== null && temp_response !== undefined && response_type !== 'object') {
						temp_response = trim(temp_response);
					}
					
					// continue to set the target value if we have not set output_response to false
					if (output_response) {
					
						if (target.$el.length) {
							
							// apply the response depending on the requirements
							switch (target.options.action) {
							
								case "replace":
									var $current_parent=target.$el.parent();
									target.$el.siblings().addClass('flag_remove');
									target.$el.addClass('flag_remove').after(temp_response);
									$('.flag_remove').remove();
									target.$el=$current_parent.children().find(':first-child');
									target.$rebind=target.$el;

									break;
								
								case "normal":
									
									if (target.$el.is("input")) {
										target.$el.val(temp_response);
									} else {
										
										base.debug('Applying contents to a ' + target.tagName + ' element');
										
										switch (target.tagName.toUpperCase()) {
										
											// tagName returns an uppercase string
											// this section will NEED to be extended as time goes by to accomodate for more element types
										
											case "SELECT":

												base.debug(target.$el.attr('multiple'));

												if (target.$el.attr('multiple') !== false) {
													
													if (!target.$el.hasClass('nonone') && !target.$el.hasClass('required')) {
														target.$el.html('<option label="None" value="">None</option>' + temp_response);
													} else {
														target.$el.html(temp_response);
													}
													
												} else {
													target.$el.html(temp_response);
												}
												
												break;
												
											default:
												target.$el.html(temp_response);
												break;
												
										}
									}
									
									break;
									
								case "selected":
									
									// preserve the current target value (e.g. list of options), but 
									// set the selected value to the ajax response
									base.debug('Changing selected value of '+ target.$el + ' to "' + temp_response + '"', 'warn');
									
									// trim the response so we don't get any smarty induced errors such as '    7'
									target.$el.val(temp_response);
									
									break;
									
							}
							
							// set selected value
							if (target.options.selected.value!=null) {
								target.$el.val(target.options.selected.value);
							}
						
						} else {
							base.debug('uz_ajax: current target ' + target.$el.selector + ' does not exist', 'warn');
						}
						
					}
					
					// fire the scrollTo callback, but only if target is a div element
					if (target.tagName === 'div' && base.options.scrollTo !== false && target.options.scrollTo !== false) {
							
						// global callback takes priority (if it exists), otherwise use target callback
						if (target.options.scrollTo) {
							target.options.scrollTo(target.$el);
						} else if (base.options.scrollTo) {
							base.options.scrollTo(target.$el);
						}

					}
					
					// do a quick check to ensure the element is suitable for highlighting
					var highlight = false;
					
					// allow higlight if we're dealing with an input, select or textarea
					// but only if it is visible (this includes type=hidden)
					
					if (target.tagName in oc(['input', 'select', 'textarea']) && target.$el.is(":visible")) {
						highlight = true;
					}
					
					// fire the highlight callback, but only if target is not a hidden input element
					if (highlight === true && base.options.highlight !== false && target.options.highlight !== false) {
						
						// global callback takes priority (if it exists), otherwise use target callback
						if (target.options.highlight) {
							target.options.highlight(target.$el);
						} else if (base.options.highlight) {
							base.options.highlight(target.$el);
						}
						
					}
					
					// force the target to change
					if (target.options.force_change === true) {
							target.$el.trigger('change');
					}

					// we want to make sure the page title has been updated
					$('.page_title').trigger("change");
					
					// rebind plugins to the new content we've got coming in
					rebind_plugins(target.$rebind);
					
				}
				
				// remove the unique ajax_staging element
				$ajax_staging.remove();
				
			}
			
		}
				
		base.debug = function(message, type) {
			
			// NOTE: this function expects the debug object to exist
			
			// if a debug callback exists send the data to that instead					
			if (base.options.debug) {
				base.options.debug(message, type);
			} else {
				
				if (output_debug === true && debug !== undefined) {
					
					switch (type || 'info') {
							
						case 'log':
							debug.log(message);
							break;
							
						case 'debug':
							debug.debug(message);
							break;
							
						case 'warn':
							debug.warn(message);
							break;
							
						case 'error':
							debug.error(message);
							break;
					
						case 'info':
						default:
							debug.info(message);
							break;
							
					}
							
				}
				
			}
		
		}
		
		base.block = function() {
		
			// first check the array item exists, giving it a starting value of 0 if it doesn't
			if (block_count[base.options.block_method] === undefined) {
				block_count[base.options.block_method] = 0;
			}
			
			// before we start incrementing, check if the block count is 0, thus we need to block
			if (block_count[base.options.block_method] === 0) {
				base.options.block();
			}
			
			block_count[base.options.block_method] += 1;
			
		}
	
		base.unblock = function() {
						
			if (block_count[base.options.block_method] === 0) {
				debug.error("Block count '" + base.options.block_method + "' decrement to less than 0");
			} else {
			
				block_count[base.options.block_method] -= 1;
				
				if (block_count[base.options.block_method] === 0) {
					base.options.unblock();
				}
				
			}
			
		}

		// Run initializer
		base.init();
		
	};

	// wrap the $.uz_ajax_base() function in the original uz_ajax function, this way we can
	// instanciate $.uz_ajax_base() using the new operator to maintain scope. This wrapper
	// function must exist before any of the objects or functions are applied to it below
	
	$.uz_ajax = function (options) {
		new $.uz_ajax_base(options);
	};
	
	// options and functions
	
	$.uz_ajax.defaultOptions = {
			
		// options
		async			: true,
		type			: "GET",
		data			: {},
		url				: "/",
		cache			: false,
		dataType		: null,
		lock_screen		: true,
		block_method	: 'screen', // can be any string, so long as it's unique to the method of blocking
		target			: [],
		
		// callbacks
		block: function() {
			if($.blockUI) {
				$.blockUI({message:''});
			}
		},
		unblock: function() {
			if($.blockUI) {
				$.unblockUI();
			}
		},
		scrollTo: function($element) {
			if($.scrollTo) {
				$element.scrollTo( { top:0, left:0 }, 1000 );
			}
		},
		highlight: function($element) {
			$element.effect("highlight", {}, 2000);
		},
		loggedOut: function() {
			alert("You are logged out");
		}
		
	};
	
	$.uz_ajax.targetOptions = {
		action			: "normal",
		field			: null,
		force_change	: true,
		selected		: {
			value		: null,
			disabled	: false
		}
	};

	$.uz_ajax.isLoggedIn = function () {
		
		var status			= true,
			check_delay		= 5, // minutes
			timestamp		= new Date().getTime() / 1000; // we want seconds, not milliseconds
		
		// this condition checks if we've gone more than x minutes since a login check
		// we don't fire this everytime as this ajax request will block others
		
		if ((timestamp - (check_delay * 60)) > last_logout_check) {
		
			// we have to use ajax instead of getJSON because we need to turn async off
			// we have to do this because we cannot return from the callback, and if we
			// try to set the status var wihout async it'll return before the ajax finished
			
			$.ajax({
				url			: '/lib/scripts/is_logged_in.php',
				dataType	: 'json',
				async		: false,
				success		: function(data) {
					status = data.logged_in;
				}
			});
		
			if (status === true) {
				last_logout_check = timestamp;
			}
			
		}
		
		return status;
		
	};
	
	$.uz_ajax.createStagingArea = function () {
		
		// the ajax identifier needs to be unique, so as well as a timestamp
		// lets also include a random number, just for good measure *paranoid*
		
		var timestamp		= new Date().getTime(),
			random_number	= Math.floor(Math.random() * 100000001),
			ajax_identifier	= 'ajax_stage_' + timestamp + '_' + random_number;
		
		// create the ajax staging area at the end of the body element
		$('body').append('<div id="' + ajax_identifier + '" style="display:none;"></div>');
		
		// return the jQuery object back to the callee
		return $('#' + ajax_identifier);

	};

	$.fn.uz_ajax = function (options) {
		
		// the new keyword ensures a new instance of $.uz_ajax_base() is fired
		// don't .each() around this elements, do that within the plugin
		
		return (new $.uz_ajax_base(options, this));
		
	};

})(jQuery);

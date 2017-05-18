/**
 * jquery.uz-constrains.js
 *
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
(function($) {
	
	$.uz_constrains = function(el, options) {
		
		// To avoid scope issues, use 'base' instead of 'this'
		// to reference this class from internal events and functions.
		
		var base = this;
		
		// Access to jQuery and DOM versions of element
		base.$el = $(el);
		base.el = el;
		
		// Add a reverse reference to the DOM object
		base.$el.data("uz_constrains", base);
		
		base.init = function() {
			
			base.options = $.extend({}, $.uz_constrains.defaultOptions, options);
			
			base.$el.change(function() {

				var $self		= $(this),
					var_target	= [],
					var_data	= {ajax: ''},
					source_id	= $self.attr('name');
				
				if ($self.data("constrains") !== undefined) {
					
					model = source_id.substr(0, source_id.indexOf('['));
					
					for (i = 0; i < $self.data("constrains").length; i++) {
						
						var current			= $self.data("constrains")[i],
							target_field	= '',
							target_id		= '';
						
						if (current.indexOf(':') > -1) {
							target_field	= current.substr(0, current.indexOf(':'));
							target_id		= model + '_' + current.substr(current.indexOf(':') + 1);
						} else {
							target_id = model + '_'+current;
						}
						
						var temp = {};
						
						temp.element	= '#' + target_id;
						temp.field		= current;
						
						var_target[i] = temp;
						
					}
					
				}
				
				var start	= source_id.indexOf('[') + 1,
					end		= source_id.indexOf(']');
				
				source_field = source_id.substr(start, end-start);
			
				if ($self.data('module') !== undefined) {
					var_data.module = $self.data('module');
				} else {
					var_data.module = $('.content_wrapper').data('module');
				}
				
				if ($self.data('controller') !== undefined) {
					var_data.controller = $self.data('controller');
				} else {
					var_data.controller = $('.content_wrapper').data('controller');
				}
				
				if ($self.data('action') !== undefined) {
					var_data.action = $self.data('action');
				} else {
					var_data.action = 'getOptions';
				}
				
				var_data[source_field] = $self.val();
				
				$.uz_ajax({
					target	: var_target,
					data	: var_data
				});
				
			});
			
		};
		
		// Run initializer
		base.init();
		
	};
	
	$.uz_constrains.defaultOptions = {
	};
	
	$.fn.uz_constrains = function(options) {
		
		return this.each(function() {
			(new $.uz_constrains(this, options));
		});
		
	};
	
})(jQuery);

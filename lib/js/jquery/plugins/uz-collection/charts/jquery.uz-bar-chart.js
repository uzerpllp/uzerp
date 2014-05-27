/**
 * jquery.uz-bar-chart.js
 *
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

;(function($){
	
	// $Revision: 1.2 $
	
	$.uz_bar_chart = function(options){
		
		// To avoid scope issues, use 'base' instead of 'this'
		// to reference this class from internal events and functions.
		var base = this;
		
		base.init = function () {
			
			// build base options, set defaults here, need to reference ourself
			base.options = $.extend(
				{}, 
				{
					horizontal: true,
					hint: {
						content: function () {
							return this.y + '';
						}
					},
					seriesList: [],
					legend: {}
				},
				options
			);
			
			// error checking
			
			if (base.options.type === null) {
				console.error("Must specify a chart type");
				return false;
			}
			
			if (base.options.identifier === null) {
				console.error("Must specify an identifier");
				return false;
			}
			
			// Access to jQuery and DOM versions of element
			base.$el = $('#' + base.options.identifier);
			
			if (!base.$el.length) {
				console.error("Chart element does not exist");
				return false;
			}
			
			// Add a reverse reference to the DOM object
			base.$el.data("uz_bar_chart", base);
			
			// BUILD CHART
			
			base.$el.wijbarchart(base.options);
			
		};
		
		// Run initializer
		base.init();
		
	};
	
	$.uz_bar_chart.defaultOptions = {
		type		: null,
		identifier	: null,
		data		: null
	};
		
})(jQuery);

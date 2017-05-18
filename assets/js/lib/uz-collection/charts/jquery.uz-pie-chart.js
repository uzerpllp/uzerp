/**
 * jquery.uz-pie-chart.js
 *
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

;(function($){
	
	// $Revision: 1.2 $
	
	$.uz_pie_chart = function(options){
		
		// To avoid scope issues, use 'base' instead of 'this'
		// to reference this class from internal events and functions.
		var base = this;
		
		base.init = function () {
			
			// build base options, set defaults here, need to reference ourself
			base.options = $.extend(
				{}, 
				{
					showChartLabels: false,
					hint: {
						content: function () {
							return this.data.label + ": " + this.value;
						}
					},
					animation:{
						enabled: false
					},
					seriesList: [] 
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
			base.$el.data("uz_pie_chart", base);
			
			// BUILD CHART

			base.$el.wijpiechart(base.options);
			
			// Bind events

			var duration		= 100,
				radiusOffset	= 10,
				offset			= {};

			base.$el.bind("wijpiechartmouseover.uz_pie_chart", function (e, objData) {
				
				if (objData != null) {
					
					var series = objData;
					var sector = $(this).wijpiechart("getSector", series.index);
					var shadow = sector.shadow;
						
					offset = sector.getOffset(radiusOffset);

					sector.animate({
						translation: offset.x + " " + offset.y
					}, duration);

					if (shadow) {
						shadow.animate({
							translation: offset.x + " " + offset.y
						}, duration);
					}
				}
			});

			base.$el.bind("wijpiechartmouseout", function (e, objData) {
				
				if (objData != null) {
				
					var series = objData;
					var sector = $(this).wijpiechart("getSector", series.index);
					var shadow = sector.shadow;
		
					sector.animate({
						translation: -offset.x + " " + offset.y * -1
					}, duration);

					if (shadow) {
						shadow.animate({
							translation: -offset.x + " " + offset.y * -1
						}, duration);
					}
					offset = {x:0, y:0};
				}
			});

			base.$el.bind("wijpiechartclick", function (e, objData) {
			
				if (base.options.data[objData.index].url !== undefined) {
					window.location.href = base.options.data[objData.index].url;
				}
			
			});			
			
		};
		
		// Run initializer
		base.init();
		
	};
	
	$.uz_pie_chart.defaultOptions = {
		type		: null,
		identifier	: null,
		data		: null
	};
		
})(jQuery);

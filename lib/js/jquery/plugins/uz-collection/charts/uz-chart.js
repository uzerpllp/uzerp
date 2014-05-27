/**
 * uz-chart.js
 *
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

// $Revision: 1.1 $

function chart_convert_dates(options) {
	
	if ($.isArray(options)) {

		var data_len = options.length;
		
		for (var i = 0; i < data_len; i++) {
			
			options[i] = chart_convert_single_date(options[i]);
			
		}
		
	} else {
	
		options = chart_convert_single_date(options);
		
	}
	
	return options;
		
}
	
function chart_convert_single_date(options) {
		
	if (options === undefined) {
		return;
	}
	
	// build up the date axis option, just so we've always got something to work with
	var default_options = {
		date_axis: {
			x: false,
			y: false
		}	
	};
	
	options = $.extend({}, default_options, options);
	
	// check + converts x axis dates
	
	if (options.date_axis.x === true) {
		
		var item_len = options.data.x.length;
		
		for (var j = 0; j < item_len; j++) {
			options.data.x[j] = new Date(options.data.x[j]);
		}
		
	}
		
	// check + convert y axis dates
		
	if (options.data.convert_y_dates === true) {
	
		var item_len = options.data.y.length;
	
		for (var j = 0; j < item_len; j++) {
			options.data.y[j] = new Date(options.data.y[j]);
		}
			
	}
		
	return options;
		
}

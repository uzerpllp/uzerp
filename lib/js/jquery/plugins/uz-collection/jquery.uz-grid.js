/*!
 * uzERP Grid plugin
 *
 * jquery.uz-grid.js
 *
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 */

/*!
 * Usage:
 * 
 *    $('#element').uz_grid();
 * 
 * The following classes need to be specified
 * 
 *    .uz-grid-table
 * 
 * The following classes are optional
 *   
 *    container				.uz-grid
 * 	  add row button		.uz-grid-add-row
 *    remove row button		.uz-grid-remove-row
 */

/*
 * callbacks
 * load, when the plugin has been aplied to an elements
 * rowAdded, after a row has been added
 * rowRemoved, after a row has been removed
 */

(function($){			
	
 	$.fn.extend({ 
 		
		//pass the options variable to the function
 		uz_grid: function(options) {
	 	
	 		var defaults = {
	 			load: function() {},
	 			rowAdded: function() {},
	 			rowRemoved: function() {}
	 		}
 		
	 		// merge the passed options with the defaults
	 		var options = $.extend(defaults, options);

	 		// loop through each element we're bound to
			return this.each(function() {
				
				// set regularly used jQuery selectors to variables
				var $uz_grid_table = $('.uz-grid-table > tbody',$(this));
				var $uz_grid_hidden_row = $('.uz-grid-hidden-row',$(this));
				
				// we need to make sure any readonly elements are disabled if a row already exitst, for example if were editing a row
				if($("tr",$uz_grid_table).length>0) {
					$('.readonly_if_lines :not(:selected)').attr('disabled','disabled');
				}
				
				// fire the load callback, passing the uz_grid_table element
				if (options.load)
					options.load($uz_grid_table);
				
				// click event for add row button
				$(this).find('.uz-grid-add-row').live('click',function(event) {
					// clone row takes a hidden dummy row, clones it and appends a unique row identifier to the id

					// get the last id
					var last_row = $uz_grid_table.find('tr:last').attr('id');
					if(last_row===undefined) {
						new_row=1;
					} else {
						new_row=parseInt(last_row.replace('row',''),10)+1;
					}

					// append element to target, changes it's id and shows it
					$uz_grid_table.append($uz_grid_hidden_row.clone(true).attr('id','row'+new_row).removeClass('uz-grid-hidden-row').show());
					
					// append unique row identifier on id and name attribute of seledct, input and a
					$('#row'+new_row).find('select, input, a').each(function(id) {
						$(this).appendAttr('id','row'+new_row);
						$(this).replaceAttr('name','_REPLACE_',new_row);
					});
					
					$('#row'+new_row).data('row-number',new_row);

					// disable all the readonly_if_lines options if this is the first row
					if(new_row==1) {
						$('.readonly_if_lines :not(:selected)').attr('disabled','disabled');
					}
					
			        // fire the rowAdded callback, passing the new row identifier
					if (options.rowAdded)
						options.rowAdded('row'+new_row);

				});
				
				// click event for remove row button
				$(this).find('.uz-grid-remove-row').live('click',function(event) {
					// Remove row does what it says on the tin, as well as a few other house keeping bits and pieces
					
					// remove the parent table row
					$(this).parents('tr').remove();
					
					// if we've removed the last row remove readonly locks
					if($uz_grid_table.find('tr').size()===0) {
						$('.readonly_if_lines :disabled').removeAttr('disabled');
					}
					
			        // fire the rowRemoved callback
					if (options.rowRemoved)
						options.rowRemoved();
					
				});
				
			});
	
		}
 	
	});
 	
})(jQuery);

/*!
 * uzERP form validation plugin
 *
 * jquery.uz-validation.js
 *
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
*/

/*!
 * Usage:
 * 
 *    $('form').uz_validation();
 * 
 * For the form to validate on the submit event, the submit button must have this class
 * 
 *    .uz-validate
 * 
 * The following classes are optional
 *   
 *    .uz-validate-required
 *    .uz-validate-string
 *    .uz-validate-number
 *    .uz-validate-date
 */

(function($){			
	
 	$.fn.extend({ 
 		
 		
		// pass the options variable to the function
 		uz_validation: function() {

 		
	 		return this.each(function() {
				
				$(this).submit(function(event) {
					
		 			var errors = 0;

					if($(this).hasClass('uz-validate')) {

						$(this).find('input, select, textarea').each(function() {
						
							$(this).removeAttr('style');
							
							if($(this).hasClass('uz-validate-required') && $(this).val()=='') {
								$(this).css('borderColor','red');
								errors++;
							}
							
						});
						
					}
					
					if(errors>0) {
						event.preventDefault();
						$('#flash').find('ul#errors').remove();
						$('#flash').append("<ul id='errors'><li>There are errors on the form</li></ul>");
					}

				});
				
			});
	
		}
 	
	});
 	
})(jQuery);

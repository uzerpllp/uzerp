/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/* $Revision: 1.4 $ */

$(document).ready(function() {
	
	/* dashboard -> mydata -> index */
	
	$(document).on('click', '#folders .heading','#dashboard-mydata-index', function() {
		
		if ($(this).hasClass('open')) {
			$('#'+$(this).data('type')).show();
		}
		else {
			$('#'+$(this).data('type')).hide();
		}
		
	});
	
});
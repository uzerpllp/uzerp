/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/* $Revision: 1.4 $ */

$(document).ready(function() {
	
	/* dashboard -> mydata -> index */
	
	$('#folders .heading','#dashboard-mydata-index').live('click',function() {
		
		if ($(this).hasClass('open')) {
			$('#'+$(this).data('type')).show();
		}
		else {
			$('#'+$(this).data('type')).hide();
		}
		
	});
	
	$('#new_password_id').strengthify({ zxcvbn: '/lib/js/zxcvbn.js', "drawMessage": true });
	
});
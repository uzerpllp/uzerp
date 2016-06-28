/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/* $Revision: 1.4 $ */

$(document).ready(function() {

	$('#User_password').strengthify({ zxcvbn: '/lib/js/zxcvbn.js', "drawMessage": true });

});
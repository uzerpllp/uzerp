/**
 *	uzERP admin.js
 *
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2000-2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
$(document).ready(function() {

    // Password strength meter and password length warning
	$('#User_password').strengthify({ zxcvbn: '/lib/js/zxcvbn.js', "drawMessage": true });
	$('#User_password').on('keyup',function(){
    	var pw_len = $(this).val().length;
        
        if (pw_len >= 10) {
            $("#char-count").addClass('min-length');
            $("#char-count").text('');
        } else {
            $("#char-count").removeClass('min-length');
            $("#char-count").text('Password must be 10 or more characters: ' + pw_len);
        }
    });

});

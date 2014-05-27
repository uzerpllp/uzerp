 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * cache.js
 * 
 * Version: $Revision: 1.3 $
 */

$(document).ready(function() {
	
	$('a[href*="action=flush"]').live('click',function(event) {
		
		var answer = confirm("Are you sure you want to flush the cache?" + "\r\n\r\n" + "This will destroy all caches for all instances!");
		
		if (!answer)
		{
			return false; 
		}

	});
	
});

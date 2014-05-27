/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * cashbook.js
 * 
 * $Revision: 1.2 $
 * 
 */
$(document).ready(function(){

	// Progress Bar for asset Depreciation
	
	$('a[href*="action=depreciation"]').live('click', function (event) {
		
		event.preventDefault();
		
		options = {main_url 		: $(this).attr('href')
				  ,progress_url		: "/?module=asset_register&controller=assets&action=getprogress&monitor_name=depreciation&ajax="
				  ,heading			: "Asset Depreciation"
				  ,title			: "Running Depreciation...."
				  ,success_message	: "Asset Depreciation Completed OK"
				  ,fail_message		: "Asset Depreciation Failed"
		};
		
		uz_progressbar(options);
		
	});
	
});
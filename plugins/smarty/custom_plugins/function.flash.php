<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.6 $ */

function smarty_function_flash($params, &$smarty) {
	
	$flash = Flash::Instance();
	$flash->restore();
	
	$data = array(
		'messages'	=> $flash->messages,
		'warnings'	=> $flash->warnings,
		'errors'	=> $flash->errors
	);
	
	$flash->clear();
	
	// fetch smarty plugin template
	return smarty_plugin_template($smarty, $data, 'function.flash');
	
}

// end of function.flash.php	
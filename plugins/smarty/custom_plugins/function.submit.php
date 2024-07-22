<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.6 $ */

function smarty_function_submit($params, &$smarty) {

	$data = array(
		'display_tags' => !(isset($params['tags']) && $params['tags'] == 'none')
	);

	// set the default array
	$default_params = array(
		'value'	=> 'Save',
		'name'	=> 'saveform',
		'id'	=> 'saveform'
	);

	// merge defaults into params
	$params += $default_params;

	$data['append']	= &$smarty->getTemplateVars('append');

	// merge params with the defaults
	$data += $params;

	// fetch smarty plugin template
	return smarty_plugin_template($smarty, $data, 'function.submit');

}

// end of function.submit.php
<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.4 $ */

function smarty_function_advanced_search($params, &$smarty) {
	
	$data = array(
		'action' => ''
	);
	
	$self = $smarty->getTemplateVars('self');
	
	unset($self['pid'], $self['module'], $self['modules'], $self['controller'], $self['action']);
	
	$data['additional_data'] = $self;
	
	if (isset($params['action']))
	{
		$data['action'] = $params['action'];
	}
	
	$userPreferences 		= UserPreferences::instance();
	$pdf_browser_printing	= $userPreferences->getPreferenceValue('pdf-browser-printing', 'shared');
	
	if (!empty($pdf_browser_printing) && $pdf_browser_printing == 'on')
	{
		$data['additional_data']['printaction'] = 'quick_output';
	}
	
	// fetch smarty plugin template
	return smarty_plugin_template($smarty, $data, 'function.advanced_search');

}

// end of function.advanced_search.php
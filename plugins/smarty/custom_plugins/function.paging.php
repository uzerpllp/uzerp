<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.3 $ */

function smarty_function_paging($params, &$smarty) {
	
	$data			= array();
	$link_params	= array();
	$self			= $smarty->getTemplateVars('self');
	$paging_link	= $smarty->getTemplateVars('paging_link');
	
	if (is_array($paging_link))
	{
		$link_params = $paging_link;
	}
	else
	{
		$link_params = $self;	
	}
	
	$data['paging_link'] = link_to($link_params, FALSE, FALSE);
	
	// fetch smarty plugin template
	return smarty_plugin_template($smarty, $data, 'function.paging');

}

// end of function.link_prev.php
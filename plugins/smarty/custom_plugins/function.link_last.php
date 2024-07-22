<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.6 $ */

function smarty_function_link_last($params, &$smarty)
{

    require_once $smarty->_get_plugin_filepath('function','link_to');

	$self		= $smarty->getTemplateVars('self');
	$num_pages	= $smarty->getTemplateVars('num_pages');
	$additional	= array(
		'page'	=> $num_pages,
		'value'	=> '>>'
	);
	$array		= $self+$additional;

	if (is_array($smarty->getTemplateVars('paging_link')))
	{
		$array = array('data' => $smarty->getTemplateVars('paging_link')) + $additional;
	}

	return smarty_function_link_to($array, $smarty);

}

// end of function.link_last.php
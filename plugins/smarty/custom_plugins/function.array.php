<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.5 $ */

function smarty_function_array($params, &$smarty)
{
	
	if (isset($params['name']) && isset($params['key']) && isset($params['value']))
	{
		
		$array = $smarty->getTemplateVars($params['name']);
		
		if (is_array($array))
		{
			$array[$params['key']] = $params['value'];
			$smarty->assign($params['name'], $array);
		}
		
	}
	
}

// end of function.array.php
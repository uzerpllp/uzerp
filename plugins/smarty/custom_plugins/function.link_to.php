<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.6 $ */

function smarty_function_link_to($params, &$smarty)
{

	with($params, $smarty);

	if (isset($params['data']) && is_array($params['data']))
	{
		$params = $params + $params['data'];		
		unset($params['data']);
	}

	foreach ($params as $key=>$value)
	{
		if (is_object($value))
		{
			unset($params[$key]);
		}
	}

	return link_to($params);

}

// end of function.link_to.php
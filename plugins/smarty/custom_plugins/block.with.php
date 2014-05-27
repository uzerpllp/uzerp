<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.4 $ */

function smarty_block_with($params, $content, &$smarty, &$repeat)
{
	
	if (empty($content))
	{
		
		$with = $smarty->getTemplateVars('with');
		
		foreach ($params as $key=>$val)
		{
			$with[$key] = $val;
			$smarty->assign($key, $val);
		}
		
		$smarty->assign('with', $with);
		
	}
	else
	{
		
		$return	= '';
		$with	= $smarty->getTemplateVars('with');
		
		foreach ($params as $key=>$val)
		{
			$smarty->clearAssign($key);
			unset($with[$key]);
		}
		
		$smarty->assign('with', $with);	
		
		return $return . $content;
		
	}
}

// end of block.with.php
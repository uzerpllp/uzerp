<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.4 $ */

function smarty_function_quicksearch($params, &$smarty)
{
	
	if (isset($params['grid']))
	{
		$grid		= $params['grid'];
		$fields		= $grid->getFields();
		$search		= $grid->search;
		$limit		= $grid->limit;
		$orderby	= $grid->orderby;
		$direction	= $grid->direction;
	}

	if (isset($params['field']))
	{
		$field = $params['field'];
	}
	
	$letters = array('a','b','c','d','e','f','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');

	$location	 = '/?module='.$smarty->getTemplateVars('module').'&controller='.$smarty->getTemplateVars('controller').'&action='.$smarty->getTemplateVars('action');
	$html		='<div id="quickSearch">'."\n".'<table><tr>';
	
	foreach($letters as $letter)
	{
		$html.='<td><a class="quicksearch" href="'.$location.'&search[]='.$letter.'&field[]='.$field.'">'.ucfirst($letter).'</td>';
	}

	$html.="</tr></table> \n </div>";
			
	return $html;
}

// end of function.quicksearch.php
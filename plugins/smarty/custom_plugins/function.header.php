<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.4 $ */

function smarty_function_header($params, &$smarty) {
	
	$self	= $smarty->getTemplateVars('self');
	$theme	= $smarty->getTemplateVars('theme');
	$title	= $smarty->getTemplateVars('page_title');
	
	$inflector	= new Inflector();
	$item_name	= prettify($inflector->singularize($smarty->getTemplateVars('controller')));
	
	if (empty($title) || $title === 'Index')
	{

		switch($smarty->getTemplateVars('action')) 
		{
			
			case 'view':
				$title = $item_name . ' Details';
				break;
				
			case 'edit':
				$title = 'Editing ' . $item_name . ' Details';
				break;
			
			case 'new':
				$title = 'Create new ' . $item_name;
				break;
			
			case 'index':
			default: 
				$title = $item_name;
				break;
			
	    }
	}
	
	return '<h1 class="page_title">' . $title . '</h1>';
	
}

// end of function.header.php
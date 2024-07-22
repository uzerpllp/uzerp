<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.7 $ */

function smarty_function_link_current($params, &$smarty)
{

	$array = $smarty->getTemplateVars('self');

	if (is_array($smarty->getTemplateVars('paging_link')))
	{
		$array = $smarty->getTemplateVars('paging_link');
	}

	if (isset($array['page']))
	{
		unset($array['page']);
	}

	if (isset($array['value']))
	{
		unset($array['value']);
	}

	$pid		='';
	$modules	='';
	$controller	='';
	$action		='';

	if (!empty($array['module']))
	{

		$modules	= $array['module'];
		$module		= 'module=' . $modules . '&amp;';

		unset($array['module']);

	}

	if (!empty($array['modules']))
	{

		$modules	= $array['modules'];
		$module		= '';
		$prefix		= 'module=';

		foreach($modules as $mod)
		{
			$module .= $prefix . $mod . '&amp;';
			$prefix  = 'sub' . $prefix;
		}

		unset($array['modules']);

	}

	if (!empty($array['controller']))
	{
		$controller = $array['controller'];
		unset($array['controller']);
	}

	if (!empty($array['action'])) 
	{
		$action = $array['action'];
		unset($array['action']);
	}

	if(!empty($array['pid']))
	{
		$pid = $array['pid'];
		unset($array['pid']);
	} 
	else 
	{
		$access	= AccessObject::Instance();
		$pid	= $access->getPermission($modules, $controller, $action);
	}

	$action = '/?pid=' . $pid . '&amp;' . $module . 'controller=' . $controller . '&amp;action=' . $action;

	foreach ($array as $name => $value)
	{
		$action .= '&amp;' . $name . '=' . $value;
	}

	$content = '<input type="hidden" id="paging_url" name="paging_url" value="' . $action . '">';

	if (isset($_GET['search_id']) && !isset($array['search_id']))
	{
		$content .= '<input type="hidden" name="search_id" value="' . $_GET['search_id'] . '" />';
	}

	$page_num  = $params['page'];
	$content  .= '<input type="text" name="goto_page" value="' . $page_num . '" class="paging">';

	return $content;

}

// end of function.link_current.php
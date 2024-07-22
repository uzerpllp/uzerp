<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.5 $ */

function smarty_function_page_identifier($params, &$smarty) {

	if (isset($params['module']) && !empty($params['module'])) 
	{
		$module = $params['module'];
	} 
	else
	{
		$module = trim((string) $smarty->getTemplateVars('module'),'_');
	}

	if (isset($params['controller']) && !empty($params['controller']))
	{
		$controller = $params['controller'];
	} 
	else 
	{
		$controller = trim((string) $smarty->getTemplateVars('controller'),'_');
	}

	if (isset($params['action']) && !empty($params['action']))
	{
		$action = $params['action'];
	}
	else
	{
		$action = trim(basename((string) $smarty->getTemplateVars('templateName'), ".tpl"), '_');
	}

	echo strToLower($module . '-' . $controller . '-' . $action);

}

// end of function.page_identifier.php
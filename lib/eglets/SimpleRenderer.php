<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class SimpleRenderer implements Renderer
{
	protected $cache_lifetime;

	public function render(EGlet &$eglet, &$smarty)
	{

		if ($eglet->should_render)
		{
			// clear all previous smarty vars
			// just to stop 'cross contamination' of vars

			$self	= $smarty->getTemplateVars('self');

			$action	= $smarty->getTemplateVars('action');

			$module = $smarty->getTemplateVars('module');

			$controller = $smarty->getTemplateVars('controller');

			$csrf_token = $smarty->getTemplateVars('csrf_token');

			$smarty->clearAllAssign();

			$vars = $eglet->get_vars();

			// loop through the vars array and assign them in smarty
			if (!empty($vars))
			{

				foreach ($vars as $key => $var)
				{
					$smarty->assign($key, $var);
				}

			}

			$smarty->assign('content', $eglet->getContents());
			$smarty->assign('self', $self);
			$smarty->assign('action', $action);
			$smarty->assign('csrf_token', $csrf_token);

			if (!isset($vars['module']))
			{
				$smarty->assign('module', $module);
			}

			if (!isset($vars['controller']))
			{
				$smarty->assign('controller', $controller);
			}

			$smarty->cache_lifetime = 200;

			echo $smarty->fetch($eglet->getTemplate());
		}

	}

}

// end of SimpleRenderer
<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.16 $ */

function smarty_block_form($params, $content, &$smarty, $repeat)
{

	if (!empty($content))
	{
		
		// set default output vars
		$data = array(
			'search_id'			=> FALSE,
			'submit_token_id'	=> FALSE,
			'class'				=> '',
			'content'			=> $content,
			'method'			=> 'post'
		);
		
		$modules = $smarty->getTemplateVars('modules');
		
		if (!empty($modules))
		{
			
			$module = '';
			$prefix = 'module=';
			
			foreach ($modules as $mod)
			{
				$module .= $prefix . $mod . '&amp;';
				$prefix  = 'sub' . $prefix;
			}
			
		}
		
		if (isset($params['target']))
		{
			$data['action'] = $params['target'];
		}
		else
		{
			$access	= AccessObject::Instance();
			$pid	= $access->getPermission($modules, $params['controller'], $params['action']);
			$data['action']	= '/?pid=' . $pid . '&' . $module . 'controller=' . $params['controller'] . '&amp;action=' . $params['action'];
		}
		
		if (isset($params['subfunction']))
		{
			
			$data['action'] .= '&amp;subfunction=' . $params['subfunction'];
			
			if (isset($params['subfunctionaction']))
			{
				$data['action'] .= '&amp;subfunctionaction=' . $params['subfunctionaction'];
			}
			
		}
		
		if (isset($params['id']))
		{
			$data['action'] .= '&amp;id=' . $params['id'];
		}
		
		foreach($params as $name=>$value) 
		{
			
			if ($name[0] === '_')
			{
				$data['action'] .= '&amp;' . substr($name, 1) . '=' . $value;
			}
			
		}
		
		if (isset($params['additional_data']))
		{
			foreach($params['additional_data'] as $name=>$value) 
			{
				$data['action'] .= '&amp;' . $name . '=' . $value;
			}
		}
		
		if (isset($params['class']))
		{
			$data['class'] = $params['class'];
		}
		
		$data['original_action'] = $smarty->getTemplateVars('action');
		
		if (isset($_GET['search_id']))
		{
			$data['search_id'] = $_GET['search_id'];
		}

		// there are some instances where we don't want the submit token
		if (strtoupper((string) $params['submit_token']) !== 'FALSE') 
		{
			$data['submit_token_id'] = uniqid();
			$_SESSION['submit_token'][$data['submit_token_id']] = TRUE;
		}
		
		$data['display_tags'] = (!isset($params['notags']));
		
		if (isset($params['form_id']))
		{
			$data['form_id'] = $params['form_id'];
		}
		
		// fetch smarty plugin template
		return smarty_plugin_template($smarty, $data, 'block.form');
	
	}
	
}

// end of block.form.php
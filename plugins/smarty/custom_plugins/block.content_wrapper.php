<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

function smarty_block_content_wrapper($params, $content, &$smarty, $repeat)
{
	
	/**************************************************************
	 * Content Wrapper block
	 **************************************************************
	 * The purpose of the content wrapper block is to encapsulate
	 * each page used by uzERP. Its initial purpose was to provide
	 * a simple alternative to the page identifier, however it has
	 * also been used to house the page title and flash, the reason
	 * being is that the page title and flash are content specific 
	 * things, not page specific. When ajaxing content the page 
	 * title and flash are not dragged along with it.
	 * 
	 * $Revision: 1.6 $
	 * 
	 **************************************************************/
	
	// set initial vars
	$data = array(
		'attrs'		=> array(),
		'title'		=> '',
		'flash'		=> FALSE,
		'content'	=> $content
	);
		
	if (!empty($content))
	{

		/*
		 * Page Identifier
		 ***********************************************************/		
				
		if (isset($params['module']) && !empty($params['module']))
		{
			$module = $params['module'];
		}
		else
		{
			$module = trim($smarty->getTemplateVars('module'),'_');
		}
			
		if (isset($params['controller']) && !empty($params['controller']))
		{
			$controller = $params['controller'];
		}
		else
		{
			$controller = trim($smarty->getTemplateVars('controller'),'_');
		}
			
		if (isset($params['action']) && !empty($params['action']))
		{
			$action = $params['action'];
		}
		else
		{
			$action = trim(basename($smarty->getTemplateVars('templateName'), ".tpl"),'_');
		}
			
		if (!isset($params['page_identifier']) || $params['page_identifier'] !== FALSE)
		{
			$data['attrs']['id'] = strToLower($module . '-' . $controller . '-' . $action);
		}
		
		$data['attrs']['data-module']		= $module;
		$data['attrs']['data-controller']	= $controller;
		$data['attrs']['data-action']		= $action;
		
		$self = $smarty->getTemplateVars('self');
		
		unset($self['modules']);
		unset($self['module']);
		unset($self['controller']);
		unset($self['action']);
		
		// This is used by popup dialog forms to refresh the calling form/page
		foreach ($self as $key=>$value)
		{
			if ($key != '_')
			{
				$data['attrs']['data-'.$key] = $value;
			}
		}
		
		/*
		 * Class
		 ***********************************************************/
		
		$data['attrs']['class'][] = 'content_wrapper';
		
		if (isset($params['class']))
		{
			$data['attrs']['class'][] = $params['class'];
		}
		
		
		/*
		 * Page Title
		 ***********************************************************/

		if (!isset($params['title']) || $params['title'] !== FALSE)
		{
			
			if (isset($params['title'])) 
			{
				$data['title'] = prettify($params['title']);
			} 
			else
			{
				$data['title'] = prettify($smarty->getTemplateVars('page_title'));
			}
			
		}

		
		/*
		 * Flash
		 ***********************************************************/
		
		if (!isset($params['flash']) || $params['flash'] !== FALSE)
		{
			$data['flash'] = TRUE;
		}
		
		
		/*
		 * Generate and output final HTML
		 ***********************************************************/
		
		// convert attrs array to a string
		$data['attrs'] = build_attribute_string($data['attrs']);
		
		// fetch smarty plugin template
		return smarty_plugin_template($smarty, $data, 'block.content_wrapper');
		
	}
	
}

// end of block.content_wrapper.php
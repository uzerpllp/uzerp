<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.10 $ */

function smarty_block_heading_cell($params, $content, &$smarty, $repeat)
{
	
	if (!empty($content))
	{
		
		// why isn't this being done way sooner?!
		if (substr($params['field'], -2) == 'id')
		{
			return '';
		}
		
		// attribute variables
		$attrs = array();
		
		// merge data attributes with attributes array
		$attrs = array_merge($attrs, build_data_attributes($params));
		
		if ($smarty->getTemplateVars('no_ordering') !== TRUE)
		{

			$link			= $smarty->getTemplateVars('self');
			$link['value']	= prettify($content);
			$action			= $smarty->getTemplateVars('action');
			
			if (empty($action)) 
			{
				$action = 'index';
			}
			
			$link['action'] = $action;
			
			if (isset($params['field']))
			{
				$link['orderby']		= $params['field'];
				$attrs['data-column']	= $params['field'];
			}
			
			$content = link_to($link, $data=true); // WTF
			
		}
		else 
		{
			$content = prettify($content);
		}
		
		$model = $params['model'];
		
		if ($model && $model->getField($params['field'])->type == 'numeric' || $params['field'] == 'right')
		{
			$attrs['class'][] = 'right';
		}
		
		if (isset($params['class'])) 
		{
			$attrs['class'][] = $params['class'];
		}
		
		// build the attribute string based on the attribute array
		$attrs = build_attribute_string($attrs);
		
		// return the built string
		return '<th ' . $attrs . '>' . $content . '</th>' . "\n";

	}
}

// end of block.heading_cell.php
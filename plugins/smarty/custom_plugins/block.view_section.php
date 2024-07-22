<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.8 $ */

function smarty_block_view_section($params, $content, &$smarty, $repeat) 
{

 	if (!empty($content))
 	{

		$attrs = array();

		$attrs['class'][] = 'heading';

		if (isset($params['class'])) 
		{
			$attrs['class'][] = $params['class'];
		}

		if (isset($params['dont_prettify']))
		{
			$heading = $params['heading'];
		} 
		else
		{
			$heading = prettify($params['heading']);
		}

		if ($heading === 'EGS_HIDDEN_SECTION')
		{
			return '';
		}

		// convert attrs array to a string

		if (isset($params['expand']))
		{
			$attrs['class'][]	= 'expand';
			$attrs['class'][]	= $params['expand'];
			$data['expand']		= ($params['expand']=='closed')?'hidden':'';
		} 

		$data['attrs'] = build_attribute_string($attrs);
		$data['heading'] = $heading;
		$data['content'] = $content;

		// fetch smarty plugin template
		return smarty_plugin_template($smarty, $data, 'block.view_section');

 	}

}

// end of block.view_section.php
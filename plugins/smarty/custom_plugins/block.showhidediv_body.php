<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.3 $ */

function smarty_block_showhidediv_body($params, $content, &$smarty)
{

// Smarty Plug In to display collapsible structure
//
// Parameters - 
//		id		- id to uniquely identify the div to collapse/expand
//		hide	- false(default) shows structure expanded
	
	// Opening Tag
	if (empty($content)) 
	{
		return;
	}
	
	$data = array(
		'content' => $content, 
		'ul' => array(
			'attrs' => array()
		)
	);

	if (isset($params['hide']) && ($params['hide']))
	{
		$data['div']['attrs']['style'][] = 'display:none;';
	}
	
	if (isset($params['class_name'])) 
	{
		$data['ul']['attrs']['class'][]		= $params['class_name'];
		$data['div']['attrs']['class'][]	= $params['class_name'];
	}
	
	$data['div']['attrs']['id'] = $params['id'];
	
	// convert attrs array to a string
	$data['ul']['attrs']	= build_attribute_string($data['ul']['attrs']);
	$data['div']['attrs']	= build_attribute_string($data['div']['attrs']);
	
	// fetch smarty plugin template
	return smarty_plugin_template($smarty, $data, 'block.showhidediv_body');
	
}

// end of block.showhidediv_body.php
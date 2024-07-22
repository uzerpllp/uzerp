<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.4 $ */

function smarty_block_showhidediv($params, $content, &$smarty)
{

// Smarty Plug In to display collapsible structure
//
// Parameters - 
//		images	- array of images to indicate closed/open focus/nofocus notexpandable
//		id		- id to uniquely identify the div to collapse/expand
//		name	- Title of the controlling div
//		class_name	- css class_name for formatting the structure
//		hide	- false(default) shows structure expanded

	// opening Tag
	if (empty($content))
	{
		return;
	}

	$data = array(
		'content' => $content,
		'div' => array(
			'attrs' => array()
		),
		'img' => array(
			'attrs' => array()
		)
	);

	// Closing tag so wrap content within 
	if (isset($params['images']))
	{
		$images = $params['images'];
	} 
	else 
	{
		$images = array(
			'open_nofocus'		=> '/assets/graphics/menu_open_nofocus.png',
			'open_focus'		=> '/assets/graphics/menu_open_focus.png',
			'closed_nofocus'	=> '/assets/graphics/menu_closed_nofocus.png',
			'closed_focus'		=> '/assets/graphics/menu_closed_focus.png',
			'noexpand'		=> '/assets/graphics/menu_noexpand.png'
		);
	}

	if (isset($params['hide']) && ($params['hide']))
	{
		$data['image']['attrs']['src']		= $images['closed_nofocus'];
		$data['image']['attrs']['class'][]	= 'eglet_closed';
	} 
	else
	{
		$data['image']['attrs']['src']		= $images['open_nofocus'];
		$data['image']['attrs']['class'][]	= 'eglet_opened';
	}


	if (isset($params['class_name']))
	{
		$data['div']['attrs']['class'][]	= $params['class_name'];
	}

	$data['image']['attrs']['id'] = 'image_' . $params['id'];

	// convert attrs array to a string
	$data['image']['attrs']	= build_attribute_string($data['image']['attrs']);
	$data['div']['attrs']	= build_attribute_string($data['div']['attrs']);

	// fetch smarty plugin template
	return smarty_plugin_template($smarty, $data, 'block.showhidediv');

}

// end of block.showhidediv.php

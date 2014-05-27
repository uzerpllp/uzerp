<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
	
/* $Revision: 1.5 $ */

function smarty_function_checkbox_tree($params, &$smarty)
{

	$html = '';	
		
	// Set the variables we need 
	$checked	= $params['checked'];
	$items		= $params['items'];
	$admins		= $params['admins'];
	
	$html .= '<ul id="permission_tree" class="permissions">';
	
	if (!isset($items) || empty($items))
	{
		return FALSE;
	}
	
	foreach ($items as $item)
	{
		$html .= mktree($item, $checked, $admins);
	}
	
	$html .= '</ul>';	
	
	return $html;
}

function mktree($items, $checked=array(), $admins, $setall = FALSE, $adchild = FALSE)
{

	$html	= '';
	$mod	= '';
	
	if (isset($checked[$items['id']]) || $setall)
	{
		$mod = ' CHECKED ';
	}
	
	/****
	 * If the item has children, we need to create the checkboxes for them by calling the mktree function on each of them
         * If not, just return a single checkbox.
	 */
	
	if (!empty($items['children']))
	{
		
		$html .= '<li class="' . $items['type'] . '">';
		
		if (trim($items['type']) == 'm' && !$adchild)
		{
			
			if (isset($admins[$items['permission']]))
			{
				$adcheck = 'checked';
			}

			$html .= '<input class="checkbox" type=checkbox name="admin[' . $items['permission'] . ']" value="admin' . $items['id'] . '" ' . $adcheck . ' /> ';
			
		}
		
		$html .= '<input class="checkbox" type=checkbox name="permission[' . $items['id'] . ']" value="' . $items['id'] . '"' . $mod . ' /> ' . prettify($items['permission']) . ": " . $items['description'] . '<ul class="permission">';
		
		foreach ($items['children'] as $child)
		{
			$html .= mktree($child, $checked, $admins, $setall, TRUE);
		}	
		
		$html .= '</ul></li>';
		
	}
	else
	{

		$html .= '<li class="' . $items['type'] . '">';
		
		if (trim($items['type']) == 'm' && !$adchild)
		{
			
			if (isset($admins[$items['permission']])) {
				$adcheck = 'checked';
			}
			
			$html .= '<input class="checkbox" type=checkbox name="admin[' . $items['permission'] . ']" value="admin' . $items['id'] . '" ' . $adcheck . ' /> ';
			
		}
		
		$html .= '<input class="checkbox"  type=checkbox name="permission[' . $items['id'] . ']" value="' . $items['id'] . '"' . $mod . ' /> ' . prettify($items['permission']) . ': ' . $items['description'] . '</li>';

	}

	return $html;
	
}

// end of function.checkbox_tree.php
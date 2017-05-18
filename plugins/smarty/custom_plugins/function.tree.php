 <?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
	
/* $Revision: 1.5 $ */

function smarty_function_tree($params, &$smarty)
{
	$tree = $params['tree'];
	return getTree($tree,$smarty);
}

function getTree($tree, $smarty)
{
	
	$excludeTags	= array('shopsection');
	$output			= '<ul id="tree_top">';		
		
	if ($tree instanceof DOMDocument)
	{
		$output .= '<li class="new">';
		$output	.= '<a href="/?module=system_admin&controller=permissions&action=new&parent_id=">[New Permission]</a>';
		$output .= '</li>';
	}
	
	if ($tree->hasChildNodes())
	{
		
		$nodes = $tree->childNodes;
		
		foreach ($nodes as $node)
		{
			
			if (!($node instanceof DOMText) && (!in_array($node->tagName, $excludeTags)))
			{
				
				$id = $node->getAttribute('id');
				$permission = new Permission();
				$permission->load($id);
				$output .= '<li class="drag '.strtolower($permission->getFormatted('type')).'" id="treeitem_'.$node->tagName.'-'.$id.'-'.$permission->type.'-'.$permission->parent_id.'">';

				if ($permission->display == 't')
				{
					$output .= '<img src="/assets/graphics/true.png" />';
				} else {
					$output .= '<img src="/assets/graphics/false.png" />';
				}
				
				$output .= $permission->permission . ' - ' . $permission->title;
				$output .= '<a href="/?module=system_admin&controller=permissions&action=new&parent_id=' . $id . '">[New]</a>&nbsp;-&nbsp;';
				$output .= '<a href="/?module=system_admin&controller=permissions&action=view&id=' . $id . '">[View]</a>&nbsp;-&nbsp;';
				$output .= '<a href="/?module=system_admin&controller=permissions&action=edit&id=' . $id . '">[Edit]</a>&nbsp;-&nbsp;';
				$output .= '<a href="/?module=system_admin&controller=permissions&action=delete&id=' . $id . '">[Delete]</a>';
				$output .= '<span class="tree_info"> (Description: ' . $permission->description . ')</span>';
				
			}
			
			$output .= getTree($node, $smarty);
			$output .= '</li>';
				
		}
	}
	
	$output .= '</ul>';
	
	return $output;
	
}
	
// end of function.tree.php

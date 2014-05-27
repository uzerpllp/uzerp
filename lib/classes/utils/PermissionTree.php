<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PermissionTree
{

	protected $version='$Revision: 1.2 $';
	
	/***
	 * A function to make a multidimentional array from an array of strings
     * 
     * @param	array	node	an array representing a node of a tree it may or may not have items currently
 	 * @param	array	element	an array of elements containig the children
	 * @id		integer	id		an integer which will be used as the id for the final element in the element array
	 */

	public  function makeTree($node, $element=array(), $extra=null)
	{
		if(count($element)> 0)
		{
			$current = array_shift($element);
			if(count($element) > 0)
			{
				if(isset($node[$current]))
				{
					$node[$current]['children'] = $this->makeTree($node[$current]['children'], $element, $extra);
					return $node;
				}
				else
				{
					$node[$current]['name']=$current;
					$node[$current]['children']=$this->makeTree(array(), $element, $extra);
					return $node;
				}
			}
			else
			{
				if($extra['title'] != '')
				{
					$title = $extra['title'];
				}
				else $title = $current;

				$display = false;
				if($extra['display'] === 't')
				{
					$display = true;
				}
				
				if(isset($node[$current]))
				{
						$node[$current] = array('name'=>$current, 'title'=>$title, 'id'=>$extra['id'], 'description'=>$extra['description'], 'display'=>$display,'position'=>$extra['position'] ,'type'=>$extra['type'] ,'children'=>$node[$current]['children']);
				}
				else
				{
					$node[$current] = array('name'=>$current, 'title'=>$title, 'id'=>$extra['id'], 'description'=>$extra['description'], 'display'=>$display,'position'=>$extra['position'] ,'type'=>$extra['type'] ,'children'=>array());
				}
				return $node;
			} 
		}

	}
	
	public static function compare($x, $y) {
		if(!isset($x['position']))
			return 1;
		if(!isset($y['position']))
			return -1;
		if ( $x['position'] == $y['position'] )
			return 0;
		else if ( $x['position'] < $y['position'] )
			return -1;
		else
			return 1;
	}
}
?>

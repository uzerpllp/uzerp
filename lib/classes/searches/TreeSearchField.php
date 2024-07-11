<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TreeSearchField extends SearchField {

	protected $version		='$Revision: 1.10 $';
	protected $options		= array();
	protected $breadrcumbs	= array();
	
	public function toHTML()
	{
		
		$html .= '<ul class="uz_breadcrumbs">';
			
		if (isset($this->breadrcrumbs) && count($this->breadcrumbs) > 0)
		{
			
			$system	= system::Instance();
			$uri	= 'pid=' . $system->pid;
			
			foreach ($system->modules as $mkey => $mvalue)
			{
				$uri .= '&' . $mkey . '=' . $mvalue;
			}
			
			$uri .= '&controller=' . str_replace('controller', '', strtolower(get_class($system->controller))) . '&action=' . $system->action;

			foreach ($this->breadcrumbs as $value)
			{
				
				$params = '';
				
				foreach ($value['data'] as $pkey => $pvalue)
				{
					$params .= '&' . $pkey . '=' . $pvalue;
				}
				
				$breadcrumbs[] = '<strong>' . $value['name'] . '</strong> <a href="/?' . $uri . $params . '" title="Click to see other items on this level">(Choose another ' . $value['descriptor'] . ')</a>';
				
			}
			
			// breadcrumbs are construct backwards, lets sort them out (pun intended)
			sort($breadcrumbs, SORT_NUMERIC);
			
			$html .= '<li>Current Selection';
			
			foreach ($breadcrumbs as $key => $value)
			{
				$html .= '<li> &#187; ' . $value . '</li>';
			}
			
		}
		else
		{
			$html .= '<li>Current Selection</li>';
			$html .= '<li>&#187; None</li>';
		}
		
		$html .= '<li><select id="tree_' . $this->fieldname . '" class="tree_search" name="Search[' . $this->fieldname . ']">';
		
		foreach ($this->options as $val => $opt)
		{
			
			$selected = '';
			
			if ($this->value == $val || (is_null($this->value) && $this->default == $val))
			{
				$selected = 'selected="selected"';
			}
			
			$html .= '<option value="' . $val . '" ' . $selected . '>' . uzh(prettify($opt)) . '</option>';
			
		} 
		
		$html .='</select></li></ul></li>';
		
		return $this->labelHTML() . $html;
		
	}

	public function toConstraint()
	{
		
		$value = array();
		
		if ($this->value_set)
		{
			$value = $this->value;
		}
		else
		{
			$value = $this->default;
		}
		
		if (!empty($value))
		{
			$c = new Constraint($this->fieldname, '=', $value);
			return $c;
		}
		
		return false;
		
	}
	
	public function setOptions($options)
	{
		$this->options = $options;
	}
	
	public function setBreadcrumbs($dataobject = '', $parent = '', $value = '', $name = '', $descriptor = '', $data = array())
	{

		// load the current item
		if ($dataobject instanceof DataObject)
		{
			$do = clone $dataobject;
		}
		else
		{
			$do = new $dataobject;
		}
		
		$do->load($value);
		
		$parent_id = $do->$parent;
		
		if (is_null($do->$parent))
		{
			$params = $data;
		}
		else
		{
			$params=array_merge(array($parent => $do->$parent), $data);
		}
		
		$breadcrumbs[] = array(
			'name'			=> $do->$name,
			'descriptor'	=> $do->$descriptor,
			'data'			=> $params
		);
		
		if (empty($descriptor))
		{
			$descriptor = $do->identifierField;
		}
		
		while (!empty($parent_id))
		{
			
			if ($dataobject instanceof DataObject)
			{
				$do = clone $dataobject;
			}
			else
			{
				$do = new $dataobject;
			}
			
			$do->load($parent_id);
			
			if (is_null($do->$parent))
			{
				$params = $data;
			}
			else
			{
				$params = array_merge(array($parent => $do->$parent), $data);
			}
			
			$breadcrumbs[] = array(
				'name'			=> $do->$name,
				'descriptor'	=> $do->$descriptor,
				'data'			=> $params
			);
			
			$parent_id = $do->$parent;
			
		}
		
		$this->breadcrumbs = $breadcrumbs;
	
	}

	/**
	 * @param void
	 * @return string
	 *
	 * Returns the HTML string containing the field's <label> tag.
	 * NOTE: at some point, this might end up being called from BaseSearch's toHTML to allow for containing elements
	 */
	protected function labelHTML()
	{
		$html = '<li><label for="search_' . str_replace('/', '_', $this->fieldname) . '">' . prettify($this->label) . '</label>';
		return $html;
	}	

	public function getCurrentValue()
	{
// The values are in $this->breadcrumbs which is an array of array(name, description)

		if (empty($this->breadcrumbs))
		{
			return 'None';
		}
		
		$value = array();
		foreach ($this->breadcrumbs as $values)
		{
			$value[] = $values['name'];
		}
		return implode('/', array_reverse($value));		
		
	}
	
}

// end of TreeSearchField.php 

<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MultiSelectSearchField extends SearchField {

	protected $version = '$Revision: 1.17 $';
	protected $options = array();

	public function toHTML()
	{
		
		$html .= '<select multiple size="5" id="search_' . $this->fieldname . '" class="multiselect" name="Search[' . $this->fieldname . '][]">';
		$value = array();
		
		if (count($this->value) > 0)
		{
			$value = $this->value;
		}
		elseif (count($this->default) > 0)
		{
			$value = $this->default;
		}
		
		foreach ($this->options as $val => $opt)
		{
			
			$selected = '';
			
			if ((count($value) > 0) && (in_array($val,$value)))
			{
				$selected = 'selected="selected"';
			}
			
			$html .= '<option value="' . $val . '" ' . $selected . '>' . h(prettify($opt)) . '</option>';
			
		}
		
		$html .= '</select></li>';
		
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
		
		if (is_array($value) && (count($value) > 0 && current($value) !== '' && current($value) !== 0 && current($value) !== '0'))
		{
			
			switch (strtolower($this->type))
			{
			
				case 'multi_select':

					// Need to loop around values to wrap them in ' quotes
					$db = DB::Instance();
					
				    foreach ($value as $key => $item)
				    {
				    	$value[$key] = $db->qstr($item);
				    }
				    
				    $output	= implode(",", $value);
					$c		= new Constraint($this->fieldname, 'in', '('.$output.')');
					
					return $c;
					
			}
			
		}
		
		return false;
		
	}
	
	public function setOptions($options)
	{
		$this->options = $options;
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
	
		$values	= $this->value;
		$parts	= array();
		
		if (is_array($values) && !empty($values))
		{
			
			$parts_count = count($values);
			
			return $parts_count . ' option' . get_plural_string($parts_count) . ' selected';
						
		}
		
		return FALSE;
		
	}
	
}

// end of MultiSearchField.php
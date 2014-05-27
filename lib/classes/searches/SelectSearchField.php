<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SelectSearchField extends SearchField {

	protected $version = '$Revision: 1.14 $';
	protected $options = array();
	
	public function toHTML()
	{
		
		// change to facilitate lists

		$id='search_'.$this->fieldname;
		$name='Search['.$this->fieldname.']';
		
		if (count($this->options)>get_config('AUTOCOMPLETE_SELECT_LIMIT'))
		{
			$html='';
			$selected=$this->value;
			if (empty($selected)) {
				$selected=$this->default;
			}
			$html.='<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$selected.'" />';
			$text_value=isset($this->options[$selected])?$this->options[$selected]:'';
			$html.='<input alt="Autocomplete enabled" type="text" id="'.$id.'_text" value="'.$text_value.'" class="uz-autocomplete  ui-autocomplete-input icon slim" data-id="'.$id.'" data-action="array"  />';
			$html.='<script type="text/javascript">'
					.'var '.$id.'='.json_encode(dataObject::toJSONArray($this->options))
					.'</script>';
		} else {
			
			$html = '<select id="'.$id.'" name="'.$name.'">';
		
			foreach ($this->options as $val => $opt)
			{
			
				$selected = '';
				
				if (($this->value === "$val") || (is_null($this->value) && $this->default === "$val"))
				{
					$selected = 'selected="selected"';
				}
			
				$html .= '<option value="' . $val . '" ' . $selected . '>' . h(prettify($opt)) . '</option>';

			}
				
			$html .= '</select></li>';
	
		}
		
		
		return $this->labelHTML() . $html;
		
	}

	public function toConstraint()
	{
		
		$value = '';
		
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
			
			switch (strtolower($this->type))
			{
				
				case 'select':
					$c = new Constraint($this->fieldname, '=', $value);
					return $c;
					
				case 'null':
					$c = new Constraint($this->fieldname, 'IS', strtoupper($value));
					return $c;
					
			}
			
		}
		
		return FALSE;
		
	}
	
	public function setOptions($options)
	{
		$this->options = $options;
	}
	
	public function isValid($val, &$errors)
	{
		
		if (!isset($this->options[$val]))
		{
			$errors[] = 'Invalid value chosen for ' . $this->fieldname;
			return FALSE;
		}
		
		return TRUE;
		
	}
	
	public function getCurrentValue()
	{
		
		// if both the default and value are empty we're not interested
		// we should also test this against other values, such as 0, 'all'
		
		if (empty($this->default) && empty($this->value))
		{
			return FALSE;
		}
		
		// however if a value is set we can go and fetch it...
		if (isset($this->options[$this->value]))
		{
			return $this->options[$this->value];
		}
		
		// failing that is a default set?
		if (isset($this->options[$this->default]))
		{
			return $this->options[$this->default];
		}
		
		return FALSE;
		
	}
	
}

// end of SelectSearchField.php
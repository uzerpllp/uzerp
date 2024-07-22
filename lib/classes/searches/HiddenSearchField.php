<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/**
 * Used for representing SearchFields that accept text from the user
 * The different types that can be set determine the placement of wildcards (%s) within the comparison
 */

class HiddenSearchField extends SearchField {

	protected $value	= '';
	protected $constraint;
	
	public function toHTML()
	{
		
		if (empty($this->value))
		{
			$this->value = $this->default;
		}
		
		$html = '<input type="hidden" id="search_' . $this->fieldname . '" name="Search[' . $this->fieldname . ']" value="' . uzh(stripslashes((string) $this->value)) . '" />';
		return $html;
		
	}
	
	public function toConstraint()
	{
		$c		= FALSE;
		$value	= '';
		
		if ($this->value_set)
		{
			$value = $this->value;
		}
		else
		{
			$value = $this->default;
		}
		
		if (isset($this->constraint))
		{
			$c = $this->constraint;
		}
		elseif((!empty($value) && !is_null($value)) || $value === 0)
		{
			$c = new Constraint($this->fieldname, '=', $value);
		}
		
		return $c;
		
	}
	
	public function setConstraint($constraint)
	{
		$this->constraint = $constraint;
	}
	
}

// end of HiddenSearchField.php

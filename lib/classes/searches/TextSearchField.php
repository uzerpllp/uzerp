<?php
/**
 * Used for representing SearchFields that accept text from the user
 * The different types that can be set determine the placement of wildcards (%s) within the comparison
 */

class TextSearchField extends SearchField {

	protected $value	= '';
	
	public function toHTML()
	{
		
		$value = $this->value;
		
		if (empty($this->value))
		{
			$value = $this->default;
		}
		
		// change to facilitate list
		$html = '<input type="text" id="search_' . $this->fieldname . '" name="Search[' . $this->fieldname . ']" value="' . uzh(stripslashes($value)) . '" /></li>';
		
		return $this->labelHTML() . $html;
		
	}
	
	public function toConstraint()
	{
		
		$c		= false;
		$value	= '';
		
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
			// Ensure input value is properly escaped
			$db = DB::Instance();
			$value = $db->qStr($value);

			switch($this->type)
			{
				
				case 'contains':
					$c = new Constraint($this->fieldname, ' ILIKE ', '%' . $value . '%');
					break;
					
				case 'is':
				case 'numerically_equal':
					$c = new Constraint($this->fieldname, '=', $value);
					break;
					
				case 'begins':
					$c = new Constraint($this->fieldname, ' ILIKE ', $value . '%');
					break;
					
				default:
					throw new Exception('Other TextSearchField types not yet implemented!');
				
			}
			
		}
		
		return $c;
		
	}
	
	public function getCurrentValue()
	{
	
		$value	= $this->value;
		$type	= '';
		
		if (empty($value))
		{
			return FALSE;
		}
		
		switch($this->type)
		{
			
			case 'contains':
				$type = 'Contains';
				break;
				
			case 'begins':
				$type = 'Begins with';
				break;
				
		}
		
		return $type . ' \'' . $value . '\'';
		
	}
	
}

// end of TextSearchField.php

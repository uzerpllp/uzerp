<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/**
 * Used for representing SearchFields that accept text from the user
 * The different types that can be set determine the placement of wildcards (%s) within the comparison
 * uses the same toHTML as TextSearchField
 */

class NumericSearchField extends TextSearchField
{

	protected $version	='$Revision: 1.8 $';
	private $ops		= array(
		'greater'			=> '>',
		'greater_or_equal'	=> '>=',
		'less'				=> '<',
		'less_or_equal'		=> '<=',
		'equal'				=> '=',
		'not_equal'			=> '!='
	);
	
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
		
		if ($value !== '' && !is_null($value))
		{
			
			switch($this->type)
			{
				
				case 'greater':
				case 'greater_or_equal':
				case 'less':
				case 'less_or_equal':
				case 'equal':
				case 'not_equal':
					$c = new Constraint($this->fieldname,$this->ops[$this->type],$value);
					break;
					
				default:
					throw new Exception('Other NumericSearchField types not implemented!');		
								
			}
			
		}
		
		return $c;
		
	}
	
	public function isValid($value, &$errors = [])
	{
		
		if (!empty($value) && !is_numeric($value))
		{
			$errors[] = prettify($this->label) . ' needs to be numeric';
			return FALSE;
		}
		
		return TRUE;
		
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
			
			case 'greater':
				$type = 'Greater than';
				break;
				
			case 'less':
				$type = 'Less then';
				break;
				
		}
		
		return $type . ' ' . $value;
		
	}
	
}

// end of NumericSearchField.php
<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ValueValidator implements FieldValidation {

	protected $version = '$Revision: 1.3 $';
	
	/**
	 * The minimum value
	 *
	 * @var Int
	 */
	protected $value;
	
	private $message_stub = '%s must be %s %s';

	private $types = array('<'	=> 'less than'
						  ,'<='	=> 'less than or equal to'
						  ,'='	=> 'equal to'
						  ,'!='	=> 'not equal to'
						  ,'<>'	=> 'not equal to'
						  ,'>='	=> 'equal to or greater than'
						  ,'>'	=> 'greater than');
	
	private $type;
	
	/**
	 * Set the minimum value
	 *
	 * @param Int $value
	 */
	function __construct($value, $type)
	{
		$this->value = $value;
		
		if (key_exists($type, $this->types))
		{
			$this->type = $type;
		}
		
	}
	
	function test(DataField $field, Array &$errors = array())
	{

		switch ($this->type)
		{
			case '<':
				if ($field->value < $this->value)
				{
					return $field->value;
				}
				break;
			case '<=':
				if ($field->value <= $this->value)
				{
					return $field->value;
				}
				break;
			case '=':
				if ($field->value == $this->value)
				{
					return $field->value;
				}
				break;
			case '<>':
			case '!=':
				if ($field->value != $this->value)
				{
					return $field->value;
				}
				break;
			case '>=':
				if ($field->value >= $this->value)
				{
					return $field->value;
				}
				break;
			case '>':
				if ($field->value > $this->value)
				{
					return $field->value;
				}
				break;
				
		}
		
		$errors[$field->name] = sprintf($this->message_stub, $field->tag, $this->types[$this->type], $this->value);

				return FALSE;
		
	}
	
}

// end of ValueValidator.php

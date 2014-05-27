<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Constraint {

	protected $version='$Revision: 1.4 $';
	
	/**
	 * The name of the field the condition involves
	 * @access private
	 * @var String $fieldname 
	 */
	private $fieldname;
	
	/**
	 * The operator used in the condition
	 * @access private
	 * @var String $operator
	 */
	private $operator;
	
	/**
	 * The value the fieldname is compared to
	 * @access private
	 * @var mixed $value
	 */
	private $value;
	
	/**
	 * A constant for date-comparisons involving 'today'
	 */
	const TODAY = "'today'::date";
	
	/**
	 * A constant for date-comparisons involving 'tomorrow'
	 */
	const TOMORROW = "'tomorrow'::date";
	
	/**
	 * Represent a constraint given fieldname,operator,value
	 * 
	 * @param String $fieldname
	 * @param String $operator
	 * @param Mixed $value
	 */
	function __construct($fieldname, $operator, $value)
	{
		
		$this->fieldname	= $fieldname;
		$this->value		= $value;
		
		if ($this->value === TRUE)
		{
			$this->value = 'true';
		}
		
		if ($this->value === FALSE) {
			$this->value = 'false';
		}
		
		if ($value === 'NULL' && $operator == '=')
		{
			$operator = ' IS ';
		}
		
		$this->operator = $operator;
		
	}

	/**
	 * Accessor for fieldname,value,operator
	 * @param String $var
	 * @magic
	 */
	function __get($var)
	{
		return $this->$var;
	}

	/**
	 * Returns the constraint as a string suitable for use in a query
	 * 
	 * Handles escaping of values and spacing around operators
	 * @param String [$table_prefix]
	 */
	function __toString()
	{
		
		if (func_num_args() > 0)
		{
			$table_prefix = func_get_arg(0);
		} 
		else
		{
			$table_prefix = '';
		}
		
		if (!empty($table_prefix))
		{
			$table_prefix = $table_prefix . '.';
		}
		else
		{
			$table_prefix = '';
		}
		
		$db		 = DB::Instance();
		$string	 = '';
		$string .= $table_prefix . $this->fieldname . ' ';
		
		switch($this->operator)
		{
			
			case 'LIKE':	//fall through
			case 'ILIKE':	// ""
			case 'IS':		// ""
			case 'IS NOT':	// ""
				$string.=' '.$this->operator.' ';
				break;	
				
			default:
				$string.=$this->operator.' ';
				
		}
		
		switch($this->value)
		{
			
			case 'NULL':
			case 'NOT NULL':
			case 'false':
			case 'true':
			case self::TODAY:
			case self::TOMORROW;
				$string.=$this->value;
				break;
			default:
				if(substr($this->value,0,1) == '('
				|| $this->operator=='between') {
					$string.=$this->value;
					break;
				}
				$string.=$db->qstr($this->value);
				
		}
		
		return $string;
		
	}
	
}

// end of Constraint.php
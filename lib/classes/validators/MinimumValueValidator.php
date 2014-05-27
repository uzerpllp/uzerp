<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MinimumValueValidator implements FieldValidation {

	protected $version='$Revision: 1.1 $';
	
	/**
	 * The minimum value
	 *
	 * @var Int
	 */
	protected $value;
	
	private $message_stub='%s must be equal to or greater than %s';

	/**
	 * Set the minimum value
	 *
	 * @param Int $value
	 */
	function __construct($value) {
		$this->value = $value;
	}
	
	function test(DataField $field,Array &$errors=array()) {
		if($field->value>=$this->value)
			return $field->value;

		$errors[$field->name]=sprintf($this->message_stub,$field->tag,$this->value);
		return false;
	}
}
?>

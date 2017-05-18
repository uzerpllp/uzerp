<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class NumericRangeValidator implements FieldValidation {

	/**
	 * The lower end of the range
	 *
	 * @var Int
	 */
	protected $from;
	
	/**
	 * The Upper end of the range
	 * 
	 * @var Int
	 */
	protected $to;
	
	/**
	 * Set the limits of the range, these are inclusive
	 *
	 * @param Int $from
	 * @param Int $to
	 */
	function __construct($from, $to) {
		$this->from = $from;
		$this->to = $to;
	}
	
	/**
	 * @see FieldValidation::test()
	 *
	 * @param DataField $field
	 * @param array $errors
	 * @return mixed
	 */
	public function test(DataField $field,Array &$errors=array()) {
		$value = $field->value;
		if(empty($value)) {
			return $value;
		}
		if($value >= $this->from && $value <= $this->to) {
			return $value;
		}
		$errors[$field->name] = sprintf('The value for %s must be between %s and %s',$field->tag,$this->from,$this->to);
		return false;
	}
	
}
?>
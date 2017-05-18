<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class NumericValidator implements FieldValidation {
	private $message_stub='%s must contain a numeric value';

	function test(DataField $field,Array &$errors=array()) {
		if(is_numeric($field->value) || ($field->value == ''))
			return $field->value;

		$errors[$field->name]=sprintf($this->message_stub,$field->tag);
		return false;
	}
}
?>

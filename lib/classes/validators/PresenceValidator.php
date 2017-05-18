<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PresenceValidator implements FieldValidation {
	private $message_stub='%s is a compulsory field, you must enter a value';
	function test(DataField $field, Array &$errors=array()) {
		$value = $field->finalvalue;
		if(empty($value)&&$value!==0&&$value!=='0') {
			if (!$field->isHandled) {
				$message=sprintf($this->message_stub,$field->tag);
				$errors[$field->name]=$message;
				return false;
			}
		}
		return $value;
	}
}
?>

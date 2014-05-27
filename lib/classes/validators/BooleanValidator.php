<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/**
 * Doesn't validate as such, but coerces values into true booleans
 */
class BooleanValidator implements FieldValidation {

	/**
	 * Ensures that the returned value is a boolean.
	 * Doesn't provide error messages, as never returns false
	 */
	public function test(DataField $field,Array &$errors=array()) {
		if($field->value===''||$field->value===false||$field->value==='false'||$field->value==='off'||$field->value==='f') {
			return 'false';
		}
		return 'true';
	}
}
?>

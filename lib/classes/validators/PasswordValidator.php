<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PasswordValidator implements FieldValidation {

	function test(DataField $field, Array &$errors=array()) {
		return md5($field->value);
	}
}
?>

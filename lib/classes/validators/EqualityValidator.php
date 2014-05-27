<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EqualityValidator implements ModelValidation {
	
	private $message_stub='%s must be identical';
	private $fields=array();
	
	function __construct($fields) {
		$this->fields=$fields;
	}

	function test(DataObject $do,Array &$errors) {
		$fail=false;
		$first_val='';
		foreach($this->fields as $fieldname) {
			if(empty($first_val)) {
				$first_val=$do->$fieldname;
			}
			else if($do->$fieldname !== $first_val) {
				$fail=true;	
			}
		}
		if($fail) {
			$fieldlist='';
			foreach($this->fields as $fieldname) {
				$fieldlist.=$do->getField($fieldname)->tag.',';
			}
			$fieldlist=substr($fieldlist,0,-1);
			$errors[$this->fields[0]]=sprintf($this->message_stub,$fieldlist);
			return false;
		}
		return $do;
	}
	
}

?>

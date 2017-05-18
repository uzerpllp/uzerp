<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DistinctValidator implements ModelValidation{
	private $fields=array();
	private $message_stub='Fields %s cannot be the same.';
	/**
	 * Constructor. Takes a fieldname for use when testing
	 * @todo allow passing of an array of fieldnames to be tested in combination
	 */
	function __construct($fields,$message=null) {
		if(!is_array($fields))
			$fields=array($fields);
		$this->fields=$fields;
		if($message!=null) {
			$this->message_stub=$message;
		}
	}

	function test(DataObject $do,Array &$errors) {
		$do_name=get_class($do);
		$test_do=new $do_name;
		$values=array();
		foreach($this->fields as $fieldname) {
			$value = $do->{$fieldname};
			if (!in_array($value, $values)) {
				$values[]=$do->{$fieldname};
			} else {
				$fieldTags = array();
				foreach ($this->fields as $field) {
					$fieldTags[] = $do->getField($field)->tag;
				}
				
				$errors[$this->fields[0]]=sprintf($this->message_stub,implode(' and ', $fieldTags));
				return false;
			}
		}
		
		return $do;
	}
}
?>

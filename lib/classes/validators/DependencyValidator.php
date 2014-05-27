<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DependencyValidator implements ModelValidation{

	protected $version='$Revision: 1.3 $';
	
	private $fields=array();
	private $cc;
	private $message_stub='%s is required';
	private $message_stub2='%s are required';
	/**
	 * Constructor. Takes a constraint (valid php condition) and field array
	 * e.g. constaint('field', '==', '1')
	 * 
	 * On validating the model, if the constraint is true, then the fields must be present
	 * Evaluates the constraint by getting the field value for the 'field'
	 */
	function __construct($cc,$fields,$message=null) {
		$this->cc=$cc;
		if(!is_array($fields))
			$fields=array($fields);
		$this->fields=$fields;
		if($message!=null) {
			$this->message_stub=$message;
			$this->message_stub2=$message;
		}
	}

	function test(DataObject $do,Array &$errors) {
		$dependency=true;
		$db=DB::Instance();
		foreach($this->cc as $c) {
			$str=$db->qstr($do->getField($c['constraint']->fieldname)->finalvalue).' '
				.$c['constraint']->operator.' '
				.$c['constraint']->value;
			eval("\$a=$str;");
			if (!$a) {
				$dependency=false;
			}
		}
		if ($dependency) {
			foreach ($this->fields as $field) {
				$value = $do->getField($field)->finalvalue;
				if(empty($value)&&$value!==0&&$value!=='0') {
					if(count($this->fields)==1) {
						$errors[$this->fields[0]]=sprintf($this->message_stub,$do->getField($field)->tag);
					} else {
						$fieldlist='';
						foreach($this->fields as $fieldname) {
							$fieldlist.=$do->getField($fieldname)->tag.',';
						}
						$fieldlist=substr($fieldlist,0,-1);
						$errors[$this->fields[0]]=sprintf($this->message_stub2,$fieldlist);
					}
					return false;
				}
			}
		}
		return $do;

	}
}
?>
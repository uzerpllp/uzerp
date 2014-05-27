<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class OrConstraint extends Constraint {

	protected $version='$Revision: 1.2 $';
	
	private $constraints=array();
	public function __construct($fieldname) {
		$this->field= $fieldname;
		$args = func_get_args();
		foreach($args as $arg) {
			if(!is_array($arg)) {
				continue;
			}
			$op=$arg[0];
			$val=$arg[1];
			$this->constraints[]= new Constraint($this->field,$op,$val);
		}
	}
	
	public function __toString() {
		$string='(';
		foreach($this->constraints as $constraint) {
			$string.=$constraint->__toString();
			$string.=' OR ';
		}
		$string.=' 1=0) ';
		return $string;
	}
	
	
}
?>
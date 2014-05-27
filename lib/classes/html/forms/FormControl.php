<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
	/**
	 * Abstract class to represent all types of form control
	 *
	 */
abstract class FormControl {
	protected $classNames=array();
	protected $tag;
	protected $name;
	abstract function __construct(DataField $field);
	
	abstract function render();
	
	public function addClassName($name) {
		$this->classNames[]=$name;
	}
	public function hasClassName($name) {
		return in_array($name,$this->classNames);
	}
	public function getClassNameString() {
		if(count($this->classNames)>0)
			$string='class="'.implode(' ',$this->classNames).'" ';
		else
			$string='';
		return $string;
	}
	function getTag() {
		if(empty($this->tag))
			$this->tag=ucwords($this->name);
		return $this->tag;
	}

	function setCompulsory(){
		return;
	}

	function __get($var) {
		return $this->{$var};
	}
	
	function extractData() {
			$this->name=$this->_data->name;
		if($this->_data->not_null&&!$this->_data->has_default)
			$this->setCompulsory();
	}

}
?>

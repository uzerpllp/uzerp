<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SelectControl extends FormControl{
	public $name, $type;
	function __construct($field) {
		$this->_data=$field;
		$this->extractData();
	}

	function extractData() {
			$this->name=$this->_data->name;
		if($this->_data->not_null&&!$this->_data->has_default)
			$this->setCompulsory();
	}
	function render($additional='') {
		$html="{select attribute='{$this->name}' {$this->getClassNameString()}}\n";
			return $html;
	}
}
?>

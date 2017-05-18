<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TextAreaControl extends FormControl {

	public function __construct(DataField $field){
		$this->_data=$field;
		$this->extractData();
	}
	
	public function render() {
		$html="{textarea attribute='{$this->name}' {$this->getClassNameString()}}\n";
		return $html;
	}

	public function setCompulsory() {
		$this->compulsory=true;
		$this->addClassName('compulsory');
	}
	
}
?>

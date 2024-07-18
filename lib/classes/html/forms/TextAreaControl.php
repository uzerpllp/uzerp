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
	
	#[\Override]
	public function render($additional='') {
		$html="{textarea attribute='{$this->name}' {$this->getClassNameString()}}\n";
		return $html;
	}

	#[\Override]
	public function setCompulsory() {
		$this->compulsory=true;
		$this->addClassName('compulsory');
	}
	
}
?>

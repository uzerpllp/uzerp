<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DateControl extends InputControl {
	public $type='date';
	protected $value='';
	protected $match;
	protected $compulsory;
	
	#[\Override]
	public function render($additional='') {
		$additional='value="'.$this->value.'" ';
		return parent::render($additional);
	}

	#[\Override]
	public function setCompulsory() {
		$this->compulsory=true;
		$this->addClassName('compulsory');
	}
	
}
?>

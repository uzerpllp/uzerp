<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class HiddenControl extends InputControl {

	public $type='hidden';
	
	public function render() {
		
		$html = parent::render($additional);
		return $html;
	}
		
}
?>

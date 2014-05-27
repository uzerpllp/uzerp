<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class NumericControl extends TextControl {
		
	function __construct($field) {
		$this->addClassName('numeric');
		parent::__construct($field);
	}
	
}
?>
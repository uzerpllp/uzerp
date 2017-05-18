<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class InjectorClassCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='InjectorClass') {
		parent::__construct($do);
			
	}

	function getClassesList($category='') {
		$sh=new SearchHandler($this,false);
		if (!empty($category)) {
			$sh->addConstraint(new Constraint('category', '=', $category));
		}
		$this->load($sh);
	}

}
?>
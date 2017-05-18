<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectcategoryCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='Projectcategory') {
		parent::__construct($do);
		$this->identifierField='name';
	}
		
}
?>
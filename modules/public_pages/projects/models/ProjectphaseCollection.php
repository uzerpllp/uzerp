<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectphaseCollection extends DataObjectCollection {
	
	public $field;
	
	function __construct($do='Projectphase') {
		parent::__construct($do);
			
		$this->identifierField='name';
	}
		
}
?>
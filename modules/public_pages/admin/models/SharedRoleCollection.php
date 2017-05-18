<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SharedRoleCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.1 $';
	
	public $field;
		
	function __construct($do='SharedRole') {
		parent::__construct($do);

		
	}

}
?>

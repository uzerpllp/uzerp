<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class UzletCallCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.2 $';
	
	public $field;
		
	function __construct($do='UzletCall') {
		parent::__construct($do);
		$this->title='uzLet Calls';
	}

}
?>
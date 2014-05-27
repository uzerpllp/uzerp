<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class IntrastatTransTypeCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.1 $';
	public $field;
		
	function __construct($do='IntrastatTransType') {
		parent::__construct($do);
			
	}

}
?>
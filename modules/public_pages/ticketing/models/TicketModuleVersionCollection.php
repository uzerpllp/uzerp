<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketModuleVersionCollection extends DataObjectCollection {	

	protected $version='$Revision: 1.1 $';
	
	public $field;
	
	function __construct($do='TicketModuleVersion') {
		parent::__construct($do);
	}

}
?>
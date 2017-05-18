<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketStatusCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='TicketStatus') {
		parent::__construct($do);
	}
		
}
?>
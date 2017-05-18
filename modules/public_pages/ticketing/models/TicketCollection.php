<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='Ticket', $tablename='tickets_overview') {
		parent::__construct($do, $tablename);
			
	}
	
}
?>
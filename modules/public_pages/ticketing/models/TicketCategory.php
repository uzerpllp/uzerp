<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketCategory extends DataObject {
	
	function __construct($tablename='ticket_categories') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->hasMany('Ticket', 'ticket_category_id');
		
	}
	
	function __toString() {
		return $this->name;
	}

}
?>
<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketPriority extends DataObject {
	
	function __construct($tablename='ticket_priorities') {
		parent::__construct($tablename);
		$this->idField='id';

		$this->orderby = 'index';
		
		$this->hasMany('Ticket', 'ticket_priority_id');
		
		$this->setConcatenation('name', array('index','name'), '-');
	}
	
	function __toString() {
		return $this->index . ' - ' . $this->name;
	}
	
	function __get($key) {
		return parent::__get($key);
	}

}
?>
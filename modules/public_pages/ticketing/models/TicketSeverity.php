<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketSeverity extends DataObject {
	
	function __construct($tablename='ticket_severities') {
		parent::__construct($tablename);
		$this->idField='id';

		$this->orderby = 'index';
		
		$this->hasMany('Ticket', 'ticket_severity_id');
	}

	function __toString() {
		return $this->index . ' - ' . $this->name;
	}

}
?>
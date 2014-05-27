<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
// THIS IS A JOIN TABLE!!! Probably shouldn't have a model.
class TicketResponse extends DataObject {
	
	function __construct($tablename='ticket_responses') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->orderby = 'created';
		$this->orderdir = 'asc';
		
		$this->belongsTo('Ticket', 'ticket_id');
	}

}
?>

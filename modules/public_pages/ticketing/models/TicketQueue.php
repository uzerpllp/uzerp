<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketQueue extends DataObject {
	
	protected $version='$Revision: 1.4 $';
	
	function __construct($tablename='ticket_queues') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->hasMany('Ticket', 'ticket_queue', 'ticket_queue_id', null, false);
	}

}
?>
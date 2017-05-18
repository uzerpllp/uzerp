<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketStatus extends DataObject {
	
	protected $version='$Revision: 1.4 $';
	
	function __construct($tablename='ticket_statuses') {
		parent::__construct($tablename);
		$this->idField='id';

		$this->orderby = 'index';
		$this->setEnum('status_code', array( 'NEW'=>'New'
			  								,'OPEN'=>'Open'
			  								,'RESO'=>'Resolved'
			  								,'CLSD'=>'Closed'));
	}

}
?>
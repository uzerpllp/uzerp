<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketModuleVersion extends DataObject {
	
	protected $version='$Revision: 1.1 $';
	
	function __construct($tablename='ticket_module_versions') {
		parent::__construct($tablename);
		$this->idField='id';

		$this->orderby = array('module', 'version');
		
		$this->belongsTo('Ticket', 'ticket_id');
	}
	
}
?>
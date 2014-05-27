<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketConfiguration extends DataObject {
	
	protected $version='$Revision: 1.4 $';
	
	protected $defaultDisplayFields=array('company'=>'Company'
										 ,'client_ticket_priority'
										 ,'client_ticket_severity'
										 ,'client_ticket_status'
										 ,'internal_ticket_priority'
										 ,'internal_ticket_severity'
										 ,'internal_ticket_status'
										 ,'ticket_category'
										 ,'ticket_queue');
	
	function __construct($tablename='ticket_configurations') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
//echo 'TicketConfiguration::__construct client_ticket_priority_default not null='.$this->getField('client_ticket_priority_default')->not_null.'<br>';
		
// Set specific characteristics
		$this->idField='id';
		$this->identifierField='';

 		$this->validateUniquenessOf('company_id');
		
// Define relationships
 		$this->belongsTo('Company', 'company_id', 'company');
 		$this->belongsTo('ticketPriority', 'client_ticket_priority_id', 'client_ticket_priority_default');
 		$this->belongsTo('ticketPriority', 'internal_ticket_priority_id', 'internal_ticket_priority_default');
 		$this->belongsTo('ticketSeverity', 'client_ticket_severity_id', 'client_ticket_severity_default');
 		$this->belongsTo('ticketSeverity', 'internal_ticket_severity_id', 'internal_ticket_severity_default');
 		$this->belongsTo('ticketStatus', 'client_ticket_status_id', 'client_ticket_status_default');
 		$this->belongsTo('ticketStatus', 'internal_ticket_status_id', 'internal_ticket_status_default');
 		$this->belongsTo('ticketQueue', 'ticket_queue_id', 'ticket_queue_default');
 		$this->belongsTo('ticketCategory', 'ticket_category_id', 'ticket_category_default');
 		
// Define field formats

// Define enumerated types
 		
	}
	
}
?>
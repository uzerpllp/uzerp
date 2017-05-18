<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MyCurrentTicketsEGlet extends SimpleListEGlet {
	protected $template = 'current_tickets_client.tpl';
	
	function populate() {
		$tickets = new TicketCollection(new Ticket);
		$pl = new PageList('current_tickets');
		$ticket_sh = new SearchHandler($tickets,false);
		$ticket_sh->setLimit(10);
		$ticket_sh->setOrderBy('created','ASC');
		
		$user = new User();
		$user->loadBy('username', EGS_USERNAME);

		$ticket_sh->addConstraint(new Constraint('originator_person_id', '=', $user->username));
		$ticket_sh->addConstraint(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
		
		// Find open statuses
		$statuses = new TicketStatusCollection(new TicketStatus);
		$status_sh = new SearchHandler($statuses);
		$status_sh->addConstraint(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
		$status_sh->addConstraint(new Constraint('status_code', '=', 'NEW'), 'OR');
		$status_sh->addConstraint(new Constraint('status_code', '=', 'OPEN'), 'OR');
		$statuses->load($status_sh);
		
		foreach ($statuses->getContents() as $status) {
			$ticket_sh->addConstraint(new Constraint('client_ticket_status_id', '=', $status->id), 'OR');
		}

		$tickets->load($ticket_sh);
		$pl->addFromCollection($tickets,array('module'=>'ticketing','controller'=>'tickets','action'=>'view'),array('id'),'ticket','summary');
		$this->contents=$pl->getPages()->toArray();
	}
}
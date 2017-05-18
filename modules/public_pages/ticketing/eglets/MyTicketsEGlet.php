<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MyTicketsEGlet extends SimpleListEGlet {
	
	function populate() {
		$pl = new PageList('my_tickets');
		$my_tickets = new TicketCollection(new Ticket);
		$sh = new SearchHandler($my_tickets,false);
		$sh->extract();
		$sh->addConstraint(new Constraint('assigned_to','=',EGS_USERNAME));
		$cc = new ConstraintChain();
		$cc->add(new Constraint('internal_status_code','=','NEW'),'OR');
		$cc->add(new Constraint('internal_status_code','=','OPEN'),'OR');
		$sh->addConstraintChain($cc);
		$sh->setLimit(10);
		$sh->setOrderBy('created','DESC');
		$my_tickets->load($sh);
		$pl->addFromCollection($my_tickets,array('module'=>'ticketing','controller'=>'tickets','action'=>'view'),array('id'),'ticket','summary');
		$this->contents=$pl->getPages()->toArray();
	}
	
}
?>

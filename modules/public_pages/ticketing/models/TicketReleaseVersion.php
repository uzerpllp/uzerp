<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketReleaseVersion extends DataObject {
	
	protected $version='$Revision: 1.3 $';
	
	function __construct($tablename='ticket_release_versions') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField='release_version';
		$this->_title='Ticket Release Version';

		$this->orderby = 'created';
		$this->orderdir = 'desc';
		
		$this->hasMany('Ticket', 'tickets', 'ticket_release_version_id');
		
		$this->setEnum('status'
							,array('N'=>'New'
								  ,'T'=>'In Trial'
								  ,'R'=>'Released'
								)
						);
	
	}

	public function __call($var, $args) {
		
		if (strtolower(substr($var, -6)) == 'status')
		{
			$status=substr($var, 0, strlen($var)-6);
			switch (strtolower($status)) {
				case 'new':
					return 'N';
				case 'intrial':
					return 'T';
				case 'released':
					return 'R';
				default:
					return '';
			}	
		}
		
	}
	
	function ticket_count() {
		$cc=new ConstraintChain();
		$cc->add(new Constraint('ticket_release_version_id', '=', $this->id));
		$ticket=new Ticket();
		$tickets=$ticket->getAll($cc);
		return count($tickets);		
	}

}
?>
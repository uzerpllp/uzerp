<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Ticket extends DataObject {
	
	protected $version='$Revision: 1.10 $';
	
	protected $defaultDisplayFields=array('number'
										 ,'summary'
										 ,'assigned_to'
										 ,'ticket_queue'
										 ,'client_ticket_status'
										 ,'internal_ticket_status'
										 ,'internal_ticket_severity'
										 ,'internal_ticket_priority'
										 ,'created'
										 ,'lastupdated');
	
	function __construct($tablename='tickets') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->orderby = 'lastupdated';
		$this->orderdir = 'desc';
		
		$this->identifier='summary';
		$this->identifierField='summary';

		$this->setAdditional('number');
		
		$this->hasMany('TicketResponse','ticket_id');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('type', '=', 'site'));
		$this->setAlias('response', 'TicketResponse', $cc, 'body');
	
		$this->belongsTo('TicketQueue', 'ticket_queue_id', 'ticket_queue');
		
		$this->belongsTo('TicketPriority', 'client_ticket_priority_id', 'client_ticket_priority');
		$this->belongsTo('TicketSeverity', 'client_ticket_severity_id', 'client_ticket_severity');
		$this->belongsTo('TicketStatus', 'client_ticket_status_id', 'client_ticket_status');
		
		$this->belongsTo('TicketPriority', 'internal_ticket_priority_id', 'internal_ticket_priority');
		$this->belongsTo('TicketSeverity', 'internal_ticket_severity_id', 'internal_ticket_severity');
		$this->belongsTo('TicketStatus', 'internal_ticket_status_id', 'internal_ticket_status');
		
		$this->belongsTo('TicketCategory', 'ticket_category_id', 'ticket_category');
		
		$this->belongsTo('TicketReleaseVersion', 'ticket_release_version_id', 'release_version');
		
		$this->belongsTo('Person', 'originator_person_id', 'originator_person');
		$this->belongsTo('Company', 'originator_company_id', 'originator_company');
		$this->hasOne('Company', 'originator_company_id', 'company');
		
		$this->belongsTo('User', 'assigned_to', 'person_assigned_to');
		
		$this->_fields['originator_company_id']->has_default=true;
		$company=new SystemCompany();
		$company->load(EGS_COMPANY_ID);
		$this->_fields['originator_company_id']->default_value=$company->company_id;
		
		$user=getCurrentUser();
		
		if ($user) {
			if (isset($user->email)) {
				$this->_fields['originator_email_address']->has_default=true;
				$this->_fields['originator_email_address']->default_value=$user->email;
			}
			if (!is_null($user->person_id)) {
				$this->_fields['originator_person_id']->has_default=true;
				$this->_fields['originator_person_id']->default_value=$user->person_id;
				if (!is_null($user->persondetail->email->contactmethod)) {
					$this->_fields['originator_email_address']->has_default=true;
					$this->_fields['originator_email_address']->default_value=$user->persondetail->email->contactmethod;
				}
			}
		}
		
		$this->_fields['raised_by']->has_default=true;
		$this->_fields['raised_by']->default_value=EGS_USERNAME;
	}


	static function getCompanyEmail ($company_id)
	{
// Get the email address for the company
// Use the first Technical address that is not defined as name TICKET_SUPPORT
//      TICKET_SUPPORT is defined in conf/config.php
// If that does not exist, use the main address
		$config	= Config::Instance();
		
		$contact = '';
		$company = new Company();
		$company->load($company_id);
		$party=$company->party;
		$sh = new SearchHandler(new PartyContactMethodCollection(new PartyContactMethod), false);
		$sh->AddConstraint(new Constraint('type', '=', 'E'));

		$ticket_support = $config->get('TICKET_SUPPORT');
		
		if (!empty($ticket_support))
		{
			$sh->AddConstraint(new Constraint('name', '!=', $ticket_support));
		}
		
		$party->addSearchHandler('contactmethods',$sh);
		$methods = $party->contactmethods;
					
		foreach ($methods as $method)
		{
			if ($method->technical == true)
			{
				// Technical contact favoured above all else
				$contact = $method->contact;
				break;
			}

			if( ($method->main == true))
			{
				// If no contact yet found and this contact is the main contact, use this instead
				$contact = $method->contact;
			}
		}
		
		return $contact;
		
	}
	
}

// End of Ticket

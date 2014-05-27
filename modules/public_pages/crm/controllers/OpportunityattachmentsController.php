<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class OpportunityattachmentsController extends AttachmentsController
{
	
	protected $version = '$Revision: 1.6 $';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		// Set up some variables
		$this->setModule('crm');
		$this->setController('opportunityattachments');
		$this->setModel('opportunity');
		$this->setIdField('opportunity_id');
	}
	
	public function view_crm_opportunity ()
	{
		parent::index();
	}
}

// End of OpportunityattachmentsController

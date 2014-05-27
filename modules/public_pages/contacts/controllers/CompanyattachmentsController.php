<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanyattachmentsController extends AttachmentsController
{
	
	protected $version = '$Revision: 1.6 $';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		// Set up some variables
		$this->setModule('contacts');
		
		$this->setController('companyattachments');
		
		$this->setModel('company');
		
		$this->setIdField('company_id');
	}
}

// End of CompanyattachmentsController

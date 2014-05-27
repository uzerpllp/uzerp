<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PartyattachmentsController extends AttachmentsController
{

	protected $version = '$Revision: 1.5 $';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		// Set up some variables
		$this->setModule('contacts');
		
		$this->setController('partyattachments');
		
		$this->setModel('party');
	}

	protected function getPageName($base = null, $type = null)
	{
		return parent::getPageName((empty($base)?'attachment':$base), $type);
	}

}

// End of PartyattachmentsController

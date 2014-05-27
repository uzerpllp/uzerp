<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PartyContactMethodCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.5 $';
	
	function __construct($do = 'PartyContactMethod', $tablename = 'partycontactmethodoverview')
	{
		parent::__construct($do, $tablename);
			
		$this->identifier		= 'name';
		$this->identifierField	= 'name';
	}

}

// End of PartyContactMethodCollection

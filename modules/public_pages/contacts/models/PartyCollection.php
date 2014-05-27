<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PartyCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.5 $';
	
	public $field;
		
	function __construct($do = 'Party', $tablename = 'party')
	{
		parent::__construct($do, $tablename);
			
		$this->identifier		= 'name';
		$this->identifierField	= 'name';
	}

}

// End of PartyCollection

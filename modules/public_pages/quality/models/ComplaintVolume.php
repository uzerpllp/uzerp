<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintVolume extends DataObject
{

	protected $version = '$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('id'
										   ,'year'
										   ,'period'
										   ,'packs'
										   );
	
	function __construct($tablename = 'qc_volume')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField = 'id';
		
// Define relationships

// Define field formats

// Define validation
		
// Define enumerated types

// Define system defaults
	
	}

}

// End of ComplaintVolume

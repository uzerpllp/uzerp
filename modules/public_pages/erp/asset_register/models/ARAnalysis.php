<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ARAnalysis extends DataObject
{

	protected $version = '$Revision: 1.6 $';
	
	function __construct($tablename = 'ar_analysis')
	{
		
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics		
		$this->idField			= 'id';
		$this->identifierField	= "description";		
 		
// Define validation
		$this->validateUniquenessOf('description');
		
// Define relationships
		
// Define field formats		
 		
// Define enumerated types
	}

}

// End of ARAnalysis

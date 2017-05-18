<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ARLocation extends DataObject
{

	protected $version = '$Revision: 1.6 $';
	
	function __construct($tablename = 'ar_locations')
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
		$this->belongsTo('GLCentre', 'pl_glcentre_id', 'pl_glcentre'); 
		$this->belongsTo('GLCentre', 'bal_glcentre_id', 'bal_glcentre'); 
		
// Define field formats		
		
// Define enumerated types
	}

}

// End of ARLocation

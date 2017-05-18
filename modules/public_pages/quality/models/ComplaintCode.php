<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintCode extends DataObject
{

	protected $version = '$Revision: 1.6 $';
	
	function __construct($tablename = 'qc_complaint_codes')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= array('code', 'description');
		$this->orderby			= 'code';
		
// Define relationships
		$this->hasMany('SupplementaryComplaintCode', 'supplementarycomplaintcodes', 'complaint_code_id');
		
// Define field formats

// Define validation
		
// Define enumerated types

// Define system defaults

	}
	
}

// End of ComplaintCode

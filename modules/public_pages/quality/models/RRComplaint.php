<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class RRComplaint extends Complaint
{
	
	protected $version = '$Revision: 1.4 $';
	
	function __construct($tablename = 'qc_complaints') {
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		
// Define relationships

// Define field formats

// Define validation
		
// Define enumerated types

// Define system defaults
		$this->type = 'RR';
		
		$this->getField('type')->setDefault('RR');
		
	}
}

// End of RRComplaint

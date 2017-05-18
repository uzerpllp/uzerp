<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
  
class SupplementaryComplaintCode extends DataObject
{
	
	protected $version = '$Revision: 1.8 $';
	
	protected $defaultDisplayFields = array(
		'code',
		'description',
		'parent_code'
	);
	
	function __construct($tablename = 'qc_supplementary_complaint_codes')
	{
		parent::__construct($tablename); 

// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= array('code', 'description');		
		
// Define relationships
		$this->belongsTo('ComplaintCode', 'complaint_code_id', 'complaint_code');

// Define field formats

// Define validation
		
// Define enumerated types

// Define system defaults
	}

}

// End of SupplementaryComplaintCode

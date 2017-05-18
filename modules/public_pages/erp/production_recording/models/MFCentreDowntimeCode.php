<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFCentreDowntimeCode extends DataObject
{

	protected $version = '$Revision: 1.3 $';
	
	function __construct($tablename = 'mf_centre_downtime_codes') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField = 'id';
 		
// Define relationships
 		$this->belongsTo('MFCentre', 'mf_centre_id', 'mf_centre');
 		$this->belongsTo('MFDowntimeCode', 'mf_downtime_code_id', 'mf_downtime_code');
 		
// Define field formats

// Define enumerated types
 		
	}

}

// End of MFCentreDowntimeCode

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFCentreWasteType extends DataObject
{

	protected $version = '$Revision: 1.3 $';
	
	function __construct($tablename = 'mf_centre_waste_types')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField = 'id';
 		
// Define relationships
 		$this->belongsTo('MFCentre', 'mf_centre_id', 'mf_centre');
 		$this->belongsTo('MFWasteType', 'mf_waste_type_id', 'mf_waste_type');
 		$this->hasOne('MFWasteType', 'mf_waste_type_id', 'waste_type_detail');
 		
// Define field formats

// Define enumerated types
 		
	}

}

// End of MFCentreWasteType

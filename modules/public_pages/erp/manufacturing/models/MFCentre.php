<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFCentre extends DataObject {

	protected $version='$Revision: 1.6 $';
	
	function __construct($tablename='mf_centres') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->orderby='work_centre';
		
		$this->identifierField='work_centre || \' - \' ||centre';
 		$this->validateUniquenessOf('work_centre');
 		
// Define relationships
 		$this->belongsTo('MFDept', 'mfdept_id', 'mfdept'); 
 		$this->hasOne('MFDept', 'mfdept_id', 'dept_detail'); 
 		
// Define field formats
		$this->getField('centre_rate')->addValidator(new NumericValidator());
		$this->getField('centre_rate')->addValidator(new MinimumValueValidator(0));
		
// Define enumerated types
 		
	}

}
?>
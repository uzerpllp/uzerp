<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFDowntimeCode extends DataObject {

	protected $version = '$Revision: 1.3 $';
	
	protected $defaultDisplayFields = array('downtime_code'
											,'description'
											);
	
	protected $linkRules;
											
	function __construct($tablename = 'mf_downtime_codes')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField = 'id';
		$this->orderby = array('downtime_code');
		
		$this->identifierField = 'downtime_code|| \'- \' ||description';
 		$this->validateUniquenessOf(array('downtime_code'));

// Define relationships
 		$this->hasMany('MFCentreDowntimeCode', 'mf_centres', 'mf_downtime_code_id');

// Define field formats

// Define enumerated types
 		
// Define related links (empty actions/rules prevent display of related links)
 		$this->linkRules=array('mf_centres'=>array('actions'=>array()
												  ,'rules'=>array()
												  )
							  );
	
	}

}

// End of MFDowntimeCode

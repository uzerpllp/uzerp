<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HourPaymentType extends DataObject
{

	protected $version = '$Revision: 1.3 $';
	
	protected $defaultDisplayFields = array('hours_type'
										   ,'payment_type');
	
	public function __construct($tablename = 'hours_payment_types')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		$this->identifierField = array('hours_type', 'payment_type');
		
		// Set specific characteristics
		
		// Define relationships
		$this->belongsTo('HourType','hours_type_id','hours_type');
		$this->belongsTo('EmployeePaymentType', 'payment_type_id', 'payment_type');
		
		// Define field formats
		
		// set formatters, more set in load() function

		// Define enumerated types
						
		// Define default values
		
		// Define field formatting
	
		// Define link rules for related items
	
	}
	
}

// End of HourPaymentType

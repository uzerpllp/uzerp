<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeePaymentType extends DataObject
{

	protected $version = '$Revision: 1.3 $';
	
	protected $defaultDisplayFields = array('name'
										   ,'description'
										   ,'allow_zero_units');
	
	public function __construct($tablename = 'employee_payment_types')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		$this->identifierField = array('name','description');
		
		$this->orderby = 'position';
		
		// Set specific characteristics
		
		// Define relationships
		
		// Define field formats
		
		// set formatters, more set in load() function

		// Define enumerated types
						
		// Define default values
		
		// Define field formatting
	
		// Define link rules for related items
	
	}

}

// End of EmployeePaymentType

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeePayFrequency extends DataObject
{

	protected $version = '$Revision: 1.1 $';

	protected $defaultDisplayFields = array('name'
										   ,'description');

	public function __construct($tablename = 'employee_pay_frequencies')
	{

		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		$this->identifierField = array('name', 'description');
//		$this->identifierField = 'name';

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

// End of EmployeePayFrequency

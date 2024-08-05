<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeeCollection extends DataObjectCollection
{

	protected $identifierField;

	public $field;

	function __construct($do = 'Employee', $tablename = 'employeeoverview')
	{
		parent::__construct($do, $tablename);

		$this->identifierField='employee';
	}

}

// End of EmployeeCollection

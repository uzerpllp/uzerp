<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeeTrainingPlanCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.5 $';
	
	public $field;
		
	function __construct($do = 'EmployeeTrainingPlan', $tablename = 'employeetrainingplans_overview')
	{
		parent::__construct($do, $tablename);

		$this->identifierField = 'employee_id';
	}

}

// End of EmployeeTrainingPlanCollection

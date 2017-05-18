<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeeTrainingPlan extends DataObject {
	
	protected $version = '$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('name'					=> 'Training Plan'
										   ,'expected_start_date'	=> 'expected_start_date'
										   ,'expected_end_date'		=> 'expected_end_date'
										   ,'actual_start_date'		=> 'actual_start_date'
										   ,'actual_end_date'		=> 'actual_end_date');

	function __construct($tablename = 'employee_training_plans')
	{
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField = 'id';
		$this->identifierField = 'employee_id';
		
		// Define relationships
		$this->belongsTo('Employee', 'employee_id', 'employee');
 		$this->belongsTo('TrainingObjective', 'training_objective_id', 'training_objective'); 
	
		// Define field formats
		
		// set formatters, more set in load() function

		// Define enumerated types
						
		// Define default values
		
		// Define field formatting
	
		// Define link rules for related items
	
	}
	
}

// End of EmployeeTrainingPlan

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeeContractDetail extends DataObject
{

	protected $version = '$Revision: 1.3 $';
	
	protected $defaultDisplayFields = array('employee'
										   ,'std_value' => 'Value'
										   ,'from_pay_frequency' => ''
										   ,'to_pay_frequency' => 'Per'
										   ,'start_date'
										   ,'end_date');
	
	public function __construct($tablename = 'employee_contract_details')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		$this->identifierField = array('employee_id', 'start_date');
		
		// Set specific characteristics
		
		// Define relationships
		$this->belongsTo('Employee', 'employee_id', 'employee');
		$this->belongsTo('EmployeePayFrequency', 'from_pay_frequency_id', 'from_pay_frequency');
		$this->belongsTo('EmployeePayFrequency', 'to_pay_frequency_id', 'to_pay_frequency');
		
		// Define field formats
		
		// set formatters, more set in load() function

		// Define enumerated types
		
		// Define default values
		
		// Define field formatting
	
		// Define link rules for related items
	
	}
	
	public function checkStartDate($_employee_id, $_start_date, $_end_date = '')
	{
		$cc = new ConstraintChain();
		$cc->add(New Constraint('employee_id', '=', $_employee_id));
		
		$db = DB::Instance();
		
		$cc1 = new ConstraintChain();
		$cc1->add(New Constraint('start_date', 'between', $db->qstr(fix_date($_start_date)).' and '.$db->qstr((empty($_end_date)?fix_date(date(DATE_FORMAT)):fix_date($_end_date)))));
		
		$cc2 = new ConstraintChain();
		$cc2->add(New Constraint('start_date', '<', fix_date($_start_date)));
		
		$cc3 = new ConstraintChain();
		$cc3->add(New Constraint('end_date', '>=', fix_date($_start_date)));
		$cc3->add(New Constraint('end_date', 'is', 'NULL'), 'OR');
		
		$cc2->add($cc3);
		$cc1->add($cc2, 'OR');
		$cc->add($cc1);
				
		return $this->getMax('end_date', $cc);
		
	}
	
	public function getLatest($_employee_id, $_from_pay_frequency_id, $_to_pay_frequency_id)
	{
		$cc = new ConstraintChain();
		
		$cc->add(New Constraint('employee_id', '=', $_employee_id));
		$cc->add(New Constraint('from_pay_frequency_id', '=', $_from_pay_frequency_id));
		$cc->add(New Constraint('to_pay_frequency_id', '=', $_to_pay_frequency_id));
		
		$start_date = $this->getMax('start_date', $cc);
		
		if (!empty($start_date))
		{
			$cc->add(New Constraint('start_date', '=', $start_date));
		}
		
		$this->loadBy($cc);
		
	}
	
}

// End of EmployeeContractDetail

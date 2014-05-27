<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeeRate extends DataObject
{

	protected $version = '$Revision: 1.2 $';
	
	protected $defaultDisplayFields = array('employee'
										   ,'payment_type'
										   ,'default_units'
										   ,'units_variable'
										   ,'rate_value' => 'Rate'
										   ,'rate_variable'
										   ,'pay_frequency' => 'Per'
										   ,'start_date'
										   ,'end_date');
	
	public function __construct($tablename = 'employee_rates')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		$this->identifierField = array('employee_id', 'start_date', 'payment_type');
		
		// Set specific characteristics
		
		// Define relationships
		$this->belongsTo('Employee', 'employee_id', 'employee');
		$this->belongsTo('EmployeePayFrequency', 'pay_frequency_id', 'pay_frequency');
		$this->belongsTo('EmployeePaymentType', 'payment_type_id', 'payment_type');
		
		// Define field formats
		
		// set formatters, more set in load() function

		// Define enumerated types
		
		// Define default values
		
		// Define field formatting
	
		// Define link rules for related items
	
	}
	
	public function getCurrent($_employee_id, $_payment_type_id, $_start_date, $_end_date = '')
	{
		$cc = new ConstraintChain();
		$cc->add(New Constraint('employee_id', '=', $_employee_id));
		$cc->add(New Constraint('payment_type_id', '=', $_payment_type_id));
		
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
				
		$this->loadBy($cc);
		
	}
	
	public function getLatest($_employee_id, $_payment_type_id)
	{
		$cc = new ConstraintChain();
		
		$cc->add(New Constraint('employee_id', '=', $_employee_id));
		$cc->add(New Constraint('payment_type_id', '=', $_payment_type_id));
		
		$start_date = $this->getMax('start_date', $cc);
		
		if (!empty($start_date))
		{
			$cc->add(New Constraint('start_date', '=', $start_date));
		}
		
		$this->loadBy($cc);
		
	}
	
}

// End of EmployeeRate

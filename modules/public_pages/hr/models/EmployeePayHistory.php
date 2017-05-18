<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeePayHistory extends DataObject
{

	protected $version = '$Revision: 1.11 $';
	
	protected $defaultDisplayFields = array('employee'
										   ,'employee_id'
										   ,'tax_year'
										   ,'tax_month'
										   ,'tax_week'
										   ,'calendar_week'
										   ,'pay_basis'
										   ,'payment_type'
										   ,'payment_type_id'
										   ,'hours_type'
										   ,'hours_type_id'
										   ,'pay_units'
										   ,'pay_frequency'
										   ,'pay_frequency_id'
										   ,'pay_rate'
										   ,'pay_value'
										   ,'comment'
										   ,'employee_pay_periods_id');
	
	public function __construct($tablename = 'employee_pay_history')
	{
		
		// Register non-persistent attributes
		$this->setAdditional('pay_value');
		
		// Contruct the object
		parent::__construct($tablename);
		
		$this->identifierField = array('tax_year', 'tax_week', 'employee', 'hours_type');
		
		// Set specific characteristics
		
		// Define relationships
		$this->belongsTo('Employee', 'employee_id', 'employee');
		$this->belongsTo('EmployeePayPeriod', 'employee_pay_periods_id', 'employee_pay_period');
		$this->belongsTo('EmployeePaymentType', 'payment_type_id', 'payment_type');
		$this->belongsTo('HourType', 'hours_type_id', 'hours_type');
		$this->belongsTo('EmployeePayFrequency', 'pay_frequency_id', 'pay_frequency');
		
		// Define field formats
		
		// set formatters, more set in load() function

		// Define enumerated types
		
		// Define default values
		
		// Define field formatting
		$this->getField('pay_rate')->setFormatter(new PriceFormatter());
		$this->getField('pay_value')->setFormatter(new PriceFormatter());
		
		// Define link rules for related items
	
	}
	
	public function getLatestPeriodStart($_employee_id)
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('employee_id', '=', $_employee_id));
		
		return $this->getMax('period_start_date', $cc, 'employee_pay_history_overview');
		
	}
	
	public function getLatestPeriodEnd($_employee_id)
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('employee_id', '=', $_employee_id));
		
		return $this->getMax('period_end_date', $cc, 'employee_pay_history_overview');
		
	}
	
}

// End of EmployeePayHistory

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeePayPeriod extends DataObject
{

	protected $version = '$Revision: 1.7 $';
	
	protected $defaultDisplayFields = array('tax_year'
										   ,'tax_month'
										   ,'tax_week'
										   ,'calendar_week'
										   ,'pay_basis'
										   ,'period_start_date'
										   ,'period_end_date' 
										   ,'closed'
										   ,'processed_date'
										   ,'processed_period'
	);
	
	public function __construct($tablename = 'employee_pay_periods')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		$db = DB::Instance();
		$this->identifierField = array('pay_basis', $db->qstr(':').' as c1', 'period_start_date', $db->qstr('to').' as c2', 'period_end_date');
		$this->identifierFieldJoin = ' ';
		//$this->identifierFieldJoin = ' to ';
		$this->orderby = array('period_start_date', 'pay_basis');
		$this->orderdir = array('DESC', 'ASC');
		
		// Set specific characteristics
		
		// Define relationships
		
		// Define field formats
		
				
		// set formatters, more set in load() function


		// Define enumerated types
		$this->setEnum(
				'pay_basis',
				array(
						'M'	=> 'Monthly',
						'W'	=> 'Weekly',
				)
		);
		
		// Define default values
		
		// Define field validation
		$this->getField('tax_week')->addValidator(new NumericRangeValidator(1, 53));
		$this->getField('calendar_week')->addValidator(new NumericRangeValidator(1, 53));
		$this->getField('tax_month')->addValidator(new NumericRangeValidator(1, 12));
		
		$date_times = array('period_start_date','period_end_date');
		
		foreach($date_times as $date_time)
		{
			$this->getField($date_time)->addValidator(new DateValidator);
		}

		// Define link rules for related items
	
	}
	
	public static function Factory($data, &$errors = array(), $do_name = null)
	{
		
		if (empty($data['calendar_week']))
		{
			$data['calendar_week'] = date('W', strtotime(fix_date($data['period_start_date'], DATE_TIME_FORMAT, $errors)));
		}
		
		return parent::Factory($data, $errors, $do_name);
	}

	public function getLatestPeriod($_pay_basis = '')
	{
		
		$next_start_date = $this->getMax('period_start_date');
		$fields = array('period_start_date');
		$values = array($next_start_date);
		
		if (!empty($_pay_basis))
		{
			$cc = new ConstraintChain();
			$cc->add(new Constraint('pay_basis', '=', $_pay_basis));
			$next_start_date = $this->getMax('period_start_date', $cc);
			$fields = array('period_start_date');
			$values = array($next_start_date);
			$fields[] = 'pay_basis';
			$values[] = $_pay_basis;
		}
		$this->loadBy($fields, $values);
		
	}

	public function getPayPeriods($_period_start_date = '', $_closed = '')
	{
		
		$cc = new ConstraintChain();
		
		if (!empty($_period_start_date))
		{
			$cc->add(new Constraint('period_start_date', '>', $_period_start_date));
		}
			
		if (is_bool($_closed))
		{
			$cc->add(new Constraint('closed', 'is', $_closed));
		}
		
		$this->orderby	= 'period_start_date';
		$this->orderdir	= 'ASC';
		
		return $this->getAll($cc);
		
	}
	
	public function getPayPeriodByDate($_period_start_date, $_pay_basis = '')
	{
		$db = DB::Instance();
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('period_start_date', '<=', $_period_start_date));
		$cc->add(new Constraint('period_end_date', '>', $_period_start_date));
		
		if (!empty($_pay_basis))
		{
			$cc->add(new Constraint('pay_basis', '=', $_pay_basis));
		}
		
		$this->loadBy($cc);
	}
	
}

// End of EmployeePayPeriod

<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class employeeSearch extends BaseSearch
{

	protected $version = '$Revision: 1.10 $';
	
	protected $fields = array();
		
	public static function useDefault($search_data = null, &$errors, $defaults = null)
	{
		
		$search = new employeeSearch($defaults);
		
		// Employee Name
		$search->addSearchField(
			'employee',
			'name_contains',
			'contains',
			'',
			'basic'
		);
		
		// NI Number
		$search->addSearchField(
			'ni',
			'ni_contains',
			'contains',
			'',
			'basic'
		);
		
		// Employee Grade
		$search->addSearchField(
			'employee_grade_id',
			'employee_grades',
			'select',
			'',
			'basic'
		);
		$grade = DataObjectFactory::Factory('employeeGrade');
		$grades = $grade->getAll(null, TRUE, TRUE);
		$options=array(''=>'all');
		$options += $grades;
		$search->setOptions('employee_grade_id',$options);
		
		// Department
		$search->addSearchField(
			'department',
			'department_contains',
			'contains',
			'',
			'basic'
		);
		
		// Date of Leaving
		$search->addSearchField(
				'start_date/finished_date',
				'Current at',
				'betweenfields',
				date(DATE_FORMAT),
				'advanced'
		);
		
		$search->setSearchData($search_data, $errors, 'default');
		
		return $search;
	
	}
		
	public static function payHistory($search_data = null, &$errors, $defaults = null)
	{
		
		$search = new employeeSearch($defaults);
		
		// Employee
		$search->addSearchField(
			'employee_id',
			'employee',
			'select',
			'',
			'basic'
		);
		$employee = DataObjectFactory::Factory('employee');
		$employee->orderby = $employee->identifierField = 'employee';
		$employee->authorisationPolicy();
		$employees = $employee->getAll();
		$options=array(''=>'all');
		$options += $employees;
		$search->setOptions('employee_id',$options);
		
		// Employee Pay Periods
		$search->addSearchField(
			'employee_pay_periods_id',
			'pay_periods',
			'select',
			'',
			'basic'
		);
		$pay_period = DataObjectFactory::Factory('EmployeePayPeriod');
		$search->setOptions('employee_pay_periods_id', array(''=>'all') + $pay_period->getAll(null, TRUE, TRUE));
		
		// Hour Types
		$search->addSearchField(
			'hours_type_id',
			'hour_types',
			'select',
			'',
			'basic'
		);
		$hour_type = DataObjectFactory::Factory('HourType');
		$search->setOptions('hours_type_id', array(''=>'all') + $hour_type->getAll());
		
		$search->setSearchData($search_data, $errors, 'payHistory');
		
		return $search;
	
	}
		
	public static function employeePayHistory($search_data = null, &$errors, $defaults = null)
	{
		
		$search = new employeeSearch($defaults);
		
		// Employee
		$search->addSearchField(
			'employee_id',
			'employee',
			'select',
			'',
			'hidden'
		);
		$employee = DataObjectFactory::Factory('employee');
		$employee->orderby = $employee->identifierField = 'employee';
		$employee->authorisationPolicy();
		$employees = $employee->getAll();
		$options=array(''=>'all');
		$options += $employees;
		$search->setOptions('employee_id',$options);
		
		// Employee Pay Periods
		$search->addSearchField(
			'employee_pay_periods_id',
			'pay_periods',
			'select',
			'',
			'basic'
		);
		$pay_period = DataObjectFactory::Factory('EmployeePayPeriod');
		$search->setOptions('employee_pay_periods_id', array(''=>'all') + $pay_period->getAll(null, TRUE, TRUE));
		
		// Hour Types
		$search->addSearchField(
			'hours_type_id',
			'hour_types',
			'select',
			'',
			'basic'
		);
		$hour_type = DataObjectFactory::Factory('HourType');
		$search->setOptions('hours_type_id', array(''=>'all') + $hour_type->getAll());
		
		$search->setSearchData($search_data, $errors, 'employeePayHistory');
		
		return $search;
	
	}
		
	public static function payPeriodHistory($search_data = null, &$errors, $defaults = null)
	{
		
		$search = new employeeSearch($defaults);
		
		// Employee
		$search->addSearchField(
			'employee_id',
			'employee',
			'select',
			'',
			'basic'
		);
		$employee = DataObjectFactory::Factory('employee');
		$employee->orderby = $employee->identifierField = 'employee';
		$employee->authorisationPolicy();
		$employees = $employee->getAll();
		$options=array(''=>'all');
		$options += $employees;
		$search->setOptions('employee_id',$options);
		
		$search->setSearchData($search_data, $errors, 'payPeriodHistory');
		
		return $search;
	
	}
		
	public static function payPeriods($search_data = null, &$errors, $defaults = null)
	{
		
		$search = new employeeSearch($defaults);
		
		$pay_period = DataObjectFactory::Factory('EmployeePayPeriod');
		
		// Period Start Date
		$search->addSearchField(
			'period_start_date',
			'period_start_date',
			'between',
			'',
			'basic'
		);
		
		// Pay Basis
		$search->addSearchField(
			'pay_basis',
			'pay_basis',
			'select',
			'',
			'basic'
		);
		$options = array(''=>'All');
		$options += $pay_period->getEnumOptions('pay_basis');
		$search->setOptions('pay_basis', $options);
		
		// Pay Period
		$search->addSearchField(
			'tax_week',
			'tax_week',
			'equal',
			'',
			'basic'
		);
		
		// Tax Year
		$search->addSearchField(
			'tax_year',
			'tax_year',
			'equal',
			'',
			'basic'
		);
		
		$search->setSearchData($search_data, $errors, 'payPeriods');
		
		return $search;
	
	}
	
	public function employeePayRates($search_data = null, &$errors, $defaults = null)
	{
		
		$search = new employeeSearch($defaults);
	
		// Employee
		$search->addSearchField(
			'employee_id',
			'employee',
			'select',
			'',
			'hidden'
		);
		$employee = DataObjectFactory::Factory('employee');
		$employee->orderby = $employee->identifierField = 'employee';
		$employee->authorisationPolicy();
		$employees = $employee->getAll();
		$options=array(''=>'all');
		$options += $employees;
		$search->setOptions('employee_id',$options);
		
		// Payment Type
		$search->addSearchField(
			'payment_type_id',
			'payment_type',
			'select',
			'',
			'basic'
		);
		$paytype = DataObjectFactory::Factory('EmployeePaymentType');
		$search->setOptions('payment_type_id', array(''=>'All')+ $paytype->getAll());
		
		// Start Date
		$search->addSearchField(
			'start_date',
			'start_date',
			'between',
			'',
			'advanced'
		);
		
		// Start Date
		$search->addSearchField(
			'end_date',
			'end_date',
			'between',
			'',
			'advanced'
		);
		
		$search->setSearchData($search_data, $errors, 'employeePayRates');
		
		return $search;
	
	}
	
}

// End of employeeSearch

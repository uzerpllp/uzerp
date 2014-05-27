<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class holidaySearch extends BaseSearch
{

	protected $version='$Revision: 1.1 $';
	
	protected $fields=array();
		
	public static function useDefault($search_data=null, &$errors, $defaults=null)
	{
		
		$search = new holidaySearch($defaults);
		
		// Employee Name
		$search->addSearchField(
			'employee',
			'name_contains',
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
		
		// Holiday Request Status
		$request = DataObjectFactory::Factory('holidayrequest');
		$search->addSearchField(
				'status',
				'status',
				'multi_select',
				array($request->authorise(), $request->newRequest()),
				'advanced'
		);
		$search->setOptions('status',$request->getEnumOptions('status'));
		
		$search->setSearchData($search_data,$errors);
		
		return $search;
	
	}
		
	
}

// End of holidaySearch

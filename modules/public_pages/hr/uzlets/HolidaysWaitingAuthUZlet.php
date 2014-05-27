<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HolidaysWaitingAuthUZlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.2 $';
	
	function getClassName()
	{
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		$employee = DataObjectFactory::Factory('Employee');		
		
		$user = getCurrentUser();
		
		if (!is_null($user->person_id))
		{
			$employee->loadBy('person_id', $user->person_id);
		}
		
		if ($employee->isLoaded())
		{
			$authorisor_model = $employee->holiday_model();
			
			$employee->authorisationPolicy($authorisor_model);
			
			$authorisees = $employee->getAuthorisees($authorisor_model);
		}
		else
		{
			$authorisees = array();
		}
		
		$holiday = DataObjectFactory::Factory('HolidayRequest');		
		
		$holidays = new HolidayrequestCollection($holiday);
		
		if (count($authorisees) > 0)
		{
			$holidays->setParams();
			
			$sh = new SearchHandler($holidays,false);
			
			$sh->setFields(array('id', 'employee', 'employee_id', 'start_date', 'end_date', 'num_days'));
			
			$sh->addConstraint(new Constraint('status', '=', $holiday->newRequest()));
			$sh->addConstraint(new Constraint('employee_id', 'in', '(' . implode(',', $authorisees) . ')'));
			
			$this->setSearchLimit($sh);
			
			$sh->setOrderby(array('employee', 'start_date'));
			
			$holidays->load($sh);
			
			$holidays->clickcontroller = 'holidayrequests';
			$holidays->editclickaction = 'view';
		}
		
		$this->contents = $holidays;
	}
	
}

// End of HolidaysWaitingAuthUZlet

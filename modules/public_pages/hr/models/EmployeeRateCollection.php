<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeeRateCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.3 $';
	
	public function __construct($do = 'EmployeeRate', $tablename = 'employee_rates_overview')
	{
		parent::__construct($do, $tablename);
		
		$this->orderby = array('employee', 'start_date', 'payment_type');
		$this->direction = array('ASC', 'DESC', 'DESC');
		
	}
	
	public function close_off_current($_employee_id, $_end_date)
	{
		$sh = new SearchHandler($this, FALSE);
		
		$sh->addConstraint(new Constraint('employee_id', '=', $_employee_id));
		$sh->addConstraintChain(new Constraint('end_date', 'is', 'NULL'));
		
		return $this->update('end_date', $_end_date, $sh);
		
	}
	
}

// End of EmployeeRateCollection

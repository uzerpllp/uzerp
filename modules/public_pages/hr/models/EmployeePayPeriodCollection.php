<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeePayPeriodCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.1 $';
	
	public function __construct($do = 'EmployeePayPeriod', $tablename = 'employee_pay_periods')
	{
		parent::__construct($do, $tablename);
	}

}

// End of EmployeePayPeriodCollection

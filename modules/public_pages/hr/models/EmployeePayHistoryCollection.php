<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeePayHistoryCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.2 $';

	public function __construct($do = 'EmployeePayHistory', $tablename = 'employee_pay_history_overview')
	{
		parent::__construct($do, $tablename);

		$this->orderby		= 'period_start_date';

		$this->direction	= 'DESC';

	}

}

// End of EmployeePayHistoryCollection

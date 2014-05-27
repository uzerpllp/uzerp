<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeeContractDetailCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.2 $';
	
	public function __construct($do = 'EmployeeContractDetail', $tablename = 'employee_contract_details_overview')
	{
		parent::__construct($do, $tablename);
		
		$this->orderby = array('employee', 'start_date');
		$this->direction = array('ASC', 'DESC');
		
	}

}

// End of EmployeeContractDetailCollection

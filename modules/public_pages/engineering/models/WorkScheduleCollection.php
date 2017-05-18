<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WorkScheduleCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.2 $';
	
	public $field;
		
	function __construct($do = 'WorkSchedule', $tablename = 'eng_work_schedules_overview')
	{
		
		parent::__construct($do, $tablename);
		
		$this->identifierField = 'name';
		
	}

}

// End of WorkScheduleCollection

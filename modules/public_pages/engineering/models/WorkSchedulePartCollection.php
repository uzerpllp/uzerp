<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WorkSchedulePartCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.2 $';
	
	public $field;
		
	function __construct($do = 'WorkSchedulePart', $tablename = 'eng_work_schedule_parts_overview')
	{
		parent::__construct($do, $tablename);
	}
		
}

// end of WorkSchedulePartCollection

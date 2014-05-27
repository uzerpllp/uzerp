<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HolidayrequestCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.7 $';
	
	public $field;
		
	function __construct($do = 'Holidayrequest', $tablename = 'holiday_requests_overview')
	{
		parent::__construct($do, $tablename);
			
	}

	function sumByStatus($cc = '')
	{
		$sh = new SearchHandler($this, FALSE);
		
		if ($cc instanceof ConstraintChain)
		{
			$sh->addConstraintChain($cc);
		}
		
		$sh->setFields(array('status as id', 'status', 'sum(num_days) as num_days'));
		$sh->setGroupBy(array('status as id', 'status'));
		$sh->setOrderBy('status');
		
		return $this->load($sh);
	}
	
}

// End of HolidayrequestCollection

<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HolidayextradayCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.5 $';
	
	public $field;
		
	function __construct($do = 'Holidayextraday')
	{
		parent::__construct($do);
	}
		
}

// End of HolidayextradayCollection

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HolidayAuthoriserCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.6 $';

	public $field;

	function __construct($do = 'HolidayAuthoriser', $tablename = 'holiday_authorisers_overview')
	{
		parent::__construct($do, $tablename);
	}

}

// End of HolidayAuthoriserCollection

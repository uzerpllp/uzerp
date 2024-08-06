<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CRMCalendarCollection extends DataObjectCollection {

	protected $identifierField;

	function __construct($do = 'CRMCalendar')
	{

		parent::__construct($do);

		$this->identifierField = 'title';

	}

}

// end of CRMCalendarCollection.php
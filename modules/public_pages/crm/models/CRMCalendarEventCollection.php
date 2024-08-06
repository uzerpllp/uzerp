<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CRMCalendarEventCollection extends DataObjectCollection {

	protected $identifierField;

	function __construct($do = 'CRMCalendarEvent', $tablename = 'crm_calendar_events_overview')
	{

		parent::__construct($do, $tablename);

		$this->identifierField = 'title';

	}

}

// end of CRMCalendarEventCollection.php
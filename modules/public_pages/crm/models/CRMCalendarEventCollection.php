<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CRMCalendarEventCollection extends DataObjectCollection {

	protected $version = '$Revision: 1.1 $';
	
	function __construct($do = 'CRMCalendarEvent', $tablename = 'crm_calendar_events_overview')
	{
	
		parent::__construct($do, $tablename);

		$this->identifierField = 'title';
		
	}

}

// end of CRMCalendarEventCollection.php
<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CalendarEvent extends DataObject
{

	protected $version = '$Revision: 1.9 $';
	
	protected $defaultDisplayFields = array('title'
										   ,'calendar'
										   ,'start_time'
										   ,'end_time'
										   ,'owner');

	function __construct($tablename = 'calendar_events')
	{
		parent::__construct($tablename);
		
		$this->idField			= 'id';
		$this->identifierField	= 'title';
		$this->orderby			= 'start_time';
		$this->orderdir			= 'ASC';
		
		$this->belongsTo('Person', 'person_id', 'person', null, 'surname || \', \' || firstname');
		$this->belongsTo('Company', 'company_id', 'company');
		$this->belongsTo('Calendar', 'calendar_id', 'calendar');
		
	}
}

// End of CalendarEvent

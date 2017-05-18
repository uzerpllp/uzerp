<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CRMCalendarEvent extends DataObject {
	
	protected $version = '$Revision: 1.1 $';
	
	protected $defaultDisplayFields = array(
		'date'		=> 'Date',
		'title'		=> 'Title',
		'calendar'	=> 'Calendar'
	);
	
	function __construct($tablename = 'crm_calendar_events')
	{
	
		parent::__construct($tablename);
		
		$this->idField	= 'id';
		$this->orderby	= 'start_date';
		$this->orderdir	= 'desc';
		
		$this->belongsTo('CRMCalendar', 'crm_calendar_id', 'crm_calendar');
		$this->hasOne('CRMCalendar', 'crm_calendar_id', 'colour');
		
	}

}

// end of CRMCalendarEvent.php
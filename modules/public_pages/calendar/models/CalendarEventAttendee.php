<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CalendarEventAttendee extends DataObject {

	function __construct($tablename='calendar_event_attendees') {
		parent::__construct($tablename);
		$this->idField='id';
		
		
 		$this->belongsTo('Event', 'calendar_event_id', 'calendar');
 		$this->belongsTo('Person', 'person_id', 'person'); 

	}


}
?>

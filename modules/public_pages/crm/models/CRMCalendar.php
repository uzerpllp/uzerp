<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CRMCalendar extends DataObject {

	protected $version = '$Revision: 1.1 $';

	protected $defaultDisplayFields = array('title' => 'Title');

	function __construct($tablename = 'crm_calendars')
	{

		parent::__construct($tablename);

		$this->idField = 'id';
		$this->identifierField = 'title';

		$this->setEnum(
			'colour',
			array(
				'#A32929',
				'#B1365F',
				'#7A367A',
				'#5229A3',
				'#29527A',
				'#2952A3',
				'#1B887A',
				'#28754E',
				'#0D7813',
				'#528800',
				'#88880E',
				'#AB8B00',
				'#BE6D00',
				'#B1440E',
				'#865A5A',
				'#705770',	
				'#4E5D6C',
				'#5A6986',
				'#4A716C',
				'#6E6E41',
				'#8D6F47'
			)
		);

		$this->belongsTo('CRMCalendarEvent', 'crm_calendar_id', 'crm_calendar');

	}

}

// end of CRMCalendar.php
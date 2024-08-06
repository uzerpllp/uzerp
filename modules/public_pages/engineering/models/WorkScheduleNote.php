<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WorkScheduleNote extends DataObject
{

	protected $version = '$Revision: 1.2 $';

	protected $defaultDisplayFields = array(
		'title',
		'note',
		'created',
		'createdby',
		'lastupdated',
		'alteredby'
		);

	function __construct($tablename = 'eng_work_schedule_notes')
	{

		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField			= 'id';

		$this->identifierField	= 'title';
		$this->orderby			= 'lastupdated';
		$this->orderdir			= 'DESC';

		$this->setTitle('work_schedule_notes');

		// Define relationships
		$this->belongsTo('WorkSchedule', 'work_schedule_id', 'job_no');

		// Define field formats

		// Define field defaults

		// Define validation

		// Define enumerated types

		// Define Access Rules

		// Define link rules for sidebar related view

	}

}

// end of WorkScheduleNote

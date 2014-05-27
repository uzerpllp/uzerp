<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EngineeringResource extends DataObject
{

	protected $version = '$Revision: 1.2 $';
	
	protected $defaultDisplayFields = array('resource'
										   ,'person'
										   ,'resource_rate'
										   ,'quantity'
										   );
	
	function __construct($tablename = 'eng_resources')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField = 'id';
		
		$this->orderby = 'resource';
		$this->orderdir = 'asc';
		
		$this->identifierField = 'resource';
		
// Define relationships
		$this->belongsTo('Person', 'person_id', 'person', null, "surname || ', ' || firstname");
 		$this->belongsTo('MFResource', 'resource_id', 'resource');
 		$this->belongsTo('WorkSchedule', 'work_schedule_id', 'work_schedule');
 		$this->hasOne('WorkSchedule', 'work_schedule_id', 'work_schedule_detail');
 		
// Define field formats
		
// Define field defaults
		
// Define validation
		$this->validateUniquenessOf(array('person_id', 'project_id', 'start_date', 'resource_id', 'task_id','usercompanyid'),'Resource already exists on this project');
	
// Define enumerated types

// Define Access Rules

// Define link rules for sidebar related view
	
	}

	public function getNetValue()
	{
		$time = explode(':', $this->duration);
		$hours = $time[0]+$time[1]/60+$time[2]/3600;
		return $hours*$this->resource_rate;
	}

	public function getAssigned($id = '')
	{
		if (empty($id))
		{
			return array();
		}

		$cc	= new ConstraintChain();
		$cc->add(new Constraint('work_schedule_id', '=', $workschedule->id));
		$this->identifierField = 'resource_id';
		
		return $this->getAll($cc);
		
	}
	
}

// End of EngineeringResource

<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Resource extends DataObject {

	protected $version='$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('resource'
										   ,'project'
										   ,'task'
										   ,'person'
										   ,'start_date'
										   ,'end_date'
										   ,'quantity'
										   ,'resource_rate'
										   );
	
	function __construct($tablename='project_resources') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		
		$this->orderby = 'person';
		$this->orderdir = 'asc';
		
		$this->identifierField = 'person';
		
// Define relationships
		$this->belongsTo('Person', 'person_id', 'person', null, "surname || ', ' || firstname");
 		$this->belongsTo('Project', 'project_id', 'project');
 		$this->belongsTo('Task', 'task_id', 'task');
 		$this->belongsTo('MFResource', 'resource_id', 'resource');
 		
// Define field formats
		
// Define field defaults
		
// Define validation
		$this->validateUniquenessOf(array('person_id', 'project_id', 'start_date', 'resource_id', 'task_id','usercompanyid'),'Resource already exists on this project');
	
// Define enumerated types

// Define Access Rules

// Define link rules for sidebar related view
	
	}

	public function getNetValue() {
		$time = explode(':', $this->duration);
		$hours = $time[0]+$time[1]/60+$time[2]/3600;
		return $hours*$this->resource_rate;
	}

}
?>
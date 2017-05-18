<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TaskResource extends DataObject {

	protected $defaultDisplayFields = array('id'
										   ,'resource'
										   );

	function __construct($tablename='task_resources') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->validateUniquenessOf(array('resource_id','task_id'),'You cannot duplicate a resource against a task');
 		$this->belongsTo('Resource', 'resource_id', 'resource');
 		$this->belongsTo('Task', 'task_id', 'task'); 
		$this->orderby = 'person';
		$this->orderdir = 'asc';

	}

}
?>
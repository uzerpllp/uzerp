<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TaskpriorityCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='Taskpriority', $tablename='task_prioritiesoverview') {
		parent::__construct($do, $tablename);
			
		$this->identifierField='name';
	}
		
}
?>
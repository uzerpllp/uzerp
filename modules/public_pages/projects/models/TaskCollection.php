<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TaskCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.4 $';
	
	public $field;
		
	function __construct($do='Task', $tablename='tasksoverview') {
		parent::__construct($do, $tablename);
			
		$this->identifierField='name';
	}
		
	public function getTaskHourTotals (&$sh, $_task_id='') {

		$this->setTablename('task_hours_overview');
		$this->title='Task Hours';
		$fields=array('usercompanyid'
					, 'resource_rate'
					, 'sum(duration) as total_hours');
		$sh->setorderby(array('usercompanyid'));
		$sh->setGroupBy(array('usercompanyid', 'resource_rate'));
		$sh->setFields($fields);
		if (!empty($_project_id))
		{
			$sh->addConstraint(new Constraint('task_id', '=', $_task_id));
		}
		
	}

}
?>
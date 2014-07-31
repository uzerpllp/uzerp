<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.5 $';
	
	public $field;
		
	function __construct($do='Project', $tablename='projectsoverview') {
		parent::__construct($do, $tablename);
		$this->identifierField='name';
	}

	public function getProjectHourTotals (&$sh, $_project_id='') {

		$this->setTablename('project_hours_overview');
		$this->title='Project Hours';
		// use hour id to set uniqueness of identifier
		$fields=array('hour_id'
					,'name'
					,'resource_rate'
					, 'sum(duration) as total_hours');
		$sh->setorderby(array('hour_id','name','resource_rate'));
		$sh->setGroupBy(array('hour_id','name','resource_rate'));
		$sh->setFields($fields);
		if (!empty($_project_id))
		{
			$sh->addConstraint(new Constraint('project_id', '=', $_project_id));
		}
		
	}
}
?>

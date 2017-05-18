<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectIssue extends DataObject {
	
	protected $defaultDisplayFields=array('problem_description'
										 ,'project'
										 ,'problem_location'
										 ,'lastupdated'
										 ,'status'
										 ,'assigned_to'
										 ,'time_fixed'
										 );
	
	function __construct($tablename='project_issues') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->getField('status_id')->setDefaultCallback(array($this,'getDefaultStatus'));
		$this->getField('assigned_to')->setDefaultCallback(array($this,'getDefaultAssignedTo'));
 		$this->belongsTo('Project', 'project_id', 'project');
 		$this->belongsTo('ProjectIssueStatus', 'status_id', 'status');
 		$this->belongsTo('User', 'assigned_to', 'assigned'); 

	}
	
	function getDefaultStatus($field) {
		$status = new ProjectIssueStatus();
		$status->loadBy('default_value',true);
		return $status->id;
	}
	
	function getDefaultAssignedTo($field) {
		if(isset($_GET['project_id'])) {
			$resource = new Resource();
			$cc = new ConstraintChain();
			$cc->add(new Constraint('project_id','=',$_GET['project_id']));
			$cc->add(new Constraint('project_manager','=','true'));
			$res=$resource->loadBy($cc);
			if($res!==false) {
				$user = new User();
				$user->loadBy('person_id',$resource->person_id);
				return $user->username;
			}
		}
		return EGS_USERNAME;
		
	}

}
?>
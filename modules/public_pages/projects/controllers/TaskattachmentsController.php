<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class TaskattachmentsController extends AttachmentsController
{
	
	protected $version='$Revision: 1.6 $';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		// Set up some variables
		$this->setModule('projects');
		$this->setController('taskattachments');
		$this->setModel('task');
		$this->setIdField('task_id');
	}
	
	public function view_project_task()
	{
		parent::index();
	}
	
}

// End of TaskattachmentsController

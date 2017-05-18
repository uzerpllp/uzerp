<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeetrainingplansController extends Controller {

	protected $version='$Revision: 1.4 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('EmployeeTrainingPlan');
		
		$this->uses($this->_templateobject);
	}

	public function index()
	{
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new EmployeeTrainingPlanCollection($this->_templateobject));

	}	

	public function delete()
	{
		sendBack();
	}
	
	public function save()
	{
		
		if(parent::save('EmployeeTrainingPlan'))
		{
			sendTo($this->name,'index',$this->_modules);
		}
		
		$this->refresh();

	}
	
}

// End of EmployeetrainingplansController

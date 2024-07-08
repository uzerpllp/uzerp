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

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new EmployeeTrainingPlanCollection($this->_templateobject));

	}	

	public function delete($modelName = null)
	{
		sendBack();
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		
		if(parent::save('EmployeeTrainingPlan'))
		{
			sendTo($this->name,'index',$this->_modules);
		}
		
		$this->refresh();

	}

	public function _new()
	{
		parent::_new();

		$flash = Flash::Instance();
		
		$employeeTrainingPlan = $this->_uses[$this->modeltype];
		
		if ($employeeTrainingPlan->isLoaded())
		{
			$employee_id = $employeeTrainingPlan->employee_id;
		}
		elseif($this->_data['employee_id'])
		{
			$employee_id = $this->_data['employee_id'];
		}
		else
		{
			$flash = Flash::Instance();
			$flash->addError('No employee selected');
			sendBack();
		}
		
		$employee = DataObjectFactory::Factory('Employee');
		
		$employee->load($employee_id);

		if (!$employee->isLoaded())
		{
			$flash->addError('Error loading employee details');
			sendBack();
		}
		
		if (!is_null($employee->finished_date) && $employee->finished_date < fix_date(date(DATE_FORMAT)))
		{
			$flash->addError('Employee has left');
			sendBack();
		}
		
		$this->view->set('employee', $employee);
		
		$collection = new EmployeeTrainingPlanCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($collection);
		
		$sh->addConstraint(new Constraint('employee_id', '=', $employee_id));
		
		parent::index($collection, $sh);
	}
	
}

// End of EmployeetrainingplansController

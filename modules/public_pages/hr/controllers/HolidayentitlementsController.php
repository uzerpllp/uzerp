<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HolidayentitlementsController extends Controller
{

	protected $version='$Revision: 1.8 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('Holidayentitlement');
		
		$this->uses($this->_templateobject);

	}

	public function index()
	{
		$this->view->set('clickaction', 'edit');
		
		parent::index(new HolidayentitlementCollection($this->_templateobject));
	}

	public function delete()
	{
		$flash = Flash::Instance();
		
		parent::delete($this->modeltype);
		
		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	}
	
	public function save()
	{
		$flash = Flash::Instance();
		
		$errors = array();
		
		$holidayEntitlement = $this->_templateobject;
		
		$overlap = $holidayEntitlement->overlap_entitlement($this->_data[$this->modeltype]);
	
		if($overlap)
		{
			$errors[] = 'Your start or end date overlaps with another entitlement';
		}
		elseif(parent::save($this->modeltype, '', $errors))
		{
			$employee = array('id' => $this->_data[$this->modeltype]['employee_id']);
			
			sendTo('employees', 'view', 'hr', $employee);
		}
		
		$flash->addErrors($errors);
		
		$this->refresh();
		
	}

	public function view()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$holidayEntitlement = $this->_uses[$this->modeltype];
		
		$employee = DataObjectFactory::Factory('Employee');
		
		$employee->load($holidayEntitlement->employee_id);
		
		$this->view->set('employee',$employee);
		
		$currently_viewing=$employee->employee.': '.$holidayEntitlement->start_date.'-'.$holidayEntitlement->end_date;
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'currently_viewing',
			array(
				$employee->employee=>array(
					'tag' => $currently_viewing,
					'link' => array('module'=>'hr','controller'=>'holidayentitlements','action'=>'view','id'=>$holidayEntitlement->id)),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array('module'=>'hr','controller'=>'holidayentitlements','action'=>'edit','id'=>$holidayEntitlement->id))));
		
		$sidebar->addList(
			'related_items',
			array(
				'holidayextraday'=>array(
					'tag'=>'Holiday Extra Days',
					'link'=>array('module'=>'hr','controller'=>'holidayextradays','action'=>'viewentitlement','entitlement_period_id'=>$holidayEntitlement->id),
					'new'=>array('module'=>'hr','controller'=>'holidayextradays','action'=>'new','entitlement_period_id'=>$holidayEntitlement->id,'employee_id'=>$holidayEntitlement->employee_id))));
		
			$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
	}
	
	public function _new()
	{
		parent::_new();
		
		$holidayEntitlement = $this->_uses[$this->modeltype];
		
		if ($holidayEntitlement->isLoaded())
		{
			$employee_id = $holidayEntitlement->employee_id;
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
		
		$this->view->set('employee', $employee);
		
		$collection = new HolidayentitlementCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($collection);
		
		$sh->addConstraint(new Constraint('employee_id', '=', $employee_id));
		
		parent::index($collection, $sh);
		
		if (!$holidayEntitlement->isLoaded())
		{
			$holidayEntitlement->start_date = $holidayEntitlement->getNextStartDate($employee_id);
		}
		
	}
	
}

// End of HolidayentitlementsController


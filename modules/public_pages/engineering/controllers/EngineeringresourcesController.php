<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EngineeringresourcesController extends Controller
{

	protected $version = '$Revision: 1.2 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('EngineeringResource');
		
		$this->uses($this->_templateobject);
	}

	public function index()
	{
		$s_data = null;
		
		$errors = array();

//		$this->setSearch('EngineeringSearch', 'resources', $s_data, $errors);

		$this->view->set('clickaction', 'edit');
		
		parent::index($pi = new EngineeringResourceCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'	=> 'new_engineering_resource'
							,'link'	=> array('modules'		=> $this->_modules
											,'controller'	=> $this->name
											,'action'		=> 'new')
					
							)
				)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete()
	{
		$flash = Flash::Instance();
		
		parent::delete($this->modeltype);
		
		sendBack();
	}
	
	public function _new()
	{
		
		parent::_new();
		
		$resource = $this->_uses[$this->modeltype];
		
		$db = DB::Instance();
		
		if ($resource->isLoaded())
		{
			$this->_data['work_schedule_id'] = $resource->work_schedule_id;
			$this->view->set('workschedule', $resource->work_schedule_detail);
		}
		elseif (isset($this->_data['work_schedule_id']))
		{
			$resource->work_schedule_id = $this->_data['work_schedule_id'];
			$this->view->set('workschedule', $resource->work_schedule_detail);
		}
		else
		{
			$workschedule = DataObjectFactory::Factory('WorkSchedule');
			
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint('status', '!=', $workschedule->completedStatus()));
			$cc->add(new Constraint('status', '!=', $workschedule->cancelledStatus()));
			
			$this->view->set('workschedules', $workschedule->getAll($cc));
		}
		
		$person = DataObjectFactory::Factory('Person');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('company_id', '=', COMPANY_ID));
		
		$this->view->set('people', $person->getAll($cc));
		
		$resources = new EngineeringResourceCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($resources);
		
		$sh->addConstraint(new Constraint('work_schedule_id', '=', $resource->work_schedule_id));
		
		parent::index($resources, $sh);
		
		$this->view->set('clickaction', 'edit');
		
	}
	
	public function save()
	{
		
		$flash = Flash::Instance();
		
		$errors = array();
		
		$data = $this->_data[$this->modeltype];

		if (!empty($data['person_id']) && $data['quantity'] > 1)
		{
			$errors['person_id'] = 'Quantity must be 1 for a person';
		}
		
		if(count($errors)==0 && parent::save($this->modeltype, '', $errors))
		{
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		else
		{
			$flash->addErrors($errors);
			$this->refresh();
		}
		
	}
	
	public function view()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$resource = $this->_uses[$this->modeltype];
		$this->view->set('model',$resource);
		
		$detail = DataObjectFactory::Factory('MFResource');
		
		$detail->load($resource->resource_id);
		
		$resource->setAdditional('resource_rate');
		$resource->resource_rate = $detail->resource_rate;
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'	=> array('tag'	=> 'new_project_resource'
								,'link'	=> array('modules'		=> $this->_modules
												,'controller'	=> $this->name
												,'action'		=> 'new')
					
				),
				'edit'	=> array('tag'	=> 'edit'
								,'link'	=> array('modules'		=> $this->_modules
												,'controller'	=> $this->name
												,'action'		=> 'edit'
												,'id'			=> $resource->id)
					
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
/* Ajax functions */
	
/* Protected Functions */
	protected function getPageName($base=null, $action=null)
	{
		return parent::getPageName((!empty($base))?$base:'engineering_resources', $action);
	}

/* Private Functions */

}

// End of EngineeringresourcesController

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class OpportunitysController extends Controller
{

	protected $version = '$Revision: 1.10 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('Opportunity');
		
		$this->uses($this->_templateobject);

	}

	public function index()
	{
		$this->view->set('clickaction', 'view');
		
		$s_data = array();
		$errors = array();

		$this->setSearch('OpportunitySearch', 'useDefault', $s_data);

		$ops = new OpportunityCollection($this->_templateobject);
		
		parent::index($ops);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'New Opportunity'
				),
				'summary'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'summary_report'),
					'tag'=>'summary_report'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function summary_report()
	{
		$users = array();
		
		if(isModuleAdmin())
		{
			$u = DataObjectFactory::Factory('User');
			
			$users = $u->getAll();
		}
		
		$this->view->set('users',$users);
		
		if(isset($this->_data['filter']))
		{
			$cc = new ConstraintChain();
			
			if(!empty($this->_data['from_date']))
			{
				$cc->add(new Constraint('enddate','>',fix_date($this->_data['from_date'])));
			}
			
			if(!empty($this->_data['to_date']))
			{
				$cc->add(new Constraint('enddate','<',fix_date($this->_data['to_date'])));
			}
			
			if(!isModuleAdmin())
			{
				$cc->add(new Constraint('assigned','='.EGS_USERNAME));
			}	
			elseif(!empty($this->_data['assigned']))
			{
				$cc->add(new Constraint('assigned','=',$this->_data['assigned']));
			}
			
			$opp_sh = new SearchHandler(new OpportunityCollection($this->_templateobject),false);
			
			$opp_sh->addConstraintChain($cc);
			
			$opp_sh->extract();
			
			$os = DataObjectFactory::Factory('Opportunitystatus');
			
			$os->addSearchHandler('opportunities',$opp_sh);
			
			$statuses = new OpportunitystatusCollection($os);
			
			$sh = new SearchHandler($statuses,false);
			
			$sh->extract();
			
			$statuses->load($sh);
			
			$this->view->set('statuses',$statuses);
			
			$this->view->set('report_headings',array('name','company','person','enddate','type','cost','assigned'));
			
			$this->view->set('cc',$cc);
		}
	}

	public function delete()
	{
		$flash = Flash::Instance();
		
		parent::delete('Opportunity');
		
		sendTo('opportunitys','index',array('crm'));
	}

	public function save()
	{
		$flash = Flash::Instance();
	
		if(parent::save('Opportunity'))
		{
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		else
		{
			$this->refresh();
		}

	}

	public function _new()
	{
		if(isset($this->_data['person_id']))
		{
			$person = DataObjectFactory::Factory('Person');
			
			$person->load($this->_data['person_id']);
			
			$_GET['company_id']=$person->company_id;
		}

		if(isset($this->_data['company_id'])) {
			$people = new Person();
			$cc = new ConstraintChain;
			$cc->add(new Constraint('company_id', '=', $this->_data['company_id']));
			$this->view->set('people', $people->getAll($cc));
		}
		
		parent::_new();
	}

	public function view()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$opportunity = $this->_uses['Opportunity'];
		
		$opportunity_id = $opportunity->id;
		
		if(!$opportunity->isLoaded())
		{
			$flash=Flash::Instance();
			
			$flash->addError('Invalid Opportunity');
			
			sendBack();
		}
		
		$sidebar = new SidebarController($this->view);
		
		$cur_view =	array(
			$opportunity->name => array(
				'tag'	=> $opportunity->name,
				'link'	=> array('modules'		=> $this->_modules
								,'controller'	=> $this->name
								,'action'		=> 'view','id'=>$opportunity_id)
			),
			'edit' => array(
				'tag'	=> 'Edit',
				'link'	=> array('modules'		=> $this->_modules
								,'controller'	=> $this->name
								,'action'		=> 'edit','id'=>$opportunity_id)
			),
			'delete' => array(
				'tag'	=> 'Delete',
				'link'	=> array('modules'		=> $this->_modules
								,'controller'	=> $this->name
								,'action'		=> 'delete','id'=>$opportunity_id)
			)
		);

		if ($opportunity->projects->count() == 0)
		{
			$cur_view['convert_to_project'] = array(
				'tag'	=> 'Convert to Project',
				'link'	=> array('module'			=> 'projects'
								,'controller'		=> 'projects'
								,'action'			=> 'opportunitytoproject'
								,'opportunity_id'	=> $opportunity_id)
			);
		}
		else
		{
			foreach ($opportunity->projects as $project)
			{
					$cur_view['view_project'.$project->id] = array(
					'tag'	=> 'View Project '.$project->name,
					'link'	=> array('module'		=> 'projects'
									,'controller'	=> 'projects'
									,'action'		=> 'view'
									,'id'			=> $project->id)
				);
			}
		}
		
		$sidebar->addList(
			'currently_viewing',
			$cur_view
		);

		$this->sidebarRelatedItems($sidebar, $opportunity);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function viewprojects ()
	{

		$project = DataObjectFactory::Factory('Project');
		
		$projects = new ProjectCollection($project);
		
		$sh = $this->setSearchHandler($projects);
		
		$sh->addConstraint(new Constraint('opportunity_id', '=', $this->_data['id']));
		
		parent::index($projects, $sh);
		
	}
	
}

// End of OpportunitysController

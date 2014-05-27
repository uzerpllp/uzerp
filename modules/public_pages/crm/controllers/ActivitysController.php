<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ActivitysController extends Controller
{

	protected $version='$Revision: 1.6 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('Activity');
		
		$this->uses($this->_templateobject);
		
	}

	public function index()
	{
	
		$this->setSearch('ActivitySearch', 'useDefault');
		
		$this->view->set('clickaction', 'view');
		
		parent::index($a=new ActivityCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('link' => array('modules'		=> $this->_modules
											,'controller'	=> $this->name
											,'action'		=> 'new')
							,'tag' => 'New Activity'
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
		
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	}
	
	public function _new()
	{
		if(isset($this->_data['opportunity_id']))
		{
			$opportunity = DataObjectFactory::Factory('Opportunity');
			
			$opportunity->load($this->_data['opportunity_id']);
			
			$this->_data['person_id']=$opportunity->person_id;
			
			$this->_data['company_id']=$opportunity->company_id;
		}
		
		parent::_new();
	}
	
	public function complete()
	{
		if (isset($this->_data['id']))
		{
			$activity = $this->_uses['Activity'];
			
			if ($activity->load($this->_data['id']))
			{
				$activity->completed = date('Y-m-d');
				
				$activity->save();
			}
		}
		
		sendBack();
	}
	
	public function save()
	{
		$flash=Flash::Instance();
		
		if(parent::save('Activity'))
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

	public function view()
	{
		if (!$this->loadData())
		{
			sendBack();
		}
		
		$activity = $this->_uses['Activity'];
		
		$this->view->set('activity', $activity);
		
		$sidebar=new SidebarController($this->view);
		
		$sidebar->addList(
			'currently_viewing',
			array(
				$activity->name => array('tag'	=> $activity->name
										,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'view'
														,'id'			=> $activity->id)
				),
				'edit' => array('tag'	=> 'Edit'
							   ,'link'	=> array('modules'		=> $this->_modules
												,'controller'	=> $this->name
												,'action'		=> 'edit'
							   					,'id'			=> $activity->id)
				),
				'delete' => array('tag'		=> 'Delete'
								 ,'link'	=> array('modules'		=> $this->_modules
													,'controller'	=> $this->name
													,'action'		=> 'delete'
								 					,'id'			=> $activity->id)
				),
				'mark_as_completed' => array('tag'	=> 'Mark as completed'
											,'link'	=> array('modules'		=> $this->_modules
															,'controller'	=> $this->name
															,'action'		=> 'complete'
															,'id'			=> $activity->id)
				)
			)
		);
		
		$sidebar->addList(
			'related_items',
			array(
				'notes'=>array('tag'=>'Notes'
							  ,'link'	=> array('modules'		=> $this->_modules
												,'controller'	=> 'activitynotes'
							  					,'action'		=> 'viewactivity'
							  					,'activity_id'	=> $activity->id)
							  ,'new'	=> array('modules'		=> $this->_modules
												,'controller'	=> 'activitynotes'
							  					,'action'		=> 'new'
							  					,'activity_id'	=> $activity->id)
				),
				'spacer',
				'attachments' => array('tag'=>'Attachments'
									  ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> 'activityattachments'
									  					,'action'		=> 'index'
									  					,'activities_id'=> $activity->id)
									  ,'new'	=> array('modules'		=> $this->_modules
														,'controller'	=> 'activityattachments'
									  					,'action'		=> 'new'
									  					,'data_module'	=> 'activity'
									  					,'entity_id'	=> $activity->id)
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
}

// End of ActivitysController

<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectnotesController extends Controller {

	protected $version = '$Revision: 1.1 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = new ProjectNote();
		$this->uses($this->_templateobject);
	}

	public function _new()
	{
		
		$flash = Flash::Instance();
		
		// ensure that a project id is specified for new notes
		if ($this->_data['action'] === 'new' && (!isset($this->_data['project_id']) || empty($this->_data['project_id'])))
		{
			$flash->addError('No project id specified');
			sendBack();			
		}
		
		parent::_new();
		
		// load either a new project or the current note model to get the project name and id
		if ($this->_data['action'] === 'new')
		{
			
			$project = new Project();
			$project->load($this->_data['project_id']);
			
			$project_name	= $project->name;
			$project_id		= $project->id;
			
		}
		else
		{
			
			$model = $this->_uses[$this->modeltype];
			
			$project_name	= $model->project;
			$project_id		= $model->project_id;
			
		}
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Project',
			array(
				'view_project' => array(
					'tag'	=> $project_name,
					'link'	=> array(
						'module'		=> 'projects',
						'controller'	=> 'projects',
						'action'		=> 'view',
						'id'			=> $project_id
					)
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	public function view()
	{
		
		$flash = Flash::Instance();
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$model = $this->_uses[$this->modeltype];
		$this->view->set('model', $model);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Project',
			array(
				'view_project' => array(
					'tag'	=> $model->project,
					'link'	=> array(
						'module'		=> 'projects',
						'controller'	=> 'projects',
						'action'		=> 'view',
						'id'			=> $model->project_id
					)
				)
			)
		);
		
		$note_sidebar['edit_note'] = array(
			'tag'	=> 'Edit Note',
			'link'	=> array(
				'module'		=> 'projects',
				'controller'	=> 'projectnotes',
				'action'		=> 'edit',
				'id'			=> $model->id
			)
		);
		
		// only show delete if the current user is the note owner
		if (EGS_USERNAME === $model->owner)
		{
			$note_sidebar['delete_note'] = array(
				'tag'	=> 'Delete Note',
				'link'	=> array(
					'module'		=> 'projects',
					'controller'	=> 'projectnotes',
					'action'		=> 'delete',
					'id'			=> $model->id,
					'project_id'	=> $model->project_id
				)
			);
			
		}
		
		$sidebar->addList(
			'Note',
			$note_sidebar
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		
		if (parent::save('ProjectNote'))
		{
			
			sendTo(
				'projectnotes',
				'view',
				array('projects'),
				array('id' => $this->saved_model->id)
			);
			
		}
		else
		{
			$this->_new();
			$this->_templateName = $this->getTemplateName('new');
		}
		
	}
	
	public function delete($modelName = null)
	{
		
		parent::delete('ProjectNote');
		
		if (isset($this->_data['project_id']))
		{
			
			sendTo(
				'projects',
				'view',
				array('projects'),
				array('id' => $this->_data['project_id'])
			);
		
		}
		else
		{
			
			sendTo(
				'projects',
				'index',
				array('projects'),
				array('a' => 'b')
			);
			
		}
		
	}
	
}

// end of ProjectsnotesController.php
<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectissuesController extends Controller {

	protected $version = '$Revision: 1.4 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = new ProjectIssueHeader();
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		
		$s_data = null;
		$errors = array();
		
		if (isset($this->_data['Search'])) 
		{
			$s_data = $this->_data['Search'];
		}
		
		$this->search = ProjectSearch::issues($s_data, $errors);
		
		if (count($errors) > 0)
		{
			$flash = Flash::Instance();
			$flash->addErrors($errors);
			$this->search->clear();
		}
		
		$this->view->set('clickaction', 'edit');
		
		parent::index($pi = new ProjectIssueCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new' => array(
					'link' => array(
						'module'		=> 'projects',
						'controller'	=> 'ProjectIssues',
						'action'		=> 'new'
					),
					'tag' => 'new_project_Issue'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}

	public function delete($modelName = null)
	{
		parent::delete('ProjectIssue');
		sendBack();
	}
	
	public function view()
	{
		
		$flash = Flash::Instance();
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$header = $this->_uses[$this->modeltype];
		
		if ($header === false)
		{
			sendBack();
		}
		
		$this->view->set('issue_lines', $header->lines);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Project',
			array(
				'view_project' => array(
					'tag'	=> $header->project,
					'link'	=> array(
						'module'		=> 'projects',
						'controller'	=> 'projects',
						'action'		=> 'view',
						'id'			=> $header->project_id
					)
				)
			)
		);
		
		$sidebar->addList(
			'Actions',
			array(
				'edit' => array(
					'link' => array(
						'module'		=> 'projects',
						'controller'	=> 'ProjectIssues',
						'action'		=> 'edit',
						'id'			=> $header->id
					),
					'tag' => 'Edit Issue Header'
				),
				'add_line' => array(
					'link' => array(
						'module'		=> 'projects',
						'controller'	=> 'ProjectIssueLines',
						'action'		=> 'new',
						'header_id'		=> $header->id
					),
					'tag' => 'New Issue Line'
				),
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		
		$flash = Flash::Instance();
		
		if (parent::save('ProjectIssueHeader'))
		{
			
			sendTo(
				'projectissues',
				'view',
				array('projects'),
				array('id' => $this->saved_model->id)
			);
			
		}
		else
		{
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}
		
	}
	
	protected function getPageName()
	{
		return parent::getPageName('project_issues');
	}
	
}

// end of ProjectissuesController.php
<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectissuelinesController extends Controller {

	protected $version = '$Revision: 1.1 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = new ProjectIssueLine();
		$this->uses($this->_templateobject);
	}

	public function delete()
	{
		parent::delete('ProjectIssueLine');
		sendBack();
	}
	
	public function _new()
	{
		
		parent::_new();
		
		$header_id = FALSE;
		
		
		if ($this->_data['action'] === 'new')
		{
			
			$header = new ProjectIssueHeader();
			$header->load($this->_data['header_id']);
			
			$header_id		= $header->id;
			$header_title	= $header->title;
			
		}
		else
		{
			
			$line = $this->_uses[$this->modeltype];
			$line->load($this->_data['id']);
			
			$header_id		= $line->header_id;
			$header_title	= $line->header->title;
			
		}
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Issue',
			array(
				'view_issue' => array(
					'tag'	=> $header_title,
					'link'	=> array(
						'module'		=> 'projects',
						'controller'	=> 'projectissues',
						'action'		=> 'view',
						'id'			=> $header_id
					)
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	public function save()
	{
		
		if (parent::save('ProjectIssueLine'))
		{
			
			sendTo(
				'projectissues',
				'view',
				array('projects'),
				array('id' => $this->saved_model->header_id)
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
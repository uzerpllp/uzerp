<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintcodesController extends Controller
{

	protected $version = '$Revision: 1.8 $';
	
	protected $_templateobject;

	public function __construct($module = NULL, $action = NULL)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('ComplaintCode');
		
		$this->uses($this->_templateobject);
		
	}
	
	public function index()
	{

		$this->view->set('clickaction', 'edit');
		
		parent::index(new ComplaintCodeCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new' => array(
					'link' => array(
						'module'		=> $this->_modules['module'],
						'controller'	=> $this->name,
						'action'		=> 'new'
					),
					'tag' => 'New Complaint Code'
				),
				'view_scc' => array(
					'link' => array(
						'module'		=> $this->_modules['module'],
						'controller'	=> 'Supplementarycomplaintcodes',
						'action'		=> 'index'
					),
					'tag' => 'View Supplementary Complaint Codes'
				)
			)
		);

		$this->view->register('sidebar', $sidebar);
		
		$this->view->set('sidebar', $sidebar);
		
	}

	public function delete()
	{
		
		$flash = Flash::Instance();
		
		parent::delete($this->modeltype);
		
		sendTo($this->name, 'index', $this->_modules);
		
	}
	
	public function save()
	{
		
		$flash=Flash::Instance();
	
		if (parent::save($this->modeltype))
		{
			sendTo($this->name, 'index', $this->_modules);
		}
		
		$this->refresh();

	}
	
	public function _new()
	{
		
		parent::_new();
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new' => array(
					'link' => array(
						'module'		=> $this->_modules['module'],
						'controller'	=> $this->name,
						'action'		=> 'index'
					),
					'tag' => 'Return to Complaint Codes'
				)
			)
		);
	
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	protected function getPageName($base = NULL, $type = NULL)
	{
		return parent::getPageName((empty($base) ? 'complaint codes' : $base), $type);
	}

}

// end of ComplaintcodesController.php
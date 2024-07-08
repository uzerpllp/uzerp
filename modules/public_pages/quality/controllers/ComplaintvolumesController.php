<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintvolumesController extends Controller
{

	protected $version = '$Revision: 1.6 $';
	
	protected $_templateobject;

	public function __construct($module = NULL, $action = NULL)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('ComplaintVolume');
		
		$this->uses($this->_templateobject);
		
	}
	
	public function index($collection = null, $sh = '', &$c_query = null)
	{
		
		// Click action index to prevent future editing
		$this->view->set('clickaction', 'index');
		$this->view->set('orderby', 'year');

		parent::index(new ComplaintVolumeCollection($this->_templateobject));

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
					'tag' => 'Add New Sales Data'
				)
			)
		);
	
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		
		$flash = Flash::Instance();
		
		if (parent::save('ComplaintVolume'))
		{
			sendTo($this->name, 'index', $this->_modules);
		}
		
		$this->refresh();
		
	}

	protected function getPageName($base = NULL, $action = NULL)
	{
		return parent::getPageName('Complaint Volume');
	}
	
}

// end of ComplaintvolumesController.php

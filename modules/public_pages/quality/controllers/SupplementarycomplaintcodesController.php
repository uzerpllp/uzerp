<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SupplementarycomplaintcodesController extends Controller
{

	protected $version = '$Revision: 1.8 $';

	protected $_templateobject;

	public function __construct($module = NULL, $action = NULL)
	{

		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('SupplementaryComplaintCode');

		$this->uses($this->_templateobject);

	}

	 public function index($collection = null, $sh = '', &$c_query = null)
	 {

		global $smarty, $module, $submodule;

		$this->view->set('clickaction', 'edit');

		$this->view->set('orderby', 'code');

		parent::index(new SupplementaryComplaintCodeCollection($this->_templateobject));

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
					'tag' => 'New Supplementary Complaint Code'
				),
				'view_cc' => array(
					'link' => array(
						'module'		=> $this->_modules['module'],
						'controller'	=> 'Complaintcodes',
						'action'		=> 'index'
					),
					'tag' => 'View Complaint Codes'
				)
			)
		);

		$this->view->register('sidebar', $sidebar);

		$this->view->set('sidebar', $sidebar);

	}

	public function delete($modelName = null)
	{

		$flash = Flash::Instance();

		parent::delete($this->modeltype);

		sendTo($this->name, 'index', $this->_modules);

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		$flash = Flash::Instance();

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
					'tag' => 'Return to Supplementary Complaint Codes'
				)
			)
		);

		$this->view->register('sidebar', $sidebar);

		$this->view->set('sidebar', $sidebar);

	}

	protected function getPageName($base = NULL, $type = NULL)
	{
		return parent::getPageName((empty($base) ? 'supplementary complaint codes' : $base), $type);
	}

}

// end of SupplementarycomplaintcodesController.php
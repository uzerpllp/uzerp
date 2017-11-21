<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class SocostsController extends Controller{

	protected $version = '$Revision: 1.9 $';
	protected $_templateobject;

	public function __construct($module = NULL, $action = NULL)	{
		parent::__construct($module, $action);
		$this->_templateobject = DataObjectFactory::Factory('SOCost');
		$this->uses($this->_templateobject);

	}

	 public function index() {
		//global $smarty, $module, $submodule;
		$this->view->set('clickaction', 'edit');

		//$this->view->set('orderby', 'code');

		parent::index(new SOCostCollection($this->_templateobject));

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
					'tag' => 'New Product Cost'
				)
			)
		);

		$this->view->register('sidebar', $sidebar);

		$this->view->set('sidebar', $sidebar);

	}

	public function delete() {
		$flash = Flash::Instance();
		parent::delete($this->modeltype);
		sendTo($this->name, 'index', $this->_modules);

	}

	public function save() {
		$flash = Flash::Instance();
		//totals cost = sum of material,labour, outside contract and overhead
		$this->_data[$this->modeltype]['cost']=$this->_data[$this->modeltype]['mat']
																					+$this->_data[$this->modeltype]['lab']
																					+$this->_data[$this->modeltype]['osc']
																					+$this->_data[$this->modeltype]['ohd'];
		if (parent::save($this->modeltype))
		{

			sendTo($this->name, 'index', $this->_modules);
		}
		$this->refresh();

	}

	public function _new() {
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
					'tag' => 'Return to Product Costs'
				)
			)
		);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		$this->view->set('soprods', $this->getUnassignedProducts());

	}

	protected function getPageName($base = NULL, $type = NULL) {
		return parent::getPageName((empty($base) ? 'product costs' : $base), $type);
	}

	// Get SO product line headers that are NOT linked to items
	public function getUnassignedProducts(){
			$soproducts= DataObjectFactory::Factory('SOProductlineHeader');
			$cc=new ConstraintChain();
			$cc->add(new Constraint('stitem_id', 'is', 'NULL'));
			// exclude product headers with costs already defined
			$cc->add(new Constraint('soc_id', 'is', 'NULL'));
			return $soproducts->getAll($cc,false,true);
	}

}

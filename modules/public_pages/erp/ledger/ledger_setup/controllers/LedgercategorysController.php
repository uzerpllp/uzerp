<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class LedgercategorysController extends LedgerController
{

	protected $version='$Revision: 1.1 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{

		parent::__construct($module, $action);

		$this->_templateobject = new LedgerCategory();

		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$this->view->set('clickaction', 'view');

		parent::index(new LedgerCategoryCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);

		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 ),
					'tag'=>'new_ledger_category'
				)
			)
		);

		$this->view->register('sidebar',$sidebar);

		$this->view->set('sidebar',$sidebar);

	}

	public function _new()
	{

		parent::_new();

		$ledgercategory=$this->_uses[$this->modeltype];

		if (!$ledgercategory->isLoaded())
		{
			$current = $ledgercategory->getAll();

			$categories = new Contactcategory();

			$cc = new ConstraintChain();
			if (count($current) > 0)
			{
				$cc->add(new Constraint('id', 'not in', '('.implode(',', $current).')'));
			}

			$this->view->set('categories',$categories->getAll($cc));
		}

	}

	public function view()
	{

		if (!$this->loadData()) {
			sendback();
		}

		$ledgercategory=$this->_uses[$this->modeltype];

		$idfield = $ledgercategory->idField;
		$id		 = $ledgercategory->$idfield;

		// Check if Ledger Category is used by Ledger Accounts
		$ledgeraccount	= new LedgerCategory('ledger_category_accounts');
		$ledgeraccount->idField			= 'company_id';
		$ledgeraccount->identifierField	= 'name';

		$cc = new ConstraintChain();
		$cc->add(new Constraint('category_id', '=', $ledgercategory->category_id));

		$ledgeraccounts = $ledgeraccount->getAll($cc);

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['all'] = array('link'=>array('modules'=>$this->_modules
											,'controller'=>$this->name
											,'action'=>'index'
											)
							  ,'tag'=>'view_ledger_categories'
							  );

		$sidebarlist['new'] = array('link'=>array('modules'=>$this->_modules
											,'controller'=>$this->name
											,'action'=>'new'
											)
							  ,'tag'=>'new_ledger_category'
							  );

		if (count($ledgeraccounts) == 0)
		{
			$sidebarlist['edit'] = array('link'=>array('modules'=>$this->_modules
												,'controller'=>$this->name
												,'action'=>'edit'
												,$idfield=>$ledgercategory->$idfield
												)
								  ,'tag'=>'edit_ledger_category'
								  );

			$sidebarlist['delete'] = array('link'=>array('modules'=>$this->_modules
												,'controller'=>$this->name
												,'action'=>'delete'
												,$idfield=>$ledgercategory->$idfield
												)
								  ,'tag'=>'delete_ledger_category'
								  );
		}

		$sidebar->addList(
			'Actions',
			$sidebarlist
		);

		$this->view->register('sidebar',$sidebar);

		$this->view->set('sidebar',$sidebar);

		$this->view->set('model', $ledgercategory);
		$this->view->set('count', count($ledgeraccounts));

	}

	/*
	 * Protected Functions
	 */
	protected function getPageName($base=null,$type=null)
	{
		return parent::getPageName((empty($base)?'ledger_categories':$base),$type);
	}

}

// End of LedgercategorysController

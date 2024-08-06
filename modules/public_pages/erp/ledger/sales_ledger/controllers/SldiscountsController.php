<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SldiscountsController extends LedgerController {

	protected $version='$Revision: 1.10 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new SLDiscount();
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null){

// Search
		$errors=array();
		$s_data=array();

		$this->setSearch('SLDiscountSearch', 'useDefault', $s_data);
// End of search

		$this->view->set('clickaction', 'edit');
		$this->view->set('orderby', 'id');

		parent::index(new SLDiscountCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								,'controller'=>$this->name
								,'action'=>'new'),
					'tag'=>'Add New Discount'
				)
			)
		);


		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new() {

		parent::_new();

		$sldiscount = $this->_uses[$this->modeltype];

		if (!$sldiscount->isLoaded() && isset($this->_data['slmaster_id']))
		{
			$sldiscount->slmaster_id = $this->_data['slmaster_id'];
		}

		$customers=$this->getOptions($this->_templateobject, 'slmaster_id', 'getOptions', 'getOptions', array('use_collection'=>true));
		$this->view->set('customers', $customers);

		$this->view->set('prod_groups', $this->getProductGroups($sldiscount->slmaster_id));
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {

// set context for 'Save and Add Another'
		if (isset($this->_data[$this->modeltype]['slmaster_id']))
		{
			$this->context=array('slmaster_id'=>$this->_data[$this->modeltype]['slmaster_id']);
		}
		else
		{
			$this->context=array('slmaster_id'=>'');
		}

		parent::save();

	}

/*
 * Protected functions
 */
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'sales_ledger_discounts':$base), $action);
	}	


/*
 * Ajax functions
 */
	public function getProductGroups($_slmaster_id = '')
	{
// Used by Ajax to return Email Addresses after selecting the Supplier
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['slmaster_id'])) { $_slmaster_id=$this->_data['slmaster_id']; }
		}

		$prod_groups = SLDiscount::unassignedProductGroups($_slmaster_id);

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$prod_groups);
			$this->setTemplateName('select_options');
		} else {
			return $prod_groups;
		}
	}
}

// End of SldiscountsController

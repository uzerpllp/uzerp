<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PaymenttermsController extends LedgerController
{

	protected $version = '$Revision: 1.12 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('PaymentTerm');
		
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$this->view->set('clickaction', 'view');
		
		parent::index(new PaymentTermCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new' => array(
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'new'
									),
					'tag'	=> 'new_PaymentTerm'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new()
	{
		parent::_new();
		
		$payterm = $this->_uses[$this->modeltype];
		
		$accounts = $this->check_discount($payterm->discount, ($payterm->allow_discount_on_allocation=='t'?'true':'false'));
		
		$this->view->set('pl_discount_glaccounts', $accounts['pl_discount_glaccount_id']['data']);
		$this->view->set('sl_discount_glaccounts', $accounts['sl_discount_glaccount_id']['data']);
		
		$centres = array('' => 'None');
		
		if ($payterm->isLoaded())
		{
			if (is_null($payterm->pl_discount_glaccount_id))
			{
				$payterm->pl_discount_glaccount_id = key($accounts['pl_discount_glaccount_id']['data']);
			}
			
			if (is_null($payterm->sl_discount_glaccount_id))
			{
				$payterm->sl_discount_glaccount_id = key($accounts['sl_discount_glaccount_id']['data']);
			}
			
			$this->view->set('pl_discount_glcentres', $this->getCentres($payterm->pl_discount_glaccount_id));
			$this->view->set('sl_discount_glcentres', $this->getCentres($payterm->sl_discount_glaccount_id));
		}
		else
		{
			$this->view->set('pl_discount_glcentres', $centres);
			$this->view->set('sl_discount_glcentres', $centres);
		}
		
	}
	
	public function check_discount($_discount = 0, $_allocate = 'false')
	{
		// Used by Ajax to return GL Accounts lists
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['discount'])) { $_discount = $this->_data['discount']; }
			if(!empty($this->_data['allocate'])) { $_allocate = $this->_data['allocate']; }
		}
		
		// store the ajax status in a different var, then unset the current one
		// we do this because we don't want the functions we all to get confused
		$ajax = isset($this->_data['ajax']);
		unset($this->_data['ajax']);
		
		if ($_discount > 0 || $_allocate=='true')
		{
			$glaccount = DataObjectFactory::Factory('GLAccount');
			
			$glaccounts = $glaccount->getAll();
		}
		else
		{
			$glaccounts = array('' => 'None');
		}
		
		$output['pl_discount_glaccount_id'] = array('data'=>$glaccounts,'is_array'=>is_array($glaccounts));
		$output['sl_discount_glaccount_id'] = array('data'=>$glaccounts,'is_array'=>is_array($glaccounts));
		
		// return formatted html if an ajax call, otherwise return the data array
		if($ajax)
		{
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		}
		else
		{
			return $output;
		}
		
	}

	public function getCentres($_id = '')
	{
// Used by Ajax to return Centre list after selecting the Account
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id = $this->_data['id']; }
		}
		
		if(!empty($_id))
		{
			$account = DataObjectFactory::Factory('GLAccount');
			$account->load($_id);
			$centres = $account->getCentres();
		}
		else
		{
			$centres = array(''=>'None');
		}
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('options', $centres);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $centres;
		}
	}

}

// End of PaymenttermsController


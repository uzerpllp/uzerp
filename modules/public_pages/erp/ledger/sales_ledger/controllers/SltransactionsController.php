<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SltransactionsController extends printController
{

	protected $version = '$Revision: 1.17 $';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('SLTransaction');
		
		$this->uses($this->_templateobject);

	}
	
	public function index()
	{
		$this->view->set('clickaction', 'view');
		
		$s_data = array();

// Set context from calling module
		if (isset($this->_data['slmaster_id']))
		{
			$s_data['slmaster_id'] = $this->_data['slmaster_id'];
		}
		
		if (isset($this->_data['status']))
		{
			$s_data['status'] = $this->_data['status'];
		}

		$this->setSearch('sltransactionsSearch', 'useDefault', $s_data);

		$transaction_date=$this->search->getValue('transaction_date');
		
		if (isset($transaction_date['from']))
		{
			$from_date = fix_date($transaction_date['from']);
		}
		else
		{
			$from_date = '';
		}
		
		if (isset($transaction_date['to']))
		{
			$to_date = fix_date($transaction_date['to']);
		}
		else
		{
			$to_date = '';
		}
		
		parent::index(new SLTransactionCollection($this->_templateobject));
		
		$this->view->set('search',$s_data);
	}

	public function view()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$transaction = $this->_uses[$this->modeltype];

		$this->view->set('transaction',$transaction);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['viewaccounts'] = array(
							'tag'	=> 'View All Customers',
							'link'	=> array('modules'		=> $this->_modules
											,'controller'	=> 'slcustomers'
											,'action'		=> 'index'
											)
				);
		
		$sidebarlist['gldetail'] = array(
							'tag'	=> 'View GL Detail',
							'link'	=> array('module'		=> 'general_ledger'
											,'controller'	=> 'gltransactions'
											,'action'		=> 'index'
											,'docref'		=> $transaction->our_reference
											,'source'		=> 'S'
											,'glperiods_id'	=> '0'
											)
				);
		
		$sidebar->addList('Actions',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function view_allocations ()
	{
		
		$flash = Flash::Instance();
		
		$collection = new SLTransactionCollection($this->_templateobject);
		
		$this->_templateobject->setAdditional('payment_value', 'numeric');
		
		$allocation = DataObjectFactory::Factory('SLAllocation');
		
		$allocationcollection = new SLAllocationCollection($allocation);
		
		$collection->_tablename = $allocationcollection->_tablename;
		
		$sh = $this->setSearchHandler($collection);
		
		$fields=array("our_reference||'-'||transaction_type as id"
					 ,'customer'
					 ,'slmaster_id'
					 ,'transaction_date'
					 ,'transaction_type'
					 ,'our_reference'
					 ,'ext_reference'
					 ,'currency'
					 ,'gross_value'
					 ,'allocation_date');
		
		$sh->setGroupBy($fields);
		
		$fields[] = 'sum(payment_value) as payment_value';
		
		$sh->setFields($fields);
		
		if (isset($this->_data['trans_id']))
		{
			$allocation->identifierField = 'allocation_id';
			
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint('transaction_id', '=', $this->_data['trans_id']));
			
			$alloc_ids = $allocation->getAll($cc);
			
			if (count($alloc_ids)>0)
			{
				$sh->addConstraint(new Constraint('allocation_id', 'in', '('.implode(',', $alloc_ids).')'));
			}
			else
			{
				$flash->addError('Error loading allocation');
				sendBack();
			}
		}
		parent::index($collection, $sh);
		
		$this->view->set('collection', $collection);
		$this->view->set('invoice_module', 'sales_invoicing');
		$this->view->set('invoice_controller', 'sinvoices');
		
		$this->view->set('clickaction', 'view');
		$this->view->set('clickcontroller', 'slcustomers');
		$this->view->set('linkvaluefield', 'slmaster_id');
	}
	
	/*
	 * Protected Functions
	 */
	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName((empty($base)?'sales_ledger_transactions':$base), $action);
	}
	
}

// End of SltransactionsController

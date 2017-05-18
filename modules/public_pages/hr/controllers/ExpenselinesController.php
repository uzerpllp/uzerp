<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ExpenselinesController extends Controller
{
	
	protected $version='$Revision: 1.5 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('ExpenseLine');
		
		$this->uses($this->_templateobject);
		
		$this->view->set('controller', 'Expenselines');
	}
	
	public function index()
	{

		$this->view->set('clickaction', 'view');
		
		parent::index(new ExpenseLineCollection($this->_templateobject));
		
	}
	
	public function delete()
	{
		if (empty($this->_data[$this->modeltype]['id']))
		{
			$this->dataError();
			sendBack();
		}
		
		$flash = Flash::Instance();
		
		$expenseline = $this->_uses[$this->modeltype];
		
		$expenseline->load($this->_data[$this->modeltype]['id']);
		
		if ($expenseline->isLoaded() && $expenseline->delete())
		{
			$flash->addMessage('Expense Line Deleted');
			
			if (isset($this->_data['dialog']))
			{
				$link=array('modules'	=> $this->_modules,
							'controller'=> 'expenses',
							'action'	=> 'view',
							'other'		=> array('id'=>$expenseline->expenses_header_id)
				);
				$flash->save();
				
				echo parent::returnJSONResponse(TRUE, array('redirect'=>'/?'.setParamsString($link)));
				exit;
			}
			else
			{
				sendTo('expenses', 'view', $this->_modules, array('id'=>$expenseline->expenses_header_id));
			}
		}
		
		$flash->addError('Error deleting Expense Line');
		
		$this->_data['id'] = $this->_data[$this->modeltype]['id'];
		
		$this->_data['expenses_header_id'] = $this->_data[$this->modeltype]['expenses_header_id'];
		
		$this->refresh();
	}

	public function _new()
	{
		$flash=Flash::Instance();
		
		parent::_new();
		
		if (isset($this->_data['ajax']))
		{
			// only reason for ajaxing an edit is for dialog display
			unset($this->_data['ajax']);
			
			$ajax = true;
			
			if (isset($this->_data['dialog']))
			{
				$this->view->set('dialog', true);
			}
		}
		
// Get the Order Line Object - if loaded, this is an edit
		$expenseline = $this->_uses[$this->modeltype];
		
		if (!$expenseline->isLoaded())
		{
			if (empty($this->_data['expenses_header_id']))
			{
				$flash->addError('No Expense Header supplied');
				sendBack();
			}
			$expenseline->expenses_header_id = $this->_data['expenses_header_id'];
			$this->view->set('awaitingAuth', TRUE);
		}
		else
		{
			$this->view->set('awaitingAuth', $expenseline->expense_header->awaitingAuthorisation());
		}
		
		$expense = DataObjectFactory::Factory('Expense');
		
		$expense->load($expenseline->expenses_header_id);
		
		$accounts = DataObjectFactory::Factory('GLAccount');
		$accounts = $accounts->getAll();
		$this->view->set('accounts',$accounts);
		
		if (isset($this->_data[$this->modeltype]))
		{
		// We've had an error so refresh the page
			$expenseline->line_number = $this->_data[$this->modeltype]['line_number'];
			
			$_glaccount_id = $this->_data[$this->modeltype]['glaccount_id'];
			
			$expenseline->currency = $expense->currency;
		}
		elseif ($expenseline->isLoaded())
		{
			$_glaccount_id = $expenseline->glaccount_id;
		}
		else
		{
			$expenseline->currency = $expense->currency;
			$this->view->set('line_number', $expense->getNextLineNumber());
			$_glaccount_id = key($accounts);
		}
		
		$this->view->set('centres', $this->getCentres($_glaccount_id));
		
		$tax_rates = DataObjectFactory::Factory('TaxRate');
		$tax_rates = $tax_rates->getAll();
		$this->view->set('taxrates',$tax_rates);
	
	}

	public function save()
	{
		$flash=Flash::Instance();
		
		$errors=array();
		
		if (!$this->checkParams(array($this->modeltype)))
		{
			sendBack();
		}

		$expenseline = ExpenseLine::Factory($this->_data[$this->modeltype], $errors, $this->modeltype);
		
		if ($expenseline && count($errors) == 0 && $expenseline->save())
		{
			$other = array('expenses_header_id' => $this->_data[$this->modeltype]['expenses_header_id']);
				
			if (isset($this->_data['saveAnother']))
			{
				sendTo($this->name, 'new', $this->_modules, $other);
			}
				
			sendTo('expenses', 'view', $this->_modules, $other);
		}
		else
		{
			if (!empty($this->_data[$this->modeltype]['id']))
			{
				$this->_data['id'] = $this->_data[$this->modeltype]['id'];
			}
			
			if (!empty($this->_data[$this->modeltype]['expenses_header_id']))
			{
				$this->_data['expenses_header_id'] = $this->_data[$this->modeltype]['expenses_header_id'];
			}
			
			$errors[] = 'Error adding Expense Line';
			
			$flash->addErrors($errors);
			
			$this->refresh();
		}
	}

	
// Protected Functions
	
	
// Private functions
	
	
/* Ajax functions */
	public function getCentres($_glaccount_id='')
	{
// Used by Ajax to return Centre list after selecting the Account

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['glaccount_id'])) { $_glaccount_id=$this->_data['glaccount_id']; }
		}
		
		$account = DataObjectFactory::Factory('GLAccount');
		$account->load($_glaccount_id);
		$centres = $account->getCentres();
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$centres);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $centres;
		}
		
	}

}

// End of ExpenselinesController

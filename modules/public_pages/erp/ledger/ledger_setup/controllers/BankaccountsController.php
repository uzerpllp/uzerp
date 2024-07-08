<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class BankaccountsController extends LedgerController
{

	protected $version = '$Revision: 1.12 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('CBAccount');
		
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$this->view->set('clickaction', 'edit');
		
		parent::index(new CBAccountCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new' => array(
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'new'
									),
					'tag'	=> 'new_bank_account'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new()
	{

		$flash = Flash::Instance();
		
		$errors = array();
		
		$glaccounts = DataObjectFactory::Factory('GLAccount');
		
		if ($glaccounts->getCount()==0)
		{
			$errors[] = 'No GL Accounts defined';
		}
		
		$glcentres = DataObjectFactory::Factory('GLCentre');
		
		if ($glcentres->getCount()==0)
		{
			$errors[] = 'No GL Centres defined';
		}
		
		$currency = DataObjectFactory::Factory('Currency');
		
		if ($currency->getCount()==0)
		{
			$errors[] = 'No Currencies defined';
		}
		
		if (count($errors)>0)
		{
			$flash->addErrors($errors);
			sendback();
		}
		
		parent::_new();

		$cbaccount = $this->_uses[$this->modeltype];

		if (!$cbaccount->isLoaded())
		{
			$params = DataObjectFactory::Factory('GLParams');
			
			$currency = $params->base_currency();
			
			$this->view->set('currency',$currency);
		}
		
		$glaccounts = $glaccounts->getAll();
		
		$this->view->set('glaccounts', $glaccounts);
	
		if (isset($_POST[$this->modeltype]['glaccount_id']))
		{
			$default_glaccount_id = $_POST[$this->modeltype]['glaccount_id'];
		}
		elseif ($cbaccount->isLoaded())
		{
			$default_glaccount_id = $cbaccount->glaccount_id;
		}
		else
		{
			$default_glaccount_id = key($glaccounts);
		}
		
		$this->view->set('glcentres',$this->getCentres($default_glaccount_id));
		
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}
		
		$flash = Flash::Instance();
		
		$errors = array();
		
		$db = DB::Instance();
		
		$db->StartTrans();
		
		if(parent::save_model($this->modeltype, $this->_data[$this->modeltype], $errors))
		{
			if (isset($this->_data[$this->modeltype]['primary_account']) && $this->_data[$this->modeltype]['primary_account']=='on')
			{
				$cbaccounts = new CBAccountCollection($this->_templateobject);
				
				$sh = new SearchHandler($cbaccounts, FALSE);
				
				$sh->addConstraint(new Constraint('id' ,'!=', $this->saved_model->id));
				
				if ($cbaccounts->update('primary_account', FALSE, $sh) === FALSE)
				{
					$errors[] = 'Error updating bank accounts';
				}
			}
			else
			{
				// Check that there is only one primary account
				$cc = new ConstraintChain();
				
				$cc->add(new Constraint('primary_account', 'is', TRUE));
				
				$count = $this->_templateobject->getCount($cc);
				
				if ($count == 0)
				{
					$flash->addWarning('No primary account defined');
				}
				elseif ($count > 1)
				{
					$flash->addWarning('Multiple primary accounts defined');
				}
			}
		}
		else
		{
			$errors[] = 'Error saving Bank Account details : '.$db->ErrorMsg();
		}
		
		if (count($errors) > 0)
		{
			$flash->addErrors($errors);
			$db->FailTrans();
		}
		
		if ($db->CompleteTrans())
		{
			sendTo($this->name
				  ,'index'
				  ,$this->_modules);
		}
		
		$this->refresh();
		
	}

}

// End of BankaccountsController

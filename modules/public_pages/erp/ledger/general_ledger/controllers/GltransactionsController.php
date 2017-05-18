<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GltransactionsController extends printController
{

	protected $version = '$Revision: 1.25 $';
	
	protected $_templateobject;

	protected $_header_model = 'GLTransactionHeader';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('GLTransaction');
		
		$this->uses($this->_templateobject);

	}

	public function index()
	{

		$id = (isset($this->_data['id']))?$this->_data['id']:0;
		
		$type = (isset($this->_data['transtype']))?$this->_data['transtype']:'';

		$errors = array();
	
		$defaults = array();
	
		if(!isset($this->_data['Search']))
		{
// set context from calling module if no search criteria set
			if (isset($this->_data['glperiods_id']))
			{
				$defaults['glperiods_id'] = array($this->_data['glperiods_id']);
			}
			
			if (isset($this->_data['docref']))
			{
				$defaults['docref'] = $this->_data['docref'];
			}
			
			if (isset($this->_data['source']))
			{
				$defaults['source'] = array($this->_data['source']);
			}
			
			if (isset($this->_data['type']))
			{
				$defaults['type'] = array($this->_data['type']);
			}
			
			if (isset($this->_data['glcentre_id']))
			{
				$defaults['glcentre_id'] = array($this->_data['glcentre_id']);
			}
			
			if (isset($this->_data['glaccount_id']))
			{
				$defaults['glaccount_id'] = array($this->_data['glaccount_id']);
			}
		}

		$this->setSearch('gltransactionsSearch', 'useDefault', $defaults);

		$this->view->set('clickaction', 'view');
		
		$gltransactions = new GLTransactionCollection($this->_templateobject);
		
		parent::index($gltransactions);
		
		$page_credit_total = 0;
		$page_debit_total = 0;
		
		foreach ($gltransactions->getArray() as $row)
		{
			$page_credit_total = bcadd($page_credit_total, $row['credit']);
			$page_debit_total = bcadd($page_debit_total, $row['debit']);
		}
		
		$this->view->set('page_total', number_format(bcsub($page_debit_total, $page_credit_total), 2));
		$this->view->set('page_credit_total', number_format($page_credit_total, 2));
		$this->view->set('page_debit_total', number_format($page_debit_total, 2));
		
		$sidebar = new SidebarController($this->view);
		$sidebarlist = array();
		
		$sidebarlist['viewaccounts'] = array(
					'tag'	=> 'View All Accounts',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> 'glaccounts'
									,'action'		=> 'index'
									)
					);
		
		$sidebarlist['viewcentres'] = array(
					'tag'	=> 'View All Centres',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> 'glcentres'
									,'action'		=> 'index'
									)
					);
		
		$sidebar->addList('Actions',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}
	
	public function delete()
	{
		$glheader = DataObjectFactory::Factory($this->_header_model);
		
		$unposted = $glheader->unpostedTransactionFactory();
		
		$transaction_model = get_class($unposted);
		
		if (!$this->checkParams(array($this->_header_model, $transaction_model)))
		{
			$this->dataError();
			sendBack();
		}
		
		if (parent::delete($unposted))
		{
			sendTo('gltransactionheaders', 'view', $this->_modules, array('id'=>$this->data[$this->_header_model]['id']));
		}
		
		$this->_data['id'] = $this->_data[$transaction_model]['id'];
		
		$this->refresh();
		
		
	}
	
	public function _new()
	{
		$glheader = DataObjectFactory::Factory($this->_header_model);
				
		$unposted = $glheader->unpostedTransactionFactory();
		
		$transaction_model = get_class($unposted);
		
		$this->_templateobject = DataObjectFactory::Factory($transaction_model);
		
		$this->uses($this->_templateobject);
		
		$this->loadData();
		
		$default_glaccount_id	= '';
		$default_glcentre_id	= '';
		
		if ($this->_templateobject->isLoaded())
		{
			$glheader->loadBy('docref', $this->_templateobject->docref);
			
			$default_glaccount_id	= $this->_templateobject->glaccount_id;
			$default_glcentre_id	= $this->_templateobject->glcentre_id;
		}
		elseif ($this->checkParams('header_id'))
		{
			$glheader->load($this->_data['header_id']);
		}
		
		if (empty($default_glaccount_id))
		{
			if (!empty($this->_data[$transaction_model]['glaccount_id']))
			{
				$default_glaccount_id = $this->_data[$transaction_model]['glaccount_id'];
			}
			elseif (!empty($this->_data['glaccount_id']))
			{
				$default_glaccount_id = $this->_data['glaccount_id'];
			}
		}
		
		if (empty($default_glcentre_id))
		{
			if (!empty($this->_data[$transaction_model]['glcentre_id']))
			{
				$default_glcentre_id = $this->_data[$transaction_model]['glcentre_id'];
			}
			elseif (!empty($this->_data['glcentre_id']))
			{
				$default_glcentre_id = $this->_data['glcentre_id'];
			}
		}
		
		$this->_templateName = $this->getTemplateName('new');
		
		if (!$glheader->isLoaded())
		{
			$this->dataError('Cannot find GL Header');
			sendBack();
		}
		
		$this->view->set('gltransaction_header', $glheader);
		
		parent::_new();
		
		$this->view->set('gltransaction', $this->_templateobject);

		$glaccount = DataObjectFactory::Factory('GLAccount');
		
		$accounts = $glaccount->nonControlAccounts();
		
		$this->view->set('accounts', $accounts);
		$this->view->set('default_account', $default_glaccount_id);
		
		if (empty($default_glaccount_id))
		{
			$default_glaccount_id = key($accounts);
		}
		
		$centres = $this->getCentres($default_glaccount_id);
		$this->view->set('centres', $centres);
		$this->view->set('default_centre', $default_glcentre_id);
		
	}
	
	/***
     * 	Altered save method to allow for unposted journal transactions to be saved.
     *  
	 */
	public function save()
	{
		$glheader = DataObjectFactory::Factory($this->_header_model);
		
		$unposted = $glheader->unpostedTransactionFactory();
		
		$transaction_model = get_class($unposted);
		
		if (!$this->checkParams(array($this->_header_model, $transaction_model)))
		{
			$this->dataError();
			sendBack();
		}
		
		if (!empty($this->_data[$this->_header_model]['id']))
		{
			$glheader->load($this->_data[$this->_header_model]['id']);
		}
		
		if (!$glheader->isLoaded())
		{
			$this->dataError('Error loading GL Transaction Header');
			sendback();
		}
		
		$flash = Flash::Instance();
		
		$errors = array();
		
		if (empty($this->_data[$transaction_model]['comment']))
		{
			$this->_data[$transaction_model]['comment'] = $glheader->comment;
		}
		
		if (empty($this->_data[$transaction_model]['reference']))
		{
			$this->_data[$transaction_model]['reference'] = $glheader->reference;
		}
		
		$debit	= $this->_data[$transaction_model]['debit'];
		$credit	= $this->_data[$transaction_model]['credit'];
		
		if($debit < 0 || $credit < 0)
		{
			$errors[] = 'Credit/Debit values cannot be negative';
		}
		elseif($debit == 0 && $credit == 0)
		{
			$errors[] = 'Can\'t enter a journal line without either a credit or a debit';
		}
		elseif($debit > 0 && $credit > 0)
		{
			$errors[] = 'A journal line cannot have both a credit and a debit';
		}
		
		$this->_data[$transaction_model]['source']	= 'G';
		$this->_data[$transaction_model]['type']	= 'J';
		
		$gltransaction = $unposted::Factory($this->_data[$transaction_model], $errors, $unposted);
		
		if (count($errors)===0 && $gltransaction && $gltransaction->save())
		{
			$flash->addMessage("GL Transaction Journal saved successfully");
			
			if (isset($this->_data['saveAnother']))
			{
				$other = array('header_id'		=> $this->_data[$this->_header_model]['id']
							  ,'glaccount_id'	=> $this->_data[$transaction_model]['glaccount_id']
							  ,'glcentre_id'	=> $this->_data[$transaction_model]['glcentre_id']);
				
				sendTo($this->name, 'new', $this->_modules, $other);
			}
			
			sendTo('gltransactionheaders', 'view', $this->_modules, array('id'=>$this->data[$this->_header_model]['id']));
			
		}
		else
		{
			$flash->addErrors($errors);
		}
		
		$this->_data['header_id']	= $this->_data[$this->_header_model]['id'];
		$this->_data['id']			= $this->_data[$transaction_model]['id'];
		
		$this->refresh();

	}

	/***
     *	The view function is used to generate an overview of a particular transaction.
     *
     */

	public function view()
	{

		if (!isset($this->_data) || !$this->loadData())
		{
			$this->dataError();
			sendBack();
		}	
		
		$transaction = $this->_uses[$this->modeltype];
		
		$id = $transaction->id;

		$this->view->set('transaction',$transaction);
		
		switch ($transaction->source.$transaction->type)
		{
			case 'CP':
			case 'CR':
				$linkmodule		= 'cashbook';
				$linkcontroller	= 'cbtransactions';
				$fklinkfield	= 'reference';
				break;
			case 'EE':
				$linkmodule		= 'hr';
				$linkcontroller	= 'expenses';
				$fklinkfield	= 'expense_number';
				break;
			case 'GJ':
				$linkmodule		= 'general_ledger';
				$linkcontroller	= 'gltransactionheaders';
				$fklinkfield	= 'docref';
				break;
				case 'PC':
			case 'PI':
				$linkmodule		= 'purchase_invoicing';
				$linkcontroller	= 'pinvoices';
				$fklinkfield	= 'invoice_number';
				break;
			case 'PJ':
			case 'PP':
				$linkmodule		= 'purchase_ledger';
				$linkcontroller	= 'pltransactions';
				$fklinkfield	= 'our_reference';
				break;
			case 'SC':
			case 'SI':
				$linkmodule		= 'sales_invoicing';
				$linkcontroller	= 'sinvoices';
				$fklinkfield	= 'invoice_number';
				break;
			case 'SJ':
			case 'SP':
				$linkmodule		= 'sales_ledger';
				$linkcontroller	= 'sltransactions';
				$fklinkfield	= 'our_reference';
				break;
		}

		$this->view->set('linkmodule', $linkmodule);
		$this->view->set('linkcontroller', $linkcontroller);
		$this->view->set('fklinkfield', $fklinkfield);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['viewaccounts'] = array(
					'tag'	=> 'View All Accounts',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> 'glaccounts'
									,'action'		=> 'index'
									)
					);
		$sidebarlist['viewcentres'] = array(
					'tag'	=> 'View All Centres',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> 'glcentres'
									,'action'		=> 'index'
									)
					);
		$sidebarlist['accountdetail'] = array(
					'tag'	=> 'View Account',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> 'glaccounts'
									,'action'		=> 'view'
									,'id'			=> $transaction->glaccount_id
									)
					);
		$sidebarlist['centredetail'] = array(
					'tag'	=> 'View Centre',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> 'glcentres'
									,'action'		=> 'view'
									,'id'			=> $transaction->glcentre_id
									)
					);

		switch($transaction->type) {
			case 'Credit Note':
				$sidebarlist['typedetail'] = array(
					'tag'	=> 'View '.$transaction->type,
					'link'	=> array('modules'			=> $this->_modules
									,'controller'		=> 'sinvoices'
									,'action'			=> 'index'
									,'invoice_number'	=> $transaction->docref
									)
					);
			case 'Invoice':
				$sidebarlist['typedetail'] = array(
					'tag'	=> 'View '.$transaction->type,
					'link'	=> array('modules'			=> $this->_modules
									,'controller'		=> 'sinvoices'
									,'action'			=> 'index'
									,'invoice_number'	=> $transaction->docref
									)
					);
					
		}
		$sidebar->addList('Actions',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}
	
	protected function getPageName($base = null, $type = null)
	{
		return parent::getPageName('general_ledger_transactions');
	}

	public function getAccounts()
	{
// Used by Ajax to return Account list after selecting the Centre
		$centre = DataObjectFactory::Factory('GLCentre');
		
		$centre->load($this->_data['id']);
		
		$accounts = $centre->getAccounts();
		
		echo json_encode($accounts);
		exit;
	}
	
	public function getCentres($_id='')
	{
// Used by Ajax to return Centre list after selecting the Account
		
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}
		
		$account = DataObjectFactory::Factory('GLAccount');
		
		$account->load($_id);
		
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
	
	public function getPeriods($_trandate='')
	{
// Used by Ajax to return Future Periods list after changing the transaction date

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_trandate=$this->_data['id']; }
		}
		
		$period = DataObjectFactory::Factory('GLPeriod');
		
		$current = $period->getPeriod(fix_date($_trandate));
		
		$periods = $period->getFuturePeriods($current['period'], $current['year']);
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$periods);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $periods;
		}
	}
	
	public function getPeriod($_trandate='')
	{

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_trandate=$this->_data['id']; }
		}
		
		$current = GLPeriod::getPeriod(fix_date($_trandate));
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$current['year'].' - period '.$current['period']);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $current['year'].' - period '.$current['period'];
		}

	}
	
}

// End of GltransactionsController

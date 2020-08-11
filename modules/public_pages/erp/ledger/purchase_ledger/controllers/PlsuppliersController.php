<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class PlsuppliersController extends LedgerController
{

	protected $version='$Revision: 1.102 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{

		parent::__construct($module, $action);

		$this->uses(DataObjectFactory::Factory('CBTransaction'));
		$this->uses(DataObjectFactory::Factory('PLTransaction'));

		$this->_templateobject = DataObjectFactory::Factory('PLSupplier');
		$this->uses($this->_templateobject, true);

	}

	public function index()
	{
		// Search
		$errors=array();
		$s_data=array();

// Set context from calling module
		$s_data['name']='';
		$s_data['currency_id']='';
		$s_data['remittance_advice']='';
		$s_data['order_method']='';
		$s_data['payment_type_id']='';

		$this->setSearch('PLSupplierSearch', 'useDefault', $s_data);

		$this->view->set('clickaction', 'view');

		parent::index(new PLSupplierCollection($this->_templateobject));

		$sidebarlist=$this->indexSidebar();

		$sidebar = new SidebarController($this->view);

		foreach ($sidebarlist as $name=>$data)
		{
			$sidebar->addList($name,$data);
		}

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function _new()
	{
		parent::_new();

		$supplier = $this->_uses[$this->modeltype];

		if ($supplier->isLoaded())
		{
			$this->view->set('transaction_count',$supplier->transactions->count());

			$emails=$this->getEmailAddresses($supplier->company_id);

			unset($emails['']);

			$this->view->set('emails',$emails);
		}
		elseif (isset($this->_data['company_id']))
		{
			$supplier->company_id = $this->_data['company_id'];

			$this->view->set('payee', $supplier->name);
		}
		else
		{
			$unassigned_list=$supplier->getUnassignedCompanies();

			if (count($unassigned_list)>0)
			{
				$this->view->set('company_list',$unassigned_list);

				$emails=$this->getEmailAddresses(key($unassigned_list));

				unset($emails['']);

				$this->view->set('emails',$emails);

				$this->view->set('payee',current($unassigned_list));

				$supplier->company_id = key($unassigned_list);
			}
			else
			{
				$flash = Flash::Instance();
				$flash->addMessage('All companies are assigned as suppliers');
				sendBack();
			}
		}

		if (is_null($supplier->cb_account_id))
		{
			$cbaccount = CBAccount::getPrimaryAccount();
			$supplier->cb_account_id = $cbaccount->{$cbaccount->idField};
		}

		$this->view->set('bank_account', $supplier->cb_account_id);
		$this->view->set('bank_accounts', $this->getbankAccounts($supplier->id));

		$this->view->set('payment_addresses', $supplier->getRemittanceAddresses());
		$this->view->set('receive_actions',WHAction::getReceiveActions());

	}

	public function getSupplierList ()
	{
		return $this->getOptions($this->_uses['PLTransaction'], 'plmaster_id', 'getSupplierList', 'getOptions', array('use_collection'=>true));

	}

	public function enter_journal()
	{

		$supplier = $this->_uses[$this->modeltype];
		$supplier_list=$this->getSupplierList();
		$this->view->set('companies', $supplier_list);

		$gl_account = DataObjectFactory::Factory('GLAccount');
		$gl_accounts=$gl_account->nonControlAccounts();
		$this->view->set('gl_accounts',$gl_accounts);

		$gl_centres=$this->getCentres(key($gl_accounts));
		$this->view->set('centres',$gl_centres);

		$this->sidebar(__FUNCTION__);

	}

	public function save_journal()
	{
		$flash = Flash::Instance();
		$errors = array();
		$result = false;
		$data = $this->_data['PLTransaction'];

		$gl_account = DataObjectFactory::Factory('GLAccount');
		$allowed_accounts = $gl_account->nonControlAccounts();
		$post_allowed = array_key_exists($data['glaccount_id'], $allowed_accounts);
		if (!$post_allowed){
		    $errors[] = 'Cannot post journal to a control account';
		}

		if ($this->checkParams('PLTransaction') && $post_allowed)
		{
			if ($data['net_value']!=0)
			{
				$supplier = $this->getSupplier($data['plmaster_id']);

				$data['currency_id']	 = $supplier->currency_id;
				$data['payment_term_id'] = $supplier->payment_term_id;

				$db = DB::Instance();
				$data['our_reference'] = $db->GenID('pl_journals_id_seq');

				$result = PLTransaction::saveTransaction($data,$errors);

				if ($result!==false)
				{
					$flash->addMessage('Journal saved');
				}
			}
			else
			{
				$errors[] = 'Zero value not allowed';
			}
		}

		if ($result!==false)
		{
			if (isset($this->_data['saveAnother']))
			{
				$this->context['plmaster_id'] = $data['plmaster_id'];
				$this->saveAnother();
			}

			sendTo($this->name
				,'view'
				,$this->_modules
				,array('id'=>$data['plmaster_id']));

		}

		$flash->addErrors($errors);

		if (isset($data['plmaster_id']) && !empty($data['plmaster_id']))
		{
			$this->_data['plmaster_id'] = $data['plmaster_id'];
		}

		$this->refresh();

	}

	public function make_payment()
	{
		$this->cashbook_payment(__FUNCTION__);

		$this->view->set('type', 'P');

	}

	public function receive_refund()
	{
		$this->cashbook_payment(__FUNCTION__);

		$this->view->set('type', 'RP');

	}

	public function save_payment()
	{

		$flash = Flash::Instance();

		$errors = array();

		if (!$this->checkParams('CBTransaction'))
		{
			sendBack();
		}

		$data = $this->_data['CBTransaction'];

		if (isset($this->_data['PLTransaction']['plmaster_id']))
		{
			$data['plmaster_id'] = $this->_data['PLTransaction']['plmaster_id'];

			$company = DataObjectFactory::Factory('PLSupplier');
			$company->load($data['plmaster_id']);

			if (!$company->isLoaded())
			{
				$this->dataError('Cannot find Supplier');
				sendBack();
			}

			$data['payment_term_id'] = $company->payment_term_id;
		}

		if ($data['net_value']>0)
		{

			$result = PLTransaction::saveTransaction($data, $errors);

			if ($result!==false)
			{
				$flash->addMessage('Payment saved');

				if (isset($this->_data['saveAnother']))
				{
					$this->context['plmaster_id'] = $data['plmaster_id'];
					$this->saveAnother();
				}
				else
				{
					sendTo($this->name
						,'view'
						,$this->_modules
						,array('id'=>$data['plmaster_id']));
				}
			}
			else
			{
				$errors[]='Error saving payment';
			}
		}
		else
		{
			$errors[]='Payment must be greater than zero';
		}

		$flash->addErrors($errors);

		if (isset($data['plmaster_id']) && !empty($data['plmaster_id']))
		{
			$this->_data['plmaster_id']=$data['plmaster_id'];
		}

		$this->refresh();

	}

	public function periodicpayments ()
	{
		sendTo('periodicpayments'
				,'index'
				,'cashbook'
				,array('source'=>'PP'));

	}

	public function allocate()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$supplier = $this->_uses[$this->modeltype];

		$transaction	= DataObjectFactory::Factory('PLTransaction');
		$transactions	= new PLTransactionCollection($transaction, 'pl_allocation_overview');

		$sh = new SearchHandler($transactions,false);

		$db=DB::Instance();
		$sh->addConstraint(new Constraint('status','in','('.$db->qstr($transaction->open()).','.$db->qstr($transaction->partPaid()).')'));
		$sh->addConstraint(new Constraint('plmaster_id','=',$supplier->id));

		$sh->setOrderby(array('supplier', 'our_reference'));
		$transactions->load($sh);

		$this->view->set('allocated_total',0);

		$this->view->set('transactions',$transactions);
		$this->view->set('no_ordering',true);

	}

	public function save_allocation()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$db=DB::Instance();
		$db->StartTrans();

		$flash = Flash::Instance();

		$errors=array();

		$transactions = array();

		$allocated_total=0.00;

		foreach ($this->_data['PLTransaction'] as $id=>$data)
		{
			if (isset($data['allocate']))
			{
				// using bcadd to format value
				$transactions[$id]	= bcadd($data['os_value'], 0);
				$allocated_total	= bcadd($allocated_total, $data['os_value']);
			}

			// Save settlement discount if present?
			if ($data['settlement_discount']>0 && isset($data['include_discount']))
			{
				// Add back the settlement discount to the transaction value
				$transactions[$id]	= bcadd($data['settlement_discount'], $transactions[$id]);
				// Create GL Journal for settlement discount

				// TODO: Check if need to create a PL transaction for the discount
				// and add id=>value pair to $transactions
				$pltransaction = DataObjectFactory::Factory('PLTransaction');

				$pltransaction->load($id);

				$discount = array();

				$discount['gross_value'] = $discount['net_value'] = $data['settlement_discount'];

				$discount['glaccount_id']	= $data['pl_discount_glaccount_id'];
				$discount['glcentre_id']	= $data['pl_discount_glcentre_id'];

				$discount['transaction_date']	= date(DATE_FORMAT);
				$discount['tax_value']			= '0.00';
				$discount['source']				= 'P';
				$discount['transaction_type']	= 'SD';
				$discount['our_reference']		= $pltransaction->our_reference;
				$discount['ext_reference']		= $pltransaction->ext_reference;
				$discount['currency_id']		= $pltransaction->currency_id;
				$discount['rate']				= $pltransaction->rate;
				$discount['description']		= (!empty($data['pl_discount_description'])?$data['pl_discount_description'].' ':'');
				$discount['description']		.=(!is_null($pltransaction->description)?$pltransaction->description:$discount['ext_reference']);
				$discount['payment_term_id']	= $pltransaction->payment_term_id;
				$discount['plmaster_id']		= $pltransaction->plmaster_id;

				$pldiscount = PLTransaction::Factory($discount, $errors, 'PLTransaction');

				if ($pldiscount && $pldiscount->save('', $errors) && $pldiscount->saveGLTransaction($discount, $errors))
				{
					$transactions[$pldiscount->{$pldiscount->idField}]	= bcadd($discount['net_value'], 0);
				}
				else
				{
					$errors[] = 'Errror saving PL Transaction Discount : '.$db->ErrorMsg();
					$flash->addErrors($errors);
				}

			}

		}

		if(count($transactions)==0)
		{
			$flash->addError('You must select at least one transaction');
		}
		elseif (count($errors)==0)
		{
			if (!PLTransaction::allocatePayment($transactions, $this->_data['id'], $errors)
			||  !PLAllocation::saveAllocation($transactions, null, $errors))
			{
				$flash->addErrors($errors);
			}
			elseif ($db->CompleteTrans())
			{
				$flash->addMessage('Transactions matched');
				sendTo($this->name
					  ,'view'
					  ,$this->_modules
					  ,array('id'=>$this->_data['id']));
			}
		}

		$flash->addErrors($errors);
		$db->FailTrans();
		$db->CompleteTrans();

		$this->allocate();
		$this->view->set('allocated_total', $allocated_total);

		$this->setTemplatename('allocate');
	}

	public function inquery_transactions()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$supplier = $this->_uses[$this->modeltype];

		$transaction	= DataObjectFactory::Factory('PLTransaction');
		$transactions	= new PLTransactionCollection($transaction);

		$db = DB::Instance();
		$transactions->orderby=array('supplier', 'our_reference');

		$sh = $this->setSearchHandler($transactions);
		$sh->addConstraint(new Constraint('status', '=', $transaction->Query()));
		$sh->addConstraint(new Constraint('plmaster_id', '=', $supplier->id));

		parent::index($transactions, $sh);

		$this->view->set('ledger_account', $supplier);
		$this->view->set('collection', $transactions);

		$this->view->set('clickaction', 'view');
		$this->view->set('clickcontroller', 'pltransactions');
		$this->view->set('invoice_module', 'purchase_invoicing');
		$this->view->set('invoice_controller', 'pinvoices');

		$this->_templateName = $this->getTemplateName('view_ledger_trans');

	}

	public function outstanding_transactions()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$supplier = $this->_uses[$this->modeltype];

		$category = DataObjectFactory::Factory('LedgerCategory');
		$categories = $category->checkCompanyUsage($supplier->company_id);

		// Check for Sales Ledger account and Contra Control account
		$glparams = DataObjectFactory::Factory('GLParams');

		// Does this PL Supplier also have a SL Customer account
		// and does the Contras Control Account also exist
		// if so, then allow contras
		$can_contra = (isset($categories['SL']['exists'])
						&& $categories['SL']['exists']
						&& $glparams->contras_control_account() != FALSE);

		$this->view->set('can_contra', $can_contra);

		$transaction	= DataObjectFactory::Factory('PLTransaction');
		$transactions	= new PLTransactionCollection($transaction);

		$db = DB::Instance();
		$transactions->orderby=array('supplier', 'our_reference');

		$sh = $this->setSearchHandler($transactions);
		$sh->addConstraint(new Constraint('status', 'in', '(' . $db->qstr($transaction->open()) . ',' . $db->qstr($transaction->partPaid()) . ')'));
		$sh->addConstraint(new Constraint('plmaster_id', '=', $supplier->id));

		parent::index($transactions, $sh);

		if ($can_contra)
		{
			// create session object to handle paged data input
			$contras_sessionobject = new SessionData('pl_contras');

			if (!$contras_sessionobject->PageDataExists())
			{
				// session object does not exist so register it
				$contras_sessionobject->registerPageData(array('os_value', 'contra'));
			}

			// Check for form input due to paging or ordering
			if (isset($this->_data['PLTransaction']))
			{
				foreach ($this->_data['PLTransaction'] as $id=>$data)
				{
					if ($fields['contra'] == 'on')
					{
						$contras_sessionobject->updatePageData($id, $fields, $errors);
					}
					else
					{
						$contras_sessionobject->deletePageData($id);
					}
				}
			}

			$contras_data = $contras_sessionobject->getPageData($errors);

			$contra_total = 0;

			foreach ($contras_data as $value)
			{
				if (isset($value['contra']) && $value['contra'])
				{
					$contra_total += $value['os_value'];
				}
			}

			$this->view->set('contra_total', $contra_total);

			$this->view->set('page_data', $contras_data);
		}

		$this->view->set('ledger_account', $supplier);
		$this->view->set('collection', $transactions);

		$this->view->set('clickaction', 'view');
		$this->view->set('clickcontroller', 'pltransactions');
		$this->view->set('invoice_module', 'purchase_invoicing');
		$this->view->set('invoice_controller', 'pinvoices');

		$this->_templateName = $this->getTemplateName('view_ledger_trans');

	}

	public function save_contras()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$db = DB::Instance();
		$db->StartTrans();

		$flash = Flash::Instance();

		$errors = array();

		$transactions = array();

		$contras_sessionobject = new SessionData('pl_contras');

		foreach ($this->_data['PLTransaction'] as $id=>$data)
		{
			$data['contra'] = (isset($data['contra']) && $data['contra']=='on');
			$contras_sessionobject->updatePageData($id, $data, $errors);
		}

		$contra_total = (isset($this->_data['contra_total']))?$this->_data['contra_total']:'0.00';

		$contra_sum = 0;

		foreach ($contras_sessionobject->getPageData($errors) as $id=>$data)
		{
			if (isset($data['contra']) && $data['contra'] == 'on')
			{
				// using bcadd to format value
				$transactions[$id]	= bcadd($data['os_value'], 0);
				$contra_sum			= bcadd($contra_sum, $data['os_value']);
			}

		}

		if(count($transactions)==0)
		{
			$errors[] = 'You must select at least one transaction';
		}
		elseif ($contra_total==$contra_sum)
		{
			$pl_journal_seq = $db->GenID('pl_journals_id_seq');
			$sl_journal_seq = $db->GenID('sl_journals_id_seq');

			// Create the PL and SL contra journals
			$pltransaction = DataObjectFactory::Factory('PLTransaction');

			$pltransaction->load($id);

			$plcontra = array();

			$plcontra['gross_value'] = $plcontra['net_value'] = bcmul($contra_sum, -1);

			$glparams = DataObjectFactory::Factory('GLParams');

			$plcontra['glaccount_id']		= $glparams->contras_control_account();
			$plcontra['glcentre_id']		= $glparams->balance_sheet_cost_centre();

			$plcontra['transaction_date']	= date(DATE_FORMAT);
			$plcontra['tax_value']			= '0.00';
			$plcontra['source']				= 'P';
			$plcontra['transaction_type']	= 'J';
			$plcontra['our_reference']		= $pl_journal_seq;
			$plcontra['currency_id']		= $this->_data['PLSupplier']['currency_id'];
			$plcontra['rate']				= $this->_data['PLSupplier']['rate'];
			$plcontra['payment_term_id']	= $this->_data['PLSupplier']['payment_term_id'];

			$slcontra = $plcontra;

			$plcontra['plmaster_id']		= $this->_data['PLSupplier']['id'];
			$plcontra['description']		= 'Contra Purchase Ledger - SL Ref:'.$sl_journal_seq;

			$pltrans = PLTransaction::Factory($plcontra, $errors, 'PLTransaction');

			if ($pltrans && $pltrans->save('', $errors) && $pltrans->saveGLTransaction($plcontra, $errors))
			{
				$transactions[$pltrans->{$pltrans->idField}]	= bcadd($plcontra['net_value'], 0);
			}
			else
			{
				$errors[] = 'Errror saving PL Transaction Contra : '.$db->ErrorMsg();
				$flash->addErrors($errors);
			}

			$slcontra['source']			= 'S';
			$slcontra['our_reference']	= $sl_journal_seq;
			$slcontra['description']	= 'Contra Sales Ledger - PL Ref:'.$pl_journal_seq;
			$slcontra['gross_value']	= $slcontra['net_value'] = bcmul($contra_sum, -1);

			$customer = DataObjectFactory::Factory('SLCustomer');
			$customer->loadBy('company_id', $this->_data['PLSupplier']['company_id']);

			if ($customer->isLoaded())
			{
				$slcontra['slmaster_id'] = $customer->{$customer->idField};

				$sltrans = SLTransaction::Factory($slcontra, $errors, 'SLTransaction');
			}
			else
			{
				$sltrans = FALSE;
			}

			if (!$sltrans || !$sltrans->save('', $errors) || !$sltrans->saveGLTransaction($slcontra, $errors))
			{
				$errors[] = 'Errror saving SL Transaction Contra : '.$db->ErrorMsg();
				$flash->addErrors($errors);
			}

		}
		else
		{
			$errors[] = 'Transactions sum mismatch Sum: '.$contra_sum.' Control Total: '.$contra_total;
		}

		if (count($errors)>0
			|| !PLTransaction::allocatePayment($transactions, $this->_data['id'], $errors)
			|| !PLAllocation::saveAllocation($transactions, null, $errors))
		{
			$db->FailTrans();
		}

		if ($db->CompleteTrans())
		{
			$contras_sessionobject->clear();

			$flash->addMessage('Contra Transactions matched');

			sendTo($this->name
				  ,'view'
				  ,$this->_modules
				  ,array('id'=>$this->_data['id']));
		}

		$flash->addErrors($errors);

		$this->outstanding_transactions();

	}

	public function all_transactions()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$supplier = $this->_uses[$this->modeltype];

		$transactions = new PLTransactionCollection();

		$sh = $this->setSearchHandler($transactions);

		$sh->addConstraint(new Constraint('plmaster_id','=',$supplier->id));

		parent::index($transactions, $sh);

		$this->view->set('collection',$transactions);
		$this->view->set('master_id', 'plmaster_id');

		$this->view->set('clickaction','view');
		$this->view->set('clickcontroller','pltransactions');
		$this->view->set('invoice_module','purchase_invoicing');
		$this->view->set('invoice_controller','pinvoices');

		$this->_templateName=$this->getTemplateName('view_ledger_trans');
	}

	public function save()
	{

		$errors=array();
		$flash=Flash::Instance();
		$db=DB::Instance();

		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}

		$company = DataObjectFactory::Factory('Company');
		$company->load($this->_data[$this->modeltype]['company_id']);

		if(!$company->isLoaded())
		{
			$flash->addError('Invalid company');
			sendBack();
		}

		if($this->_data[$this->modeltype]['email_order_id']==0)
		{
			$this->_data[$this->modeltype]['email_order_id']=NULL;
		}

		if($this->_data[$this->modeltype]['email_remittance_id']==0)
		{
			$this->_data[$this->modeltype]['email_remittance_id']=NULL;
		}

		if(parent::save_model($this->modeltype, $this->_data[$this->modeltype], $errors))
		{
			sendTo($this->name, 'view', $this->_modules,array('id'=>$this->saved_model->id));
		}
		else
		{
			if (count($errors)>0)
			{
				$flash->addErrors($errors);
			}
			$flash->addError('Error saving Supplier '.$db->ErrorMsg());
		}

		if (isset($this->_data[$this->modeltype]['id']))
		{
			$this->_data['id']=$this->_data[$this->modeltype]['id'];
		}

		$this->refresh();

	}

	public function view()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$supplier=$this->_uses[$this->modeltype];

		$flash=Flash::Instance();

		if(!$supplier->isLoaded())
		{
			$flash->addError('Error getting supplier');
			sendTo($this->name
				,'index'
				,$this->_modules);
		}

		$idField = $supplier->idField;
		$idValue = $supplier->$idField;

		$sidebar=new SidebarController($this->view);

		$sidebar->addList(
			'Actions',
			array(
				$supplier->name => array(
					'tag'	=> 'View All Suppliers',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'index'
									)
				),
				'make_payment' => array(
					'tag'	=> 'make_payment',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'make_payment'
									)
				),
				'receive_refund' => array(
					'tag'	=> 'receive_refund',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'receive_refund'
									)
				),
				'enter_journal' => array(
					'tag'	=> 'enter_PL_journal',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'enter_journal'
									)
				),
				'periodic_payments' => array(
					'tag'	=> 'periodic_payments',
					'link'	=> array('module'		=> 'cashbook'
									,'controller'	=> 'Periodicpayments'
					 				,'action'		=> 'index'
									,'source'		=> 'PP'
									)
				)
			)
		);

		$sidebarlist = array(
				$supplier->name => array(
					'tag'	=> $supplier->name,
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'view'
									,$idField		=> $idValue
									)
				),
				'edit' => array(
					'tag'	=> 'Edit',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'edit'
									,$idField		=> $idValue
									)
				),
				'newinvoice' => array(
					'tag'	=> 'New Invoice',
					'link'	=> array('module'			=> 'purchase_invoicing'
									,'controller'		=> 'pinvoices'
									,'action'			=> 'new'
									,'plmaster_id'		=> $idValue
									,'transaction_type'	=> 'I'
									,'payment_term_id'	=> $supplier->payment_term_id
									,'currency_id'		=> $supplier->currency_id
									)
				),
				'newcreditnote' => array(
					'tag'	=> 'New Credit Note',
					'link'	=> array('module'			=> 'purchase_invoicing'
									,'controller'		=> 'pinvoices'
									,'action'			=> 'new'
									,'plmaster_id'		=> $supplier->id
									,'transaction_type'	=> 'C'
									,'payment_term_id'	=> $supplier->payment_term_id
									,'currency_id'		=> $supplier->currency_id
									)
				),
				'enter_journal'=>array(
					'tag'	=> 'Enter PL Journal',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'enter_journal'
									,'plmaster_id'	=> $idValue
									)
				),
				'make_payment' => array(
					'tag'	=> 'make_payment',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'make_payment'
									,'plmaster_id'	=> $idValue
									)
				),
				'receive_refund' => array(
					'tag'	=> 'receive_refund',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'receive_refund'
									,'plmaster_id'	=> $idValue
									)
				),
				'periodic_payments' => array(
					'tag'	=> 'periodic_payments',
					'link'	=> array('module'		=> 'cashbook'
									,'controller'	=> 'Periodicpayments'
									,'action'		=> 'index'
									,'source'		=> 'PP'
									,'company_id'	=> $supplier->company_id
									)
				),
		);

		if ($supplier->canDelete())
		{
			$sidebarlist['delete'] = array(
				'tag'	=> 'delete',
				'link'	=> array('modules'		=> $this->_modules
								,'controller'	=> $this->name
								,'action'		=> 'delete'
								,$idField		=> $idValue
			),
			'class' => 'confirm',
			'data_attr' => ['data_uz-confirm-message' => "Delete Supplier?|This cannot be undone.",
							'data_uz-action-id' => $idValue]
			);
		}

		if (!is_null($supplier->date_inactive))
		{
			$sidebarlist['inactive'] = array(
				'tag'	=> 'Make Active',
				'link'	=> array('modules'		=> $this->_modules
								,'controller'	=> $this->name
								,'action'		=> 'make_active'
								,$idField		=> $idValue
				),
				'class' => 'protected',
				'data_attr' => ['data_uz-action-id' => $idValue]
			);
		}
		elseif (!$supplier->hasCurrentActivity())
		{
			$sidebarlist['inactive'] = array(
				'tag'	=> 'Make Inactive',
				'link'	=> array('modules'		=> $this->_modules
								,'controller'	=> $this->name
								,'action'		=> 'make_inactive'
								,$idField		=> $idValue
				),
				'class' => 'confirm',
				'data_attr' => ['data_uz-confirm-message' => "Make Supplier Inactive?|No purchases can be made from this supplier once they are inactive.",
								'data_uz-action-id' => $idValue]
			);
		}

		$sidebar->addList(
			'currently_viewing', $sidebarlist
		);

		$sidebar->addList(
			'reports',
			array(
				'suggestedpayment' => array(
					'tag'	=> 'Suggested Payments',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'printDialog'
									,'printaction'	=> 'suggestedpayments'
									,'filename'		=> 'suggestPayments_'.$supplier->name
									,$idField		=> $idValue
									)
				)
			)
		);

		$sidebar->addList(
			'related_items',
			array(
				'allocate' => array(
					'tag'	=> 'allocate',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'allocate'
									,$idField		=> $idValue
									)
				),
				'outstanding' => array(
					'tag'	=> 'Outstanding',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'outstanding_transactions'
									,$idField		=> $idValue
									)
				),
				'inquery' => array(
					'tag'	=> 'In Query',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'inquery_transactions'
									,$idField		=> $idValue
									)
				),
				'all' => array(
					'tag'	=> 'All',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'all_transactions'
									,$idField		=> $idValue
									)
				),
				'viewunposted'=>array(
					'tag'	=> 'View unposted invoices',
					'link'	=> array('module'		=> 'purchase_invoicing'
									,'controller'	=> 'pinvoices'
									,'action'		=> 'index'
									,'plmaster_id'	=> $idValue
									,'status'		=> 'N'
									)
				),
				'viewinvoices' => array(
					'tag'	=> 'View all invoices',
					'link'	=> array('module'		=> 'purchase_invoicing'
									,'controller'	=> 'pinvoices'
									,'action'		=> 'index'
									,'plmaster_id'	=> $idValue
									)
				),
				'viewcontact_details' => array(
					'tag'	=> 'View Contact Details',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'viewcontact_methods'
									,$idField		=> $idValue
									)
				),
				'vieworders' => array(
					'tag'	=> 'View Supplier Orders',
					'link'	=> array('module'		=> 'purchase_order'
									,'controller'	=> 'porders'
									,'action'		=> 'index'
									,'plmaster_id'	=> $idValue
									),
					'new'	=> array('module'		=> 'purchase_order'
									,'controller'	=> 'porders'
									,'action'		=> 'new'
									,'plmaster_id'	=> $idValue
									)
				),
				'viewprices'=>array(
					'tag'	=> 'View Supplier Prices',
					'link'	=> array('module'		=> 'purchase_order'
									,'controller'	=> 'poproductlines'
									,'action'		=> 'index'
									,'plmaster_id'	=> $idValue
									)
				)
			)
		);

		$address = $supplier->getBillingAddress();
		$this->view->set('billing_address',$address);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function make_active()
	{
		$this->checkRequest(['post'], true);
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$supplier=$this->_uses[$this->modeltype];

		$flash = Flash::Instance();

		$supplier->date_inactive = null;

		$db = DB::Instance();
		$db->StartTrans();

		if (!$supplier->save())
		{
			$flash->addError('Error making supplier active: '.$db->ErrorMsg());
			$db->FailTrans();
		}

		if (!$supplier->companydetail->makeActive()) {
            $flash->addError('Error making supplier contact active: ' . $db->ErrorMsg());
            $db->FailTrans();
        } else {
            $flash->addMessage('Supplier marked as active');
        }

		$db->CompleteTrans();

		sendBack();

	}

	public function make_inactive()
	{
		$this->checkRequest(['post'], true);
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$supplier=$this->_uses[$this->modeltype];

		$flash = Flash::Instance();

		// Check to make sure no-one has updated the customer
		if ($supplier->hasCurrentActivity())
		{
			$flash->addError('Error making supplier inactive - supplier is still active');
		}
		else
		{
			$supplier->date_inactive = fix_date(date(DATE_FORMAT));

			$db = DB::Instance();
			$db->StartTrans();

			if (!$supplier->save())
			{
				$flash->addError('Error making supplier inactive: '.$db->ErrorMsg());
				$db->FailTrans();
			}
			else
			{
				// Now close off any open PO Product Lines for the Supplier
				$poproductline	= DataObjectFactory::Factory('POProductline');
				$poproductlines = new POProductlineCollection($poproductline);

				$sh = new SearchHandler($poproductlines, FALSE);

				$sh->addConstraintChain($poproductline->currentConstraint());
				$sh->addConstraint(new Constraint('plmaster_id', '=', $supplier->id));

				if ($poproductlines->update('end_date', $supplier->date_inactive, $sh) !== FALSE)
				{
					$flash->addMessage('Supplier marked as inactive');
				}
				else
				{
					$flash->addError('Error closing off supplier product lines: '.$db->ErrorMsg());
					$db->FailTrans();
				}

			}

            // Make contact and associated people inactive
            try
            {
                $result = $supplier->companydetail->makeInactive(fix_date(date(DATE_FORMAT)), 'PL');
                if (!$result) {
                    $flash->addError('Error making supplier contact inactive: ' . $db->ErrorMsg());
                    $db->FailTrans();
                }
            }
            catch(Exception $e)
            {
				if ($e->getCode() === 1) {
					$flash->addError($e->getMessage());
					$db->FailTrans();
				} else {
					$flash->addWarning($e->getMessage());
				}
            }

			$db->CompleteTrans();
		}
		sendBack();
	}

	public function delete() {
        $this->checkRequest(['post'], true);
        parent::delete();
    }

	public function viewcontact_methods ()
	{

		if (!$this->checkParams('id'))
		{
			sendBack();
		}

		$flash=Flash::Instance();

		$errors=array();

		$supplier=$this->_uses[$this->modeltype];
		$supplier->load($this->_data['id']);

		$cc = new ConstraintChain();
		$cc->add(new Constraint('payment', 'is', true));

		$this->view->set('contactdetails',$supplier->companydetail->getContactMethods('', $cc));
	}

	/*
	 * Ajax functions
	 */
	public function getAccountRate($_supplier_id = '', $_cb_account_id = '')
	{
// Used by Ajax to return Currency after selecting the Customer
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['cb_account_id'])) { $_cb_account_id=$this->_data['cb_account_id']; }
		}

		$rate = '';

		$glparams = DataObjectFactory::Factory('GLParams');

		$supplier = $this->getSupplier($_supplier_id);

		if ($supplier->isLoaded()
			&& $glparams->base_currency() != $supplier->currency_id)
		{
			$rate = $supplier->currency_detail->rate;
		}

		if (empty($rate) && !empty($_cb_account_id))
		{

			$cb_account = DataObjectFactory::Factory('CBAccount');
			$cb_account->load($_cb_account_id);

			if ($cb_account->isLoaded()
				&& $glparams->base_currency() != $cb_account->currency_id)
			{
				$rate = $cb_account->currency_detail->rate;
			}
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$rate);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $rate;
		}

	}

	public function getBankAccountId($_id = '')
	{
		// Used by Ajax to return Currency after selecting the Supplier

		$value='';

		$supplier = $this->getSupplier($_id);

		if ($supplier->isLoaded())
		{
			$value = $supplier->cb_account_id;
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$value);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $value;
		}
	}

	public function getbankAccounts($_supplier_id = '')
	{
		// Used by Ajax to return list of allowed bank accounts after selecting the Customer

		$cbaccounts = array();

		$supplier = $this->getSupplier($_supplier_id);

		$glparams = DataObjectFactory::Factory('GLParams');
		$currency_id = $glparams->base_currency();

		// Override the currency if we have an existing supplier record,
		// otherwise the base currency from above will be used.
		if ($supplier->isLoaded())
		{
			$currency_id	= $supplier->currency_id;
		}

		// If the user has selected a new currency
		// the id will be in the request,
		// so set the selected currency id.
		if(isset($this->_data['ajax'])) {
			$currency_id = $this->_data['id'];
		}

		$cc = new ConstraintChain();
		$base_currency_id = $glparams->base_currency();

		if ($currency_id != $base_currency_id)
		{
			$cc->add(new Constraint('currency_id', 'in', '('.$currency_id.','.$base_currency_id.')'));
		}
		$cbaccount = DataObjectFactory::Factory('CBAccount');
		$cbaccounts = $cbaccount->getAll($cc);
		
		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$cbaccounts);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $cbaccounts;
		}
	}

	public function getCurrencyId($_id = '')
	{
		// Used by Ajax to return Currency after selecting the Supplier

		$currency='';

		$supplier = $this->getSupplier($_id);

		if ($supplier->isLoaded())
		{
			$currency = $supplier->currency_id;
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$currency);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $currency;
		}
	}

	public function getCurrency($_id = '')
	{
// Used by Ajax to return Currency after selecting the Supplier

		$currency='';

		$supplier = $this->getSupplier($_id);

		if ($supplier->isLoaded())
		{
			$currency=$supplier->currency;
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$currency);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $currency;
		}
	}

	public function getCompanyName($_id = '')
	{
		// Used by Ajax to return Company Name after selecting the Supplier

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}

		$name='';
		if (!empty($_id))
		{
			$company = DataObjectFactory::Factory('Company');
			$company->load($_id);
			if ($company->isLoaded())
			{
				$name=$company->name;
			}
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$name);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $name;
		}
	}

	public function getCustomerData()
	{
	// this function will only ever be called via an AJAX request, no paramters needed

		$fields = explode(',',$this->_data['fields']);

		$supplier = $this->getSupplier();

		foreach($fields as $key=>$value)
		{
			$temp=$supplier->$value;
			$output[$value]=array('data'=>$temp,'is_array'=>is_array($temp));
		}

		$accounts = $this->getBankAccounts();
		$output['cb_account_id']=array('data'=>$accounts,'is_array'=>is_array($accounts));

		$this->view->set('data',$output);
		$this->setTemplateName('ajax_multiple');

	}

	public function getEmailAddresses($_id = '')
	{
		// Used by Ajax to return Email Addresses after selecting the Supplier

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}

		$emails=array(''=>'None');
		if (!empty($_id)) {
			$company = DataObjectFactory::Factory('Company');
			$company->load($_id);
			if ($company->isLoaded())
			{
				foreach ($company->getEmailAddresses() as $emailaddresses)
				{
					$emails[$emailaddresses->id]=$emailaddresses->contact;
				}
			}
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$emails);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $emails;
		}
	}

	public function getPaymentTypeId($_id = '')
	{
		// Used by Ajax to return Payment Type after selecting the Supplier

		$payment_type='';

		$supplier = $this->getSupplier($_id);

		if ($supplier->isLoaded())
		{
			$payment_type = $supplier->payment_type_id;
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$payment_type);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $payment_type;
		}
	}

	public function getRemittanceAddresses($_company_id = '')
	{
		// Used by Ajax to return Payment Type after selecting the Supplier

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_company_id = $this->_data['id']; }
		}

		$addresses = array();

		if (!empty($_company_id))
		{
			$supplier = $this->_uses[$this->modeltype];

			$supplier->company_id = $_company_id;

			$addresses = $supplier->getRemittanceAddresses();
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options', $addresses);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $addresses;
		}

	}

	/*
	 * Private functions
	 */
	private function cashbook_payment($current_type = __FUNCTION__)
	{

		$supplier_list=$this->getSupplierList();

		if(isset($this->_data['plmaster_id']))
		{
			$supplier_id = $this->_data['plmaster_id'];
		}
		else
		{
			$supplier_id = key($supplier_list);
		}

		$supplier = $this->getSupplier($supplier_id);

		if (!$supplier->isLoaded())
		{
			$flash = Flash::Instance();
			$flash->addError('Error loading Supplier details');
			sendBack();
		}

		$this->_data['currency_id']= $supplier->currency_id;

		$this->view->set('master_value', $supplier_id);
		$this->view->set('company_id', $supplier->company_id);
		$this->view->set('currency', $supplier->currency);
		$this->view->set('payment_type', $supplier->payment_type_id);

		if (is_null($supplier->cb_account_id))
		{
			$cbaccount = CBAccount::getPrimaryAccount();
			$supplier->cb_account_id = $cbaccount->{$cbaccount->idField};
		}

		$this->view->set('bank_account', $supplier->cb_account_id);
		$this->view->set('bank_accounts', $this->getbankAccounts($supplier_id));
		$this->view->set('rate', $this->getAccountRate($supplier->id, $supplier->cb_account_id));
		$this->view->set('companies', $supplier_list);

		$this->sidebar($current_type);
		$this->_templateName=$this->getTemplateName('enter_payment');

	}

	private function getSupplier($_supplier_id = '')
	{

		$supplier = $this->_uses[$this->modeltype];

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['plmaster_id'])) { $_supplier_id = $this->_data['plmaster_id']; }
		}

		if (!empty($_supplier_id))
		{
			if ($supplier->isLoaded())
			{
				$supplier = DataObjectFactory::Factory('PLSupplier');
			}

			$supplier->load($_supplier_id);
		}
		elseif (!$supplier->isLoaded())
		{
			$this->loadData();

			$supplier = $this->_uses[$this->modeltype];
		}

		return $supplier;

	}

	private function sidebar($current_type)
	{

		$this->view->set('source', 'P');
		$this->view->set('Transaction', $this->_uses['PLTransaction']);
		$this->view->set('master_id', 'plmaster_id');
		$this->view->set('master_label', 'Supplier');

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['all'] = array(
					'tag'	=> 'View all suppliers',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'index'
									)
					);

		if ($current_type != 'make_payment')
		{
			$sidebarlist['make_payment'] = array(
					'tag'	=> 'make_payment',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'make_payment'
									)
					);
		}

		if ($current_type != 'receive_refund')
		{
			$sidebarlist['receive_refund'] = array(
					'tag'	=> 'receive_refund',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'receive_refund'
									)
					);
		}

		if ($current_type != 'enter_journal')
		{
			$sidebarlist[$type] = array(
					'tag'	=> 'enter_journal',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'enter_journal'
									)
					);
		}

		$sidebar->addList('Actions', $sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	private function indexSidebar()
	{
		$sidebarlist=array();

		$sidebarlist['actions']['all']=array(
					'tag' => 'View All Suppliers',
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=> 'index'
								 )
					);
		$sidebarlist['actions']['new']=array(
					'tag' => 'new_Supplier',
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=> 'new'
								 )
					);
		$sidebarlist['actions']['make_payment']=array(
					'tag'	=> 'make_payment',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'make_payment'
									)
					);
		$sidebarlist['actions']['receive_refund']=array(
					'tag'	=> 'receive_refund',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'receive_refund'
									)
					);
		$sidebarlist['actions']['enter_journal']=array(
					'tag'	=> 'enter_PL_journal',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'enter_journal'
									)
					);
		$sidebarlist['actions']['periodic_payments']=array(
					'tag'	=> 'periodic_payments',
					'link'	=> array('module'		=> 'cashbook'
									,'controller'	=> 'Periodicpayments'
									,'action'		=> 'index'
									,'source'		=> 'PP'
									)
					);
		$sidebarlist['actions']['batch_payments']=array(
					'tag'	=> 'batch_payments',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> 'plpayments'
									,'action'		=> 'index'
									)
					);

		return $sidebarlist;

	}

	/*
	 * Protected functions
	 */
	protected function getPageName($base=null,$type=null)
	{
		return parent::getPageName((empty($base)?'purchase_ledger_suppliers':$base),$type);
	}

	/* output functions */
	public function suggestedPayments($status='generate')
	{

		// build options array
		$options=array('type'		=>	array('pdf'=>'',
											  'xml'=>''
										),
					   'output'		=>	array('print'=>'',
					   						  'save'=>'',
					   						  'email'=>'',
					   						  'view'=>''
										),
					   'filename'	=>	'suggestedpPayments_'.fix_date(date(DATE_FORMAT)),
					   'report'		=>	'PL_SuggestedPayments'
				);

		if(strtolower($status)=="dialog")
		{
			return $options;
		}

		// load the model
		$supplier_id='';
		$suppliers = new PLSupplierCollection($this->_templateobject);

		if (isset($this->_data['id']))
		{
			$supplier_id=$this->_data['id'];
		}
		$suppliers->paymentsList($supplier_id);

		// generate the xml and add it to the options array
		$options['xmlSource']=$this->generateXML(array('model'=>$suppliers,
													   'relationship_whitelist'=>array('transactions')
													  )
												);

		// execute the print output function, echo the returned json for jquery
		echo $this->constructOutput($this->_data['print'],$options);
		exit;

	}

}

// End of PlsuppliersController

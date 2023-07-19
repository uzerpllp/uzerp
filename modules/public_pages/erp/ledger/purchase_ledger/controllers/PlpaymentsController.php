<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PlpaymentsController extends printController
{

	protected $version = '$Revision: 1.3 $';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('PLPayment');
		
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$this->view->set('clickaction', 'view');
		
		$s_data = array();

// Set context from calling module

		$this->setSearch('pltransactionsSearch', 'payments', $s_data);
	    $this->_templateobject->orderby = ['created'];
		parent::index(new PLPaymentCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);
		
		$sidebarlist=array();
		
		$this->sidebarIndex($sidebarlist);

		$sidebar->addList('Actions',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
	}
	
	public function view()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$plpayment = $this->_uses[$this->modeltype];
		
		$pltransactions = new PLTransactionCollection();
		$pltransactions->paidList($plpayment->{$plpayment->idField});
		
		$this->view->set('pltrans', $pltransactions);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist=array();
		
		$this->sidebarIndex($sidebarlist);

		$this->sidebarAllPayments($sidebarlist);

		if ($plpayment->isNewStatus())
		{
			$sidebarlist['override'] = array(
							'tag'=>'Set/Unset Process Override',
							'link'=>array('modules'				=> $this->_modules
										 ,'controller'			=> $this->name
										 ,'action'				=> 'process_override'
										 ,$plpayment->idField	=> $plpayment->{$plpayment->idField}
										 )
				);
			
			if ($plpayment->getNoOutput() !== 't') {
				$sidebarlist['process_payment'] = array(
								'tag'=>'Process Payment',
								'link'=>array('modules'				=> $this->_modules
											,'controller'			=> $this->name
											,'action'				=> 'printDialog'
											,'printaction'			=> 'make_batch_payment'
											,$plpayment->idField	=> $plpayment->{$plpayment->idField}
											)
					);
			}
		}
		
		if ($plpayment->isProcessed())
		{
			$sidebarlist['remittances'] = array(
							'tag'=>'Reprint Remittances',
							'link'=>array('modules'		=> $this->_modules
										 ,'controller'	=> 'pltransactions'
										 ,'action'		=> 'select_remittances'
										 ,'id'			=> $plpayment->id
										 )
				);
		}
		
		$sidebar->addList('Actions',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);

		$r = $plpayment->getRemittanceOutputHeaders();
		$this->view->set('outputs', $r);
	}

	public function process_override()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$this->view->set('plpayment', $this->_uses[$this->modeltype]);
		
	}
	
	public function save_process_override()
	{
		if (!$this->checkParams($this->modeltype) || !$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$flash = Flash::Instance();
		
		$plpayment = $this->_uses[$this->modeltype];
		
		$override = empty($this->_data[$this->modeltype]['override'])?FALSE:TRUE;
		
		$no_output = empty($this->_data[$this->modeltype]['no_output'])?FALSE:TRUE;
		
		if ($plpayment->update($plpayment->id, array('override', 'no_output'), array($override, $no_output)))
		{
			$flash->addMessage('PL Payment override updated OK');
			
			sendTo($this->name, 'view', $this->_modules, array($plpayment->idField => $plpayment->{$plpayment->idField}));
		}
		
		$flash->addErrors('Error processing payment override');
		$this->refresh();
		
		
	}

	public function remittance_list()
	{
	
		$pltransactions = new PLAllocationCollection();
		$pltransactions->remittanceList($this->_data['id']);
	
		$this->view->set('pltrans', $pltransactions);
	
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist=array();
		
		$this->sidebarIndex($sidebarlist);

		$this->sidebarAllPayments($sidebarlist);

		$sidebarlist['viewpayment'] = array(
				'tag' => 'View Payment List',
				'link'=>array('modules'		=> $this->_modules
							 ,'controller'	=> $this->name
							 ,'action'		=> 'view'
							 ,'id'			=> $this->_data['payment_id']
				)
		);
		
		$sidebar->addList('actions',$sidebarlist);
		
		$sidebarlist=array();
		
		$sidebarlist['reprint_remittance'] = array(
				'tag' => 'Reprint Remittance',
				'link'=>array('modules'		=> $this->_modules
							 ,'controller'	=> 'pltransactions'
							 ,'action'		=> 'printDialog'
							 ,'printaction'	=> 'print_single_remittance'
							 ,'filename'	=> 'remittance_'.fix_date(date(DATE_FORMAT))
							 ,'id'			=> $this->_data['id']
				)
		);
		
		$sidebar->addList('reports',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	
	}
	
	public function select_for_payment ()
	{
		
		$errors=array();

		$supplier = DataObjectFactory::Factory('plsupplier');
		
		if (isset($this->_data['plmaster_id']))
		{
			$supplier->load($this->_data['plmaster_id']);
		}
		// Search
		$s_data=array();

		// Set context from calling module
		if ($supplier)
		{
			$s_data['plmaster_id'] = $supplier->id;
		}
		
		$params = DataObjectFactory::Factory('GLParams');
		$s_data['currency_id']=$params->base_currency();
		
		$paytype = DataObjectFactory::Factory('PaymentType');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('method_id', 'is not', 'NULL'));
		
		$paytypes = $paytype->getAll($cc);
		
		$paytype_ids = array_keys($paytypes);
		
		$s_data['payment_type_id'] = $paytype_ids[0];
		
		$this->setSearch('pltransactionsSearch', 'select_payments', $s_data);
		// End of search
		
		$cc='';
		
		if(isset($this->search))
		{
			$cc = new ConstraintChain();
			$cc = $this->search->toConstraintChain();
		}
		
		$transaction	= DataObjectFactory::Factory('PLTransaction');
		$transactions	= new PLTransactionCollection($transaction, 'pl_allocation_overview');
		
		$sh = new SearchHandler($transactions,false);

		$sh->addConstraint(new Constraint('status','=','O'));
		$sh->addConstraintChain($cc);
		
		$sh->setOrderby(array('supplier', 'our_reference'));

		$transactions->load($sh);

		$this->view->set('transactions',$transactions);
		$this->view->set('no_ordering',true);
				
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist=array();
		
		$this->sidebarIndex($sidebarlist);

		$this->sidebarAllPayments($sidebarlist);
		
		$sidebar->addList('Actions',$sidebarlist);
				
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}

	public function selected_payments()
	{

		$transaction	= DataObjectFactory::Factory('PLTransaction');
		$transactions	= new PLTransactionCollection($transaction, 'pl_allocation_overview');
		
		$transactions->summaryPayments();
		
		$this->view->set('transactions',$transactions);
		$this->view->set('num_records',$transactions->num_records);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist=array();
		
		$this->sidebarIndex($sidebarlist);

		$this->sidebarAllPayments($sidebarlist);
		
		$sidebar->addList('Actions',$sidebarlist);
				
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}

	public function selected_payments_list()
	{
		
		$errors=array();

		$supplier = DataObjectFactory::Factory('plsupplier');
		
		if (isset($this->_data['plmaster_id']))
		{
			$supplier->load($this->_data['plmaster_id']);
		}
		// Search
		$s_data=array();

		// Set context from calling module
		if ($supplier)
		{
			$s_data['plmaster_id']=$supplier->id;
		}
		
		if (isset($this->_data['currency_id']))
		{
			$s_data['currency_id']=$this->_data['currency_id'];
		}
		
		if (isset($this->_data['payment_type_id']))
		{
			$s_data['payment_type_id']=$this->_data['payment_type_id'];
		}
		
		$this->setSearch('pltransactionsSearch', 'paymentsSummary', $s_data);
		// End of search
		
		$cc='';
		if(isset($this->search))
		{
			$cc = new ConstraintChain();
			$cc = $this->search->toConstraintChain();
			$plmaster_id=$this->search->getValue('plmaster_id');
			
			if ($plmaster_id>0)
			{
				$this->view->set('id', $plmaster_id);
			}
			
			$currency_id=$this->search->getValue('currency_id');
			if ($currency_id>0)
			{
				$this->view->set('currency_id', $currency_id);
			}
			
			$payment_type_id=$this->search->getValue('payment_type_id');
			if ($payment_type_id>0)
			{
				$this->view->set('payment_type_id', $payment_type_id);
			}
		}
		
		$transaction	= DataObjectFactory::Factory('PLTransaction');
		$transactions	= new PLTransactionCollection($transaction, 'pl_allocation_overview');
		$transactions->forPayment($cc);
		
		if (isset($this->search) && ($this->isPrintDialog() || $this->isPrinting()))
		{
			$this->printCollection($transactions);
		}
		
		$this->view->set('transactions',$transactions);
		$this->view->set('num_records',$transactions->num_records);

		$cbaccounts = DataObjectFactory::Factory('CBAccount');
		
		$this->view->set('cbaccounts',array(''=>'None')+$cbaccounts->getAll());
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist=array();
		
		$this->sidebarIndex($sidebarlist);

		$this->sidebarAllPayments($sidebarlist);
		
		$sidebar->addList('Actions',$sidebarlist);
				
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}

	public function processPaymentsList()
	{
		$flash=Flash::Instance();
		$errors=array();
		
		if ($this->checkParams('PLTransaction'))
		{
			foreach ($this->_data['PLTransaction'] as $data)
			{
				$transaction = DataObjectFactory::Factory('PLTransaction');
				$transaction->update($data['id'], array('for_payment', 'include_discount'), Array((isset($data['for_payment'])?'true':'false'), (isset($data['include_discount'])?'true':'false')));
			}
		}

		$flash->addErrors($errors);
		
		if (!empty($supplier_id))
		{
				sendTo($this->name
				,'view'
				,$this->_modules
				,array('id'=>$supplier_id));
		}
		else
		{
			sendTo($this->name
				,'selected_payments'
				,$this->_modules);
		}
	}
	
	public function process_payments ($print_data, $options)
	{
		$db=DB::Instance();
		$db->StartTrans();
		$flash=Flash::Instance();
		$errors=array();
		if ($this->checkParams('id'))
		{
			$plpayment = DataObjectFactory::Factory('PLPayment');
			$plpayment->load($this->_data['id']);
			
			if (!$plpayment)
			{
				$errors[]='Error trying to get Payment Details';
			}
			else
			{
				$this->_data['cb_account_id']=$plpayment->cb_account_id;
				$pay_class=$plpayment->paymentClass();
				
				if (empty($pay_class) || !class_exists($pay_class))
				{
					$errors[]='No Payment Method defined for this Payment Type';
				}
				elseif (strtolower($this->_data['saveform'])=='test print')
				{
					$pay_class = new $pay_class($this);
					$pay_class->setData(1, $pay_class->testprint(), $errors, $this->_data, $plpayment);
					
					if (count($errors)>0 || !$pay_class->constructPrint())
					{
						$flash->addErrors($errors);
						$flash->addError('Test print for '.get_class($pay_class).' Failed');
					}
					else
					{
						$flash->addWarning('Check Printer for '.get_class($pay_class).' alignment');
					}
					
					$db->FailTrans();
					$db->CompleteTrans();
					$this->_data['printaction']='process_payments';
					$this->refresh();
					return;
				}
				else
				{
					$pay_class = new $pay_class($this);
					$hash =$plpayment->cb_account_id;
					$hash.=$plpayment->currency_id;
					$hash.=$plpayment->payment_type_id;
					$hash.=$plpayment->reference;
					$hash.=$plpayment->payment_date;
					$hash.=$plpayment->number_transactions;
					$hash.=$plpayment->payment_total;
		
					$pltransactions = new PLTransactionCollection();
					$pltransactions->paidList($plpayment->id);
					$trans_count=0;
					$trans_value=0;
					
					foreach ($pltransactions as $pl_data)
					{
						$trans_count=$trans_count+1;
						$payment_value=bcmul($pl_data->base_gross_value,-1,2);
						$trans_value=bcadd($trans_value,$payment_value);
						$hash.=$pl_data->plmaster_id;
						$hash.=bcadd($payment_value,0);
					}
					
					if ($plpayment->hash_key!=$plpayment->generateHashcode($hash)
					|| $trans_count!=$plpayment->number_transactions
					|| $trans_value!=$plpayment->payment_total)
					{
						$errors[]='Payments mismatch';
					}
				}
			}
			
			if (count($errors)==0 && $plpayment->no_output==='f' && $pay_class)
			{
				$pay_class->setData(1, $pltransactions, $errors, $this->_data, $plpayment);
				
				if (count($errors)==0 && !$pay_class->constructPrint($print_data, $options))
				{
					$errors[]='Process '.get_class($pay_class).' Failed';
				}
			}
			
			if (count($errors)==0)
			{
				if (!$plpayment->update($plpayment->id, 'status', 'P'))
				{
					$errors[]='Error trying to update Payment Details';
				}
			}

		}
		
		if (count($errors)>0)
		{
			$db->FailTrans();
			echo $this->returnResponse(false,array('message'=>implode("<br />",$errors)));
		}
		else
		{
			$db->CompleteTrans();
			echo $this->returnResponse(true,array('redirect'=>'/?module='.$this->module.'&controller='.$this->name.'&action=enter_payment_reference&id='.$plpayment->id));
		}
		exit;
	}
	
	public function select_for_output()
	{
		
		if (!$this->checkParams('type'))
		{
			sendBack();
		}
		
		if ($this->_data['type']=='remittance')
		{
			sendTo($this->name
				,'index'
				,$this->_modules
				);
		}
		
		parent::select_for_output();
		
	}
	
	public function save_payments ()
	{
		
		$flash=Flash::Instance();
		
		$errors=array();
		
		if ($this->checkParams('PLTransaction'))
		{
			$this->_data['source']				= 'P';
			$this->_data['transaction_date']	= $this->_data['payment_date'];
			$this->_data['number_transactions']	= count($this->_data['PLTransaction']);
			
			set_time_limit($this->_data['number_transactions']+10);
			
			$payment=PLPayment::Factory($this->_data, $errors);
		}
		else
		{
			sendBack();
		}
		
		if ($this->_data['payment_type_id'])
		{
			$paytype = DataObjectFactory::Factory('PaymentType');
			$paytype->load($this->_data['payment_type_id']);
			
			if ($paytype->isLoaded() && !is_null($paytype->payment_class->class_name))
			{
				$payclass = new $paytype->payment_class->class_name($this);
				$payclass->validate($this->_data, $errors);
				if (isset($payclass->no_output)) {
					$payment->no_output = 't';
				}
			}
			
		}
		
		if (count($errors)==0 && $payment && $payment->savePLPayment($this->_data, $errors))
		{
			if (isset($payclass->no_output)) {
				$paid_suppliers = array_keys($this->_data['PLTransaction']);
				$supplier_has_remittance = new PLSupplierCollection();
				$sh = new SearchHandler($supplier_has_remittance, false);
				$cc = new ConstraintChain();
				$cc->add(new Constraint('remittance_advice', 'IS', true));
				if (count($paid_suppliers) > 1) {
					$cc->add(new Constraint('id', 'in', '(' . implode(',', $paid_suppliers) . ')'));
				} else {
					$cc->add(new Constraint('id', '=', $paid_suppliers[0]));
				}
				$sh->addConstraintChain($cc);
				$supplier_has_remittance->load($sh);

				$payment->status = 'P';
				$payment->save();
				$flash->addMessage('Payment processed.');
				
				if (count($supplier_has_remittance) > 0) {
					// Allow the user to output a remittance advice
					// for suppliers that need it
					sendTo(
						'pltransactions',
						'select_remittances',
						$this->_modules,
						array('id'=>$payment->id)
					);
				}
			}

			// Not outputting remittance advice at this point.
			// Return to the payment list where the user can
			// select 'Proccess' to continue to the next stage.
			sendTo($this->name
				,'index'
				,$this->_modules);
		}
		else
		{
			$params=array();
			
			if (isset($this->_data['id']))
			{
				$params['id']=$this->_data['id'];
			}
			
			if (isset($this->_data['currency_id']))
			{
				$params['currency_id']=$this->_data['currency_id'];
			}
			
			if (isset($this->_data['payment_type_id']))
			{
				$params['payment_type_id']=$this->_data['payment_type_id'];
			}

			$callback = function($errors) {
				if (count($errors > 0)) {
					return false;
				}
			};
			
			// Kill off the progress bars
			$progressNames = [
				'checking_supplier_details',
				'creating_pl_transactions',
				'allocate_payments'
			];
			foreach($progressNames as $name) {
				$progressbar = new Progressbar($name);
				$progressbar->process([], $callback);
			}
		
			$flash->addErrors($errors);
			if (isset($payclass->no_output)) {
				sendBack();
			}
			$this->refresh();
		}
	}

	public function make_batch_payment ($status='generate')
	{
		
		// if we've encountered errors there's no point in continuing
		if(!empty($errors))
		{
			// print dialog will look out for the string "FAILURE" on the dialog, 
			// anything after the ":" will be used as the error message
			echo "FAILURE:".implode("<br />",$errors);
			exit;
		}
		
		// build options array
		$options=array('type'			=>	array('text'=>''),
					   'actions'		=>	array('save'=>''),
					   'filename'		=>	'BACS_'.fix_date(date(DATE_FORMAT)),
					   'requires_xml'	=>	false
				);
			
		if(strtolower($status)=="dialog")
		{
			return $options;
		}
		
		$this->process_payments($this->_data['print'], $options);
		exit;
		
	}
	
	public function update_pay_reference ()
	{

		$db=DB::Instance();
		$db->StartTrans();
		$flash=Flash::Instance();
		$errors=array();
		
		if ($this->checkParams('PLPayment'))
		{
			$plpayment = $this->_uses[$this->modeltype];
			$plpayment->load($this->_data['PLPayment']['id']);
			
			if (!$plpayment)
			{
				$errors[]='Error trying to get Payment Details';
			}
			else
			{
				if (isset($this->_data['start_reference'])
				&& isset($this->_data['end_reference'])
				&& $this->_data['end_reference']!=($this->_data['start_reference']+$plpayment->number_transactions-1))
				{
					$errors[]='Reference range does not match number of transactions';
				}
				else
				{
					$pltransactions = new PLTransactionCollection(DataObjectFactory::Factory('PLTransaction'));
					$pltransactions->paidList($plpayment->id);
					$ext_ref=$this->_data['start_reference'];

					$progressbar = new Progressbar('update_payment_reference');
		
					$callback = function($pl_data, $key) use (&$ext_ref, &$errors, $db)
					{
						$cbtrans = DataObjectFactory::Factory('CBTransaction');
						$cbtrans->loadBy(array('source'
								, 'reference'
								, 'ext_reference'
								, 'gross_value'),
								array('P'
										, $pl_data->our_reference
										, $pl_data->cross_ref
										, $pl_data->gross_value));
						
						if (!$cbtrans
								|| !$cbtrans->update($cbtrans->id, 'ext_reference', $ext_ref))
						{
							$errors[] = 'Error updating CB Transaction External Reference : '.$db->ErrorMsg();
							return FALSE;
						}
						
						if (!$pl_data->update($pl_data->id, 'ext_reference', $ext_ref))
						{
							$errors[] = 'Error updating PL Transaction External Reference : '.$db->ErrorMsg();
							return FALSE;
						}
						else
						{
							$ext_ref+=1;
						}
						
					};
					
					if ($progressbar->process($pltransactions, $callback)===FALSE)
					{
						$errors[] = 'Error updating payment reference';
					}
				
				}
			}
		}
		
		if (count($errors)>0)
		{
			$flash->addErrors($errors);
			$this->_templateName=$this->getTemplateName('enter_payment_reference');
			$this->view->set('PLPayment', $plpayment);
			$db->FailTrans();
			$db->CompleteTrans();
		}
		else
		{
			$db->CompleteTrans();
			sendTo('pltransactions'
				,'select_remittances'
				,$this->_modules
				,array('id'=>$plpayment->id)
				);
		}
	}
	
	public function enter_payment_reference()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$plpayment = $this->_uses[$this->modeltype];

	}

	/*
	 * Protected Functions
	 */
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'PL Payments':$base), $action);
	}
	
	/*
	 * Private Functions
	 */
	private function sidebarIndex (&$sidebarlist = array())
	{
		
		$sidebarlist['viewaccounts'] = array(
				'tag'=>'View All Suppliers',
				'link'=>array('modules'		=> $this->_modules
							 ,'controller'	=> 'plsuppliers'
							 ,'action'		=> 'index'
				)
		);
		
		$sidebarlist['select_for_payment'] = array(
				'tag'	=> 'select_for_payment',
				'link'	=> array('modules'		=> $this->_modules
								,'controller'	=> $this->name
								,'action'		=> 'select_for_payment'
				)
		);
		
		$sidebarlist['selected_payments'] = array(
				'tag'	=> 'selected_payments',
				'link'	=> array('modules'		=> $this->_modules
								,'controller'	=> $this->name
								,'action'		=> 'selected_payments'
				)
		);
		
	}
	
	private function sidebarAllPayments (&$sidebarlist = array())
	{
		
		$sidebarlist['viewpayments'] = array(
				'tag'=>'View All Payments',
				'link'=>array('modules'		=> $this->_modules
						,'controller'	=> $this->name
						,'action'		=> 'index'
				)
		);
		
	}
	
}

// End of PlpaymentsController

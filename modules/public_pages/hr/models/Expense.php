<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Expense extends DataObject
{

	protected $version = '$Revision: 1.21 $';
	
	public $defaultDisplayFields = array(
		'expense_number'	=> 'Expense Number',
		'employee'			=> 'Name',
		'expense_date'		=> 'Date',
		'our_reference'		=> 'Reference',
		'description'		=> 'Description',
		'gross_value'		=> 'Amount',
		'status'			=> 'Status'
	);
	
	public function __construct($tablename = 'expenses_header')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		
		// Define relationships
		$this->belongsTo('Currency', 'currency_id', 'currency');
		$this->belongsTo('Currency', 'twin_currency_id', 'twin_currency');
		$this->belongsTo('Project', 'project_id', 'project');
		$this->belongsTo('Task', 'task_id', 'task');
		$this->belongsTo('Employee', 'employee_id', 'employee');
		$this->hasOne('Employee', 'authorised_by', 'authorisor');
 		$this->hasMany('ExpenseLine', 'lines', 'expenses_header_id');
 		
		// Define field formats
		$params			= DataObjectFactory::Factory('GLParams');
		$base_currency	= $params->base_currency();
		
		$this->getField('base_net_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('base_tax_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('base_gross_value')->setFormatter(new CurrencyFormatter($base_currency));

		// Define field defaults
		$this->getField('status')->setDefault('W');	
		$this->_autohandlers['expense_number'] = new CompanyUniqueReferenceHandler('expenses_header', 'expense_number');
		
		// Define validation
		$this->validateUniquenessOf(array("expense_number"));

		// Define enumerated types
 		$this->setEnum('status',
 			array(
 				'A'	=> 'Authorised',
				'C'	=> 'Cancelled',
				'D'	=> 'Declined',
				'O'	=> 'Awaiting Payment',
				'P'	=> 'Paid',
				'W'	=> 'Awaiting Authorisation'
			)
		);
		
	}
	
	function cb_loaded()
	{
		
		// then set these formatters here because they depend on the loaded currency_id
		$this->getField('net_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		$this->getField('tax_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		$this->getField('gross_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		$this->getField('twin_net_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
		$this->getField('twin_tax_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
		$this->getField('twin_gross_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
		
	}

	public function currentBalance()
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('employee_id', '=', $this->employee_id));
		
		$cc->add(new Constraint('status', '=', 'W'));
		
		return $this->getSum('gross_value', $cc);				
	}

	public static function Factory($data, &$errors=array(), $do_name=null)
	{

		if (isset($data['currency_id']))
		{
			$currency = DataObjectFactory::Factory('Currency');
			
			$currency->load($data['currency_id']);
			
			if ($currency)
			{
				$data['rate'] = $currency->rate;
			}
		}
		
		if (!isset($data['rate']))
		{
			$errors[] = 'Cannot find currency rate';
		}
		
		//determine the twin currency
		$glparams = DataObjectFactory::Factory('GLParams');
		
		$twin_currency = DataObjectFactory::Factory('Currency');
		
		$twin_currency->load($glparams->twin_currency());
		
		$data['twin_rate']			= $twin_currency->rate;
		$data['twin_currency_id']	= $twin_currency->id;
		
//		header totals are built from the lines
//		so initialise the values to zero
		$sums = array('net_value', 'tax_value', 'gross_value');
		
		$prefixes = array('','twin_','base_');
		
		foreach($prefixes as $prefix)
		{
			foreach($sums as $sum)
			{
				$data[$prefix.$sum]=0;
			}
		}

		if (is_string($do_name))
		{
			$do_name = DataObjectFactory::Factory($do_name);
		}
		
		if (get_class($do_name) == __CLASS__)
		{
			$employee = DataObjectFactory::Factory('employee');
			
			$do_name->belongsTo('Employee', 'employee_id', 'employee', $employee->authorisationPolicy($employee->expense_model()));
		}
		
		$header = parent::Factory($data, $errors, $do_name);
		
		if (count($errors)>0 || !$header)
		{
			$errors[] = 'Error validating expense';
			return false;
		}

		return $header;

	}

	static function updateStatus ($data, &$errors)
	{
		$db = DB::Instance();
		$db->StartTrans();
		
		$employee = DataObjectFactory::Factory('employee');
		
		$expense = DataObjectFactory::Factory('Expense');
		
		// Need to add policy to FK for authoriser
		// otherwise DataObject::Factory will fail when checking FK
		$expense->belongsTo('Employee', 'employee_id', 'employee', $employee->authorisationPolicy($employee->expense_model()));
		
		$expense = DataObject::Factory($data, $errors, $expense);
		
		if (!$expense || !$expense->save())
		{
			$errors[] = 'Failed to update status : '.$db->ErrorMsg();
			$db->FailTrans();
		}
		
		return $db->CompleteTrans();
		
	}
	
	function awaitingAuthorisation ()
	{
		return ($this->status=='W');
	}

	function authorised ()
	{
		return ($this->status=='A');
	}

	function hasBeenPosted ()
	{
		return ($this->status=='O');
	}

	function paid ()
	{
		return ($this->status=='P');
	}

	function cancel ()
	{
		return 'C';
	}
	
	function statusAwaitingPayment ()
	{
		return 'O';
	}
	
	function statusAwaitingAuthorisation ()
	{
		return 'W';
	}
	
	function statusPaid ()
	{
		return 'P';
	}
	
	public function getNextLineNumber ()
	{
		
		$expenseline = DataObjectFactory::Factory('ExpenseLine');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('expenses_header_id', '=', $this->id));
		
		$max_line_number=$expenseline->getMax('line_number', $cc);
		
		if (empty($max_line_number))
		{
			$max_line_number = 0;
		}
		return $max_line_number + 1;
				
	}
	
	public function getOutstandingTransactions($extract=true, $cc='')
	{
		$transactions = new ELTransactionCollection();
		
		$sh = new SearchHandler($transactions,false);
		
		if($extract)
		{
			$sh->extract();
		}
		
		$sh->addConstraint(new Constraint('status','=','O'));
		
		if($this->id)
		{
			$sh->addConstraint(new Constraint('employee_id','=',$this->id));
		}
		
		if (!empty($cc) && $cc instanceOf ConstraintChain)
		{
			$sh->addConstraintChain($cc);
		}
		
		$sh->setOrderby(array('employee', 'our_reference'));
		
		$transactions->load($sh);
		
		return $transactions;
	}

	public function pay_claim($data, &$errors = array())
	{
		if($this->paid())
		{
			$errors[] = 'Expense Claim has already been paid';
			return false;
		}
		
		$db = DB::Instance();
		$db->StartTrans();

		// TODO: may be able to reuse some of this code
		// but see EmployeesController::save_payment and EmployeesController::save_allocation
		$gl_params = DataObjectFactory::Factory('GLParams');
		
		$data['glaccount_id'] = $gl_params->expenses_control_account();
		
		$data['glcentre_id'] = $gl_params->balance_sheet_cost_centre();
		
		$data['source']	= 'E';
		
		$result = ELTransaction::saveTransaction($data, $errors);
		
		// Match the payment to the expense claim
		
		if (empty($errors))
		{
			$exptrans = DataObjectFactory::Factory('ELTransaction');
			
			$exptrans->loadBy(array('employee_id', 'transaction_type', 'status', 'our_reference')
							 ,array($this->employee_id, 'E', 'O', $this->expense_number));
			
			if (!$exptrans->isLoaded())
			{
				$errors[] = 'Error matching Expense Transaction to Payment';
				$db->FailTrans();
			}
			
			$paytrans = DataObjectFactory::Factory('ELTransaction');
			
			$paytrans->load($data['ledger_transaction_id']);
			
			if (!$paytrans->isLoaded())
			{
				$errors[] = 'Error loading payment : '.$db->ErrorMsg();
				$db->Failtrans();
			}
			else
			{
				$base_total = bcmul($exptrans->gross_base_total, $paytrans->gross_base_total);
				
				$paytrans->status = $exptrans->status = 'P';
				
				$exptrans->os_value			= 0;
				$exptrans->twin_os_value	= 0;
				$exptrans->base_os_value	= 0;
				$exptrans->for_payment		= 'f';
				
				// Update the EL Transactions - Expense and matching Payment
				if (!$exptrans->saveForPayment($errors) || !$paytrans->saveForPayment($errors))
				{
					$db->Failtrans();
				}
			}
		}
		
		// Check for any currency conversion credit/debit
		if (empty($errors) && $base_total!=0)
		{
			$data = array();
			
			$data['docref']	= $this->employee_id;
			$data['value']	= $base_total*-1;
			
			if (!ELTransaction::currencyAdjustment($data, $errors))
			{
				$db->FailTrans();
			}
		}
		
		// Update the Expense Claim header status
		if (empty($errors))
		{
			$this->status = $this->statusPaid();
			
			if (!$this->save())
			{
				$message = $db->ErrorMsg();
				
				if (!empty($message))
				{
					$errors[] = $message;
				}
				
				$db->FailTrans();
			}
		}
		
		return $db->CompleteTrans();
		
	}
	
	public function post(&$errors = array())
	{
		if($this->hasBeenPosted())
		{
			$errors[] = 'Expense has already been posted';
			return false;
		}
		
		$db = DB::Instance();
		$db->StartTrans();

		$transaction = DataObjectFactory::Factory('ELTransaction');
		
		$trans_id = $db->GenID('ELTransactions_id_seq');

		//copy across the fields that are needed
		foreach($this->getFields() as $fieldname=>$field)
		{
			if($transaction->isField($fieldname))
			{
				$transaction->$fieldname = $this->$fieldname;
			}
		}
		
		$transaction->id				= $trans_id;
		$transaction->transaction_type	= 'E';
		$transaction->status			= 'O';
		$transaction->our_reference		= $this->expense_number;
		
		$prefixes = array('','base_','twin_');
		
		//the outstanding (os) values are the gross values to begin with
		foreach($prefixes as $prefix)
		{
			$transaction->{$prefix.'os_value'} = $transaction->{$prefix.'gross_value'};
		}
		
		$transaction->transaction_date = $this->expense_date;
		
		$result = $transaction->save($this, $errors);
		
		// Create and save the GL Transactions
		if ($result)
		{
			$gl_transactions = GLTransaction::makeFromExpenseTransaction($transaction, $this, $errors);
			
			if (!is_array($gl_transactions) || count($errors)>0)
			{
				$result = false;
			}
			else
			{
				$result = GLTransaction::saveTransactions($gl_transactions, $errors);
			}
		}
		
		// Update the Expense Claim header status
		if ($result)
		{
			$this->status = $this->statusAwaitingPayment();
			
			$result = $this->save();
		}
		
		if (!$result)
		{
			$message = $db->ErrorMsg();
			
			if (!empty($message))
			{
				$errors[] = $message;
			}
			
			$db->FailTrans();
		}
		
		$db->CompleteTrans();
		
		return $result;
	}
	
	public function save($debug)
	{
		
		$expenseline = DataObjectFactory::Factory('ExpenseLine');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('expenses_header_id', '=', $this->id));		
		
		$totals = $expenseline->getSumFields(
				array(
						'gross_value',
						'tax_value',
						'net_value',
						'twin_gross_value',
						'twin_tax_value',
						'twin_net_value',
						'base_gross_value',
						'base_tax_value',
						'base_net_value'
					),
					$cc,
					'expenses_lines'
				);
				
		unset($totals['numrows']);
		
		// set the correct totals back to the order header
		foreach($totals as $field=>$value)
		{
			$this->$field=(empty($value))?0.00:bcadd($value,0);
		}
				
		return parent::save($debug);
		
	}
	
}

// End of Expense
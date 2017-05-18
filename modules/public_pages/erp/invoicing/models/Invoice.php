<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
abstract class Invoice extends DataObject {
	
	protected $version='$Revision: 1.27 $';
	
	private $unsaved_lines=array();

	protected $multipliers = array('I'=>1
								  ,'C'=>-1
								  );
	
	/**
	 *  Build the invoice header, as if from a submitted form
	 *
	 */
	public static function makeHeader($data,$do,&$errors) {

		if (!isset($data['id']) || $data['id']=='') {

			$generator = new UniqueNumberHandler(false, ($data['transaction_type']!='T'));
			$data['invoice_number'] = $generator->handle(DataObjectFactory::Factory($do));
		
			$data['status'] = 'N';
		}

		//determine the base currency
		$currency = DataObjectFactory::Factory('Currency');
		$currency->load($data['currency_id']);
		$data['rate'] =$currency->rate;

		//determine the twin currency
		$glparams = DataObjectFactory::Factory('GLParams');
		$twin_currency = DataObjectFactory::Factory('Currency');
		$twin_currency->load($glparams->twin_currency());
		$data['twin_rate'] = $twin_currency->rate;
		$data['twin_currency_id'] = $twin_currency->id;
		
		$terms = DataObjectFactory::Factory('PaymentTerm');
		$terms->load($data['payment_term_id']);
		
		$today = date(DATE_FORMAT);
		
		if (empty($data['invoice_date']))
		{
			$data['invoice_date'] = $today;
		}
		
		if ($data['transaction_type']=='I')
		{
			$data['due_date'] = calc_due_date($data['invoice_date'],$terms->basis,$terms->days,$terms->months);
		}
		else
		{
			
			if (fix_date($data['due_date']) < fix_date($today))
			{
				$data['due_date'] = $today;
			}
		}
		
		$data['original_due_date'] = $data['due_date'];
		//		build the lines - header totals are built from the lines
//		so initialise the values to zero
		$sums = array('net_value', 'tax_value', 'gross_value');
		$prefixes = array('','twin_','base_');
		foreach($prefixes as $prefix) {
			foreach($sums as $sum) {
				$data[$prefix.$sum]='0.00';
			}
		}
		
		return DataObject::Factory($data, $errors, $do);
	}

	public function save($debug=false) {
		$db=DB::Instance();
		$db->StartTrans();
		$result = parent::save($debug);
		if($result===false) {
			$db->FailTrans();
		}
		$db->CompleteTrans();
		return $result;
	}
	
	/**
	 *  Post an invoice to the Sales ledger
	 *  - saving to SLTransaction automatically saves to General Ledger
	 */
	public function post(&$errors=array())
	{
		if($this->hasBeenPosted())
		{
			$errors[]='Invoice has already been posted';
			return false;
		}
		
		$db=DB::Instance();
		$db->StartTrans();
		
		// reload the invoice to refresh the dependencies
		$this->load($this->id);
		
		// Validate Header and Line Values for following nine value fields
		// (1) Net + Tax = Gross
		// (2) Sum of line values = header value
		$fields=array('gross_value', 'net_value', 'tax_value'
					, 'base_gross_value', 'base_net_value', 'base_tax_value'
					, 'twin_gross_value', 'twin_net_value', 'twin_tax_value');
		
		$linesum=array();
		
		foreach ($fields as $field)
		{
			$linesum[$field]=0;
		}
		
		// Get sum of values for the lines by field
		foreach ($this->lines as $line)
		{
			if (bcadd($line->net_value, $line->tax_value)!=$line->gross_value
				|| bcadd($line->base_net_value, $line->base_tax_value)!=$line->base_gross_value
				|| bcadd($line->twin_net_value, $line->twin_tax_value)!=$line->twin_gross_value)
			{
				$errors[]='Line '.$line->line_number.' values mismatch (net+tax=>gross)';
			}
			elseif (!$line->update($line->id, 'glaccount_centre_id', 'null'))
			{
				$errors[]='Error updating invoice line '.$line->line_number;
			}
			foreach ($fields as $field)
			{
				$linesum[$field]=bcadd($linesum[$field], $line->$field);
			}
		}
		
		// Check line sum against header
		foreach ($linesum as $field=>$value)
		{
			if ($this->$field!=$value)
			{
				$errors[]=$field.' line sum ('.$value.') does not match header value '.$this->$field;
			}
		}
		
		if (bcadd($this->net_value, $this->tax_value)!=$this->gross_value
			|| bcadd($this->base_net_value, $this->base_tax_value)!=$this->base_gross_value
			|| bcadd($this->twin_net_value, $this->twin_tax_value)!=$this->twin_gross_value) {
			$errors[]='Header values mismatch (net+tax=>gross)';
		}
		
		// Create and save the Ledger Transaction
		if (count($errors)==0)
		{
//			$transaction = LedgerTransaction::makeFromInvoice($this);
			$transaction = $this->makeLedgerTransaction();
			
			if ($transaction!==false)
			{
				$result = $transaction->save($this, $errors);
			}
			else
			{
				$result = false;
			}
		}
		
		// Create and save the GL Transactions
		if ($result)
		{
//			$gl_transactions = GLTransaction::makeFromLedgerTransaction($transaction, $this, $errors);
			$gl_transactions = $this->makeGLTransactions($errors);
				
			if (!is_array($gl_transactions) || count($errors)>0)
			{
				$result = false;
			}
			else
			{
				$result = GLTransaction::saveTransactions($gl_transactions, $errors);
			}
		}
		
		// Update the Invoice header status
		if ($result)
		{
			$this->status = 'O';
			
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
	
	public function hasBeenPosted() {
		return ($this->status!='N');
	}

	public function hasBeenPostednotPaid() {
		return ($this->status=='O');
	}

	public function onQuery() {
		return ($this->status=='Q');
	}

	public function hasBeenPaid() {
		return ($this->status=='P');
	}

	public function updateStatus($invoice_number, $status) {
		$cc=new ConstraintChain();
		$cc->add(new Constraint('invoice_number', '=', $invoice_number));
		$this->loadBy($cc);
		if ($this->isLoaded() && $this->update($this->id, 'status', $status)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getMultipliers() {
		return $this->multipliers;
	}

	protected function getNextLineNumber ($_invoiceline) {
		
		$cc=new ConstraintChain();
		$cc->add(new Constraint('invoice_id', '=', $this->id));
		$max_line_number=$_invoiceline->getMax('line_number', $cc);
		if (empty($max_line_number)) {
			$max_line_number=0;
		}
		return $max_line_number+1;

	}
	
	function getFormatted($name, $html = TRUE)
	{
		$value = parent::getFormatted($name, $html);
		
		if ($name=='invoice_number' && $this->transaction_type=='T') {
			return $this->transaction_type.$value;
		}
		
		return $value;
		
	}
	
	public function getSettlementDiscount()
	{
		return $this->payment_term->calcSettlementDiscount($this->gross_value) ;
	}

	/*
	 * Protected Functions
	 */
	protected function makeGLTransactions(&$errors=array())
	{
		
		$newerrors = array();
		
		//sort out the header details
		$gl_transactions = array();
		$gl_data		 = array();
		
		//the gl docref is the invoice number
		$gl_data['docref']		= $this->invoice_number;
		$gl_data['reference']	= $this->our_reference;
		
		//dates should be the same
		$gl_data['transaction_date'] = un_fix_date($this->invoice_date);
		
		//first character of class identifies source
		$gl_data['source'] = substr(strtoupper(get_class($this)),0,1);
		
		//type depends on Invoice or Credit Note
		$gl_data['type'] = $this->transaction_type;
		
		//the description is one from a number of bits of information
		//(description is compulsory for GL, but the options aren't for SLTransaction and SInvoice)
		$desc			= $this->description;
		$ext_ref		= $this->ext_reference;
		$sales_order_id	= $this->sales_order_id;
		
		if(!empty($desc))
		{
			$header_desc = $desc;
		}
		elseif(!empty($ext_ref))
		{
			$header_desc = $ext_ref;
		}
		elseif(!empty($sales_order_id))
		{
			$header_desc = $sales_order_id;
		}
		else
		{
			$header_desc = $this->invoice_number;
		}
		
		$gl_data['comment'] = $header_desc;
		
		//another docref
		$gl_data['docref2'] = $sales_order_id;
		
		// set the period based on invoice date
		$glperiod = GLPeriod::getPeriod($this->invoice_date);
		if ((!$glperiod) || (count($glperiod) == 0))
		{
			$errors[] = 'No period exists for this date';
			return false;
		}
		$gl_data['glperiods_id'] = $glperiod['id'];
		
		$gl_data['twin_currency_id'] = $this->twin_currency_id;
		$gl_data['twin_rate']		 = $this->twin_rate;
		
		//there needs to be a tax element
		$gl_data['base_tax_value'] = $this->base_tax_value;
		$gl_data['twin_tax_value'] = $this->twin_tax_value;
		
		$vat_element = GLTransaction::makeCBTax($gl_data, $newerrors);
		
		if($vat_element!==false)
		{
			$gl_transactions[]=$vat_element;
		}
		else
		{
			$errors+=$newerrors;
			return false;
		}
		
		//this is the control element (used to balance the tax and lines)
		$gl_data['base_gross_value'] = $this->base_gross_value;
		$gl_data['twin_gross_value'] = $this->twin_gross_value;
		
		$gl_params = DataObjectFactory::Factory('GLParams');
		
		$gl_data['glaccount_id'] = $this->get_ledger_control_account($gl_params, $errors);
		
		if ($gl_data['glaccount_id'] === FALSE)
		{
			return FALSE;
		}
		
		$gl_data['glcentre_id'] = $this->get_balance_sheet_cost_centre($gl_params, $errors);
		
		if ($gl_data['glcentre_id'] === FALSE)
		{
			return FALSE;
		}
		
		$control = GLTransaction::makeCBControl($gl_data, $newerrors);
		
		if($control!==false)
		{
			$gl_transactions[]=$control;
		}
		else
		{
			$errors+=$newerrors;
			return false;
		}
		
		// Now do the GL Transactions for each invoice line
		$this->makeGLTransactionLines($gl_data, $gl_transactions, $errors);
		
		return $gl_transactions;
	
	}
	
	protected function makeGLTransactionLines($gl_data, &$gl_transactions, &$errors = array())
	{
		
		$newerrors = array();
		
		foreach($this->lines as $line)
		{
			
			// Set common gl data for the line
			$line->makeGLTransactions($gl_data);
			
			$element = GLTransaction::makeCBLine($gl_data, $newerrors);
			
			if($element!==FALSE)
			{
				$gl_transactions[]=$element;
			}
			else
			{
				$errors+=$newerrors;
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	protected function get_balance_sheet_cost_centre($gl_params = null, &$errors=array())
	{
		
		if (!($gl_params instanceof GLParams))
		{
			$gl_params = DataObjectFactory::Factory('GLParams');
		}
		
		$glcentre_id = $gl_params->balance_sheet_cost_centre();
		
		if ($glcentre_id===false)
		{
			$errors[]='Balance Sheet Cost Centre Code not found';
		}
		
		return $glcentre_id;
	
	}
	
	/*
	 * Private Functions
	 */
	private function makeLedgerTransaction()
	{
	
		$transaction = $this->transactionFactory();
		
		//copy across the fields that are needed
		foreach($this->getFields() as $fieldname=>$field)
		{
			if($transaction->isField($fieldname) && $fieldname != $transaction->idField)
			{
				$transaction->$fieldname = $this->$fieldname;
			}
		}
		
		$transaction->transaction_type	= substr($this->transaction_type,0,1);
		$transaction->our_reference		= $this->invoice_number;
		$transaction->status			= 'O';
		
		if ($transaction->transaction_type == 'C')
		{
			foreach($transaction->getFields() as $fieldname=>$field)
			{
				if(substr($fieldname,-5,5) == 'value') {
					$transaction->$fieldname = bcmul($transaction->$fieldname, -1);
				}
			}
		}
		
		$prefixes = array('', 'base_', 'twin_');
		//the outstanding (os) values are the gross values to begin with
		
		foreach($prefixes as $prefix)
		{
			$transaction->{$prefix.'os_value'} = $transaction->{$prefix.'gross_value'};
		}
		
		$transaction->transaction_date	= $this->invoice_date;
//		$transaction->due_date			= $this->due_date;
		$transaction->created			= $transaction->autohandle('created');
		$transaction->createdby			= $transaction->autohandle('alteredby');
		$transaction->lastupdated		= $transaction->autohandle('lastupdated');
		$transaction->alteredby			= $transaction->autohandle('alteredby');
		
		return $transaction;
	}
	
}

// End of Invoice

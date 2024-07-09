<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PLTransaction extends LedgerTransaction
{

	protected $version = '$Revision: 1.27 $';
	
	protected $defaultDisplayFields = array('our_reference'
											,'supplier'
											,'transaction_date'
											,'ext_reference'
											,'due_date'
											,'gross_value'
											,'os_value'
											,'currency'
											,'transaction_type'
											,'status'
											,'for_payment'
											,'plmaster_id');
	
	protected static $multipliers = array(
		'P'=>array(
			'I'=>1,
			'C'=>-1,
			'J'=>1,
			'P'=>-1,
			'RP'=>1,
			'SD'=>-1
			)	
	);

	function __construct($tablename = 'pltransactions')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
				
// Set specific characteristics
		
// Define relationships
		$this->belongsTo('PLSupplier', 'plmaster_id', 'supplier');
 		
// Define field formats
		
// Define system defaults
		
// Define enumerated types
		$this->setEnum('transaction_type'
							,array('I'	=> 'Invoice'
								  ,'C'	=> 'Credit Note'
								  ,'J'	=> 'Journal'
								  ,'P'	=> 'Payment'
								  ,'RP'	=> 'Refund Payment'
								  ,'SD'	=> 'Settlement Discount'
								  )
						);
		
	}
		
	public function getOwner()
	{
		$supplier = DataObjectFactory::Factory('PLSupplier');
			
		$supplier->load($this->plmaster_id);
	
		return $supplier;
	}
	
	function getRemittance (&$_data=array(), &$model=array(), &$extra=array(), &$errors=array())
	{
		
		// load the model
		$supplier = DataObjectFactory::Factory('PLSupplier');
		$supplier->load($this->plmaster_id);
		
		$_data['email_subject']	= $supplier->name.' Remittance '.$this->transaction_date; 
		$_data['filename']		= 'Remittance-'.$this->id;
		
		// get the payment method
		$plpayment = DataObjectFactory::Factory('PLPayment');
		$plpayment->load($this->cross_ref);
		
		// get the remittance list
		$pltransactions = new PLAllocationCollection(DataObjectFactory::Factory('PLAllocation'));
		$pltransactions->remittanceList($this->id);

		$model = array($supplier, $pltransactions);
		
		// set date
		// a bit messy nesting in so many functions
		$extra['date'] = un_fix_date(fix_date(date(DATE_FORMAT)));
		
		// set company name
		$company = DataObjectFactory::Factory('Company');
		$company->load(COMPANY_ID);
		
		$extra['company_name'] = $company->name;
				
		// set company address
		$company_address = array('name'=>$company->name);
		
		$output	= array();
		$parts	= array("street1","street2","street3","town","county","postcode","country");
		$address = $company->getAddress();
		
		foreach($parts as $part)
		{
			if(!is_null($address->$part))
			{
				$output[$part]=$address->$part;
			}	
		}
		
		$company_address+=$output;
		$extra['company_address'] = $company_address;
		
		// set the company details
		$email = $company->getContactDetail('E', 'REMITTANCE');
		
		if (empty($email))
		{
			$email = $company->getContactDetail('E');
		}
		
		$_data['replyto'] = $email;
		$extra['company_details']=array('tel'=>'Tel: '.$company->getContactDetail('T'),
										'fax'=>'Fax: '.$company->getContactDetail('F'),
										'email'=>'Email: '.$email,
										'vat_number'=>'VAT Number: '.$company->vatnumber,
										'company_number'=>'Company Number: '.$company->companynumber);
		
		// set supplier address
		$supplier_address = array('name'=>$supplier->name);
		
		$output = array();
		$parts	= array("street1","street2","street3","town","county","postcode","country");
		$address = $supplier->getBillingAddress();
		
		foreach($parts as $part)
		{
			if(!is_null($address->$part))
			{
				$output[$part]=$address->$part;
			}	
		}
		
		$supplier_address+=$output;
		$extra['supplier_address'] = $supplier_address;
		
		// set document details
		$document_reference=array();
		$document_reference[]['line'] = array('label'=>'Payment Date','value'=>un_fix_date($this->transaction_date));
		$document_reference[]['line'] = array('label'=>'Payment Value','value'=>bcmul($this->gross_value,-1,2));
		$document_reference[]['line'] = array('label'=>'Currency','value'=>$plpayment->currency);
		$document_reference[]['line'] = array('label'=>'Payment Method','value'=>$plpayment->payment_type);
		$extra['document_reference'] = $document_reference;
		
	}
	
	public function payee_name_check ()
	{
		return str_replace(array('&','(',')','#','"',"'",'.',',','!','*','+','-','/'), '', $this->payee_name);	
	}
	
	static function allocatePayment ($transactions, $supplier_id, &$errors = [])
	{
		$db=DB::Instance();
		$db->StartTrans();
		
		$total		= 0;
		$base_total	= 0;
		
		foreach($transactions as $id=>$value)
		{
			$trans = DataObjectFactory::Factory('PLTransaction');
			$trans->load($id);
			
			$total			 = bcadd($total,$value);
			$base_total		 = bcadd($base_total,$trans->base_os_value);
			$trans->os_value = bcsub($trans->os_value, $value);
			
			$trans_store[]	 = $trans;
		}
			
		if($total==0)
		{
			foreach($trans_store as $transaction)
			{
				if ($transaction->os_value==0)
				{
					$transaction->status		= $transaction->paid();
					$transaction->twin_os_value	= 0.00;
					$transaction->base_os_value	= 0.00;
				}
				else
				{
					$transaction->status = $transaction->partPaid();
					
					$transaction->setTwinBaseValue('os_value');
				}
				
				$base_total = bcsub($base_total,$transaction->base_os_value);
				
				$transaction->for_payment		= 'f';
				$transaction->include_discount	= 'f';
				
				$result = $transaction->saveForPayment($errors);
				
				if ($result===false)
				{
					$errors[] = 'Error saving transaction';
					$db->FailTrans();
					$db->CompleteTrans();
					return false;				
				}
				
				if ($transaction->os_value==0
				&&	($transaction->transaction_type=='C'
					|| $transaction->transaction_type=='I'))
				{
					$invoice = DataObjectFactory::Factory('PInvoice');
					
					if (!$invoice->updateStatus($transaction->our_reference, 'P'))
					{
						$errors[] = 'Error updating Invoice';
						$db->FailTrans();
						$db->CompleteTrans();
						return false;		
					}
				}
			}
			
			if ($base_total!=0)
			{
				$data = array();
				
				$data['docref']			 = $supplier_id;
				$data['original_source'] = 'P';
				$data['reference']		 = '';
				$data['value']			 = $base_total*-1;
				$data['comment']		 = 'Purchase Allocation Currency Adjustment';
				
				if (!GLTransaction::currencyAdjustment($data, $errors))
				{
					$db->FailTrans();
					$db->CompleteTrans();
					return false;
				}
			}
		}
		else
		{
			$errors[] = 'Transactions must sum to zero';
			$db->FailTrans();
			$db->CompleteTrans();
			return false;
		}

		return $db->CompleteTrans();

	}
	
}

// End of PLTransaction

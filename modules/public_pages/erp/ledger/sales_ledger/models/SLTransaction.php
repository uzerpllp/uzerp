<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SLTransaction extends LedgerTransaction
{

	protected $version = '$Revision: 1.18 $';
	
	protected $defaultDisplayFields = array('our_reference'
											,'customer'
											,'person'
											,'transaction_date'
											,'ext_reference'
											,'due_date'
											,'gross_value'
											,'os_value'
											,'currency'
											,'transaction_type'
											,'status'
											,'slmaster_id'
											,'person_id');
	
	protected static $multipliers = array(
		'S'=>array(
			'I'=>1,
			'C'=>-1,
			'J'=>1,
			'R'=>-1,
			'RR'=>1,
			'SD'=>-1
			)	
	);

	function __construct($tablename='sltransactions')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		
		// Define relationships
		$this->belongsTo('SLCustomer', 'slmaster_id', 'customer');
		$this->belongsTo('Person', 'person_id', 'person', null, "surname || ', ' || firstname");
		
		// Define field formats
		
		// set formatters, more set in load() function
		
		// Define enumerated types
		$this->setEnum('transaction_type'
						  ,array('I'	=> 'Invoice'
								,'C'	=> 'Credit Note'
								,'J'	=> 'Journal'
								,'R'	=> 'Receipt'
								,'RR'	=> 'Refund Receipt'
								,'SD'	=> 'Settlement Discount'
								)
					  );
		
		// Do not allow links to the following
	
	}
	
	public function getOwner()
	{
		$customer = DataObjectFactory::Factory('SLCustomer');
			
		$customer->load($this->slmaster_id);
	
		return $customer;
	}
	
	static function allocatePayment ($transactions, $customer_id, &$errors = [])
	{
		$db=DB::Instance();
		$db->StartTrans();
		
		$total = 0;
		
		$base_total = 0;
		
		foreach($transactions as $id=>$value)
		{
			$trans = DataObjectFactory::Factory('SLTransaction');
			
			$trans->load($id);
			
			$total = bcadd($total,$value);
			
			$base_total = bcadd($base_total,$trans->base_os_value);
			
			$trans->os_value = bcsub($trans->os_value, $value);
			
			$trans_store[] = $trans;
		}
			
		if($total == 0)
		{
			foreach($trans_store as $transaction)
			{
				if ($transaction->os_value == 0)
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
				
				$base_total = bcsub($base_total, $transaction->base_os_value);
				
				$result = $transaction->saveForPayment($errors);
				
				if ($result === false)
				{
					$errors[] = 'Error saving transaction';
					
					$db->FailTrans();
					$db->CompleteTrans();
					return false;
				}
				
				if ($transaction->os_value == 0
					&&	($transaction->transaction_type == 'C'
						|| $transaction->transaction_type == 'I'))
				{
					$invoice =  DataObjectFactory::Factory('SInvoice');
					
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
				
				$data['docref']			 = $customer_id;
				$data['original_source'] = 'S';
				$data['reference']		 = '';
				$data['value']			 = $base_total;
				$data['comment']		 = 'Sales Allocation Currency Adjustment';
				
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

// End of SLTransaction

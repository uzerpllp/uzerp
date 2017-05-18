<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PLAllocation extends DataObject
{

	protected $version = '$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('supplier'
											,'payee_name'
											,'transaction_date'
											,'transaction_type'
											,'payment_value'
											,'remittance_advice'
											);

	function __construct($tablename = 'pl_allocation_details')
	{
// Register non-persistent attributes
 		$this->setAdditional('transaction_type');
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		
// Define relationships
		$this->belongsTo('PLTransaction', 'pl_transaction_id', 'transaction');
		$this->belongsTo('PLPayment', 'pl_payment_id', 'batch_payment');
		
// Define field formats

// Define system defaults
		
// Define enumerated types
		$this->setEnum('transaction_type'
							,array('I'	=> 'Invoice'
								  ,'C'	=> 'Credit Note'
								  ,'J'	=> 'Journal'
								  ,'P'	=> 'Payment'
								  ,'R'	=> 'Reciept'
								  ,'SD'	=> 'Settlement Discount'
								  )
						);
		
	}

	static function saveAllocation ($transactions, $payment_id, &$errors)
	{
		$db=DB::Instance();
		
		$db->StartTrans();
		
		$alloc_id = $db->GenID('pl_allocation_id_seq');
		
		foreach($transactions as $id=>$value)
		{
//			$trans = new PLTransaction();
//			$trans->load($id);
			$data = array('allocation_id'	=> $alloc_id,
						'transaction_id'	=> $id,
						'payment_id'		=> $payment_id,
						'payment_value'		=> $value);
			
			$alloc = DataObject::Factory($data, $errors, 'PLAllocation');
			
			if (count($errors) > 0 || !$alloc->save())
			{
				break;
			}
		}
		
		if (count($errors) > 0)
		{	
			$db->FailTrans();
			$db->CompleteTrans();
			return false;
		}
		
		return $db->CompleteTrans();
	}

}

// End of PLAllocation

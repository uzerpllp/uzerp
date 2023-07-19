<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PLPayment extends DataObject
{

	protected $version = '$Revision: 1.25 $';
	
	protected $defaultDisplayFields = array('payment_date'
											,'status'
											,'reference'
											,'number_transactions'
											,'bank_account'
											,'payment_type'
											,'currency'
											,'payment_total');
	
	function __construct($tablename = 'pl_payments')
	{
		parent::__construct($tablename);
		
		$this->idField='id';
		
		$this->orderby='payment_date';
		$this->orderdir='DESC';
		
 		$this->belongsTo('CBAccount', 'cb_account_id', 'bank_account');
		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('PaymentType', 'payment_type_id', 'payment_type');

		$this->setEnum('status'
							,array('N'=>'New'
								  ,'C'=>'Cancelled'
								  ,'P'=>'Processed'
								)
						);
		
 		$this->getField('status')->setDefault('N');
	}

	public static function Factory ($data, &$errors = [], $do_name = null)
	{

		$hash =$data['cb_account_id'];
		$hash.=$data['currency_id'];
		$hash.=$data['payment_type_id'];
		$hash.=$data['reference'];
		$hash.=fix_date($data['payment_date']);
		$hash.=$data['number_transactions'];
		$hash.=$data['payment_total'];

		$progressbar = new Progressbar('create_security_key');
		
		$callback = function($pl_data, $key) use (&$hash, &$errors)
		{
			$hash.=$pl_data['plmaster_id'];
			$hash.=bcadd($pl_data['net_value'],0);
		};
						
		$progressbar->process($data['PLTransaction'], $callback);
		
		$data['hash_key'] = self::generateHashcode($hash);
		
		return parent::Factory($data, $errors, 'PLPayment');
		
	}

	public static function generateHashcode($hash)
	{
		 return base64_encode(hash('sha1', $hash, false));
	}
	
	public function savePLPayment ($pay_data, &$errors) 
	{
		$db = DB::Instance();
		$db->StartTrans();

//		Save the Payment Header
		if (!parent::save())
		{
			$errors[] = 'Failed to save Payment Header';
			$db->FailTrans();
			return false;
		}

		$flash = Flash::Instance();
		
//		Validate and write purchase ledger, cashbook and general ledger transactions

		$progressbar = new Progressbar('creating_pl_transactions');
		
		$payment_id = $this->id;
		
		$callback = function($data, $key) use (&$pay_data, &$errors, $payment_id)
		{
			$data['cb_account_id']		= $pay_data['cb_account_id'];
			$data['reference']			= $pay_data['reference'];
			$data['cross_ref']			= $data['ext_reference'] = $payment_id;
			$data['description']		= $pay_data['description'];
			$data['transaction_type']	= 'P';
			$data['transaction_date']	= $pay_data['transaction_date'];
			$data['source']				= $pay_data['source'];
			
			$supplier = DataObjectFactory::Factory('PLSupplier');
			$supplier->load($data['plmaster_id']);
			
			$data['payment_term_id']	= $supplier->payment_term_id;
			
			if (PLTransaction::saveTransaction($data, $errors)===false)
			{
				$errors[] = 'Failed to save payments';
				return false;
			}
				
			$pay_data['PLTransaction'][$key]['ledger_transaction_id'] = $data['ledger_transaction_id'];
					
		};
			
		if ($progressbar->process($pay_data['PLTransaction'], $callback)===FALSE)
		{
			$db->FailTrans();
			$db->CompleteTrans();
			return FALSE;
		}
			
//		Match and update purchase ledger payments
		$payment_total = 0;
				
		$progressbar = new Progressbar('allocate_payments');
		
		$callback = function($data, $key) use (&$pay_data, &$errors, $payment_id, &$payment_total)
		{
			$db = DB::Instance();
			// Get the payment transaction and update it to paid
			$pltransaction = DataObjectFactory::Factory('PLTransaction');
			$pltransaction->load($data['ledger_transaction_id']);
			
			if (!$pltransaction->update($pltransaction->id
                                         , array('status', 'os_value', 'twin_os_value', 'base_os_value')
                                         , array('P', '0.00', '0.00', '0.00')))
			{
				$errors[] = 'Error updating payment status : '.$db->ErrorMsg();
				return false;
			}
			
			// get all the transactions linked for payment to the payment transaction
			$pltransactions	= new PLTransactionCollection(DataObjectFactory::Factory('PLTransaction'), 'pl_allocation_overview');
			
			$pltransactions->getPaid($data);
			
			// the allocation amount is the gross payment value
			$allocations = array($pltransaction->id=>$pltransaction->gross_value);
			
			$trans_total		= 0;
			$trans_base_total	= $pltransaction->base_gross_value;
			
			foreach ($pltransactions as $trans)
			{
				// now mark all the linked transactions as paid
				// update the invoices linked to thes transactions as paid
				$trans_total		= bcadd($trans->gross_value, $trans_total);
				$trans_base_total	= bcadd($trans->base_gross_value, $trans_base_total);
				
				if (!$trans->update($trans->id
								  , array('status', 'for_payment', 'os_value', 'twin_os_value', 'base_os_value')
								  , array($trans->Paid(), false, '0.00', '0.00', '0.00')))
				{
					$errors[] = 'Error updating transaction status : '.$db->ErrorMsg();
					return false;
				}
				
				if ($trans->transaction_type == 'C'
					|| $trans->transaction_type  == 'I')
				{
					$invoice = DataObjectFactory::Factory('PInvoice');
					
					if (!$invoice->updateStatus($trans->our_reference, 'P'))
					{
						$errors[] = 'Error updating Invoice : '.$db->ErrorMsg();
						return false;
					}
				}
				
				$allocations[$trans->id] = $trans->gross_value;
				
				// Save settlement discount if present?
				if ($trans->settlement_discount>0 && $trans->include_discount=='t')
				{
					// Create GL Journal for settlement discount
					
					$discount = array();
					
					$discount['gross_value'] = $discount['net_value'] = $trans->settlement_discount;
					
					$discount['glaccount_id']	= $trans->pl_discount_glaccount_id;
					$discount['glcentre_id']	= $trans->pl_discount_glcentre_id;
					
					$discount['transaction_date']	= date(DATE_FORMAT);
					$discount['tax_value']			= '0.00';
					$discount['source']				= 'P';
					$discount['transaction_type']	= 'SD';
					$discount['our_reference']		= $trans->our_reference;
					$discount['ext_reference']		= $trans->ext_reference;
					$discount['currency_id']		= $trans->currency_id;
					$discount['rate']				= $trans->rate;
					$discount['description']		= (!is_null($trans->pl_discount_description)?$trans->pl_discount_description.' ':'');
					$discount['description']		.=(!is_null($trans->description)?$trans->description:$trans->ext_reference);
					$discount['payment_term_id']	= $trans->payment_term_id;
					$discount['plmaster_id']		= $trans->plmaster_id;
					$discount['status']				= 'P';
					
					$pldiscount = PLTransaction::Factory($discount, $errors, 'PLTransaction');
					
					if ($pldiscount && $pldiscount->save('', $errors) && $pldiscount->saveGLTransaction($discount, $errors))
					{
						$allocations[$pldiscount->{$pldiscount->idField}]	= bcadd($discount['net_value'], 0);
						
						$trans_total		= bcadd($trans_total, $pldiscount->gross_value);
						$trans_base_total	= bcadd($trans_base_total, $pldiscount->base_gross_value);
					}
					else
					{
						$errors[] = 'Errror saving PL Transaction Discount : '.$db->ErrorMsg();
					}
					
				}
			
			}
			
			if ($data['net_value'] != $pltransaction->gross_value*-1
				|| $data['net_value'] != $trans_total)
			{
				$errors[] = 'Transaction Payment mismatch '.$data['net_value'].' '.($pltransaction->gross_value*-1).' '.$trans_total.' for '.$trans->supplier;
				return false;
			}
			
			// save the allocations
			if (!PLAllocation::saveAllocation($allocations, $payment_id, $errors))
			{
				return false;
			}
			
			if ($trans_base_total!=0)
			{
				$adj_data	= array();
				$errors		= array();
				
				$adj_data['docref']			 = $pltransaction->plmaster_id;
				$adj_data['original_source'] = 'P';
				$adj_data['reference']		 = '';
				$adj_data['value']			 = $trans_base_total*-1;
				$adj_data['comment']		 = 'Purchase Allocation Currency Adjustment';
				
				if (!GLTransaction::currencyAdjustment($adj_data, $errors))
				{
					return false;
				}
			}
				
			$payment_total = bcadd($payment_total, $trans_total);
			
		};
			
		if ($progressbar->process($pay_data['PLTransaction'], $callback)===FALSE)
		{
			$db->FailTrans();
			$db->CompleteTrans();
			return FALSE;
		}
		
		if ($payment_total<>$pay_data['payment_total'])
		{
				$errors[] = 'Payment Mismatch - Total '.$pay_data['payment_total'].' not equal sum Transaction Payments '.$payment_total;
				$db->FailTrans();
				$db->CompleteTrans();
				return false;
		}
		return $db->CompleteTrans();
		
	}
	
	public function paymentClass()
	{
		if ($this->isLoaded())
		{
			$paytype = DataObjectFactory::Factory('PaymentType');
			$paytype->load($this->payment_type_id);
			return $paytype->payment_class->class_name;
		}
		return null;
	}

	/**
	 * Get no_output value
	 * 
	 * no_output is not in the collection DB view.
	 * Not likely needed elsewhere and I'm too lazy
	 * to do a migration to add it in so that it
	 * appears in collections :-)
	 *
	 * @return bool
	 */
	public function getNoOutput()
	{
		if (!isset($this->no_output)) {
			$plpayment = new PLPayment();
			$plpayment->load($this->id);
			return $plpayment->no_output;
		}
		return $this->no_output;
	}
	
	public function isNewStatus()
	{
		return ($this->status == 'N');
	}
	
	public function isProcessed()
	{
		return ($this->status == 'P');
	}

	public function getRemittanceOutputHeaders()
	{
		// SQL to get the output header ids related to
		// this PLPayment via the associated PLTransactions.
		// Match the PLTransaction.ext_reference to the PLPayment.id
		$SQL = "select distinct od.output_header_id, oh.created
		from pltransactions as plt
		join output_details od on od.select_id = plt.id
		left join output_header as oh on oh.id = od.output_header_id
		where plt.ext_reference = ? and oh.type = 'remittance'
		order by oh.created";

		$db = DB::Instance();
		$query = $db->prepare($SQL);
		$values = [$this->id];
		$result = $db->execute($query, $values);
		return $result;
	}
	
}

// End of PLPayment

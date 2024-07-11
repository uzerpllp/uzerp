<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class LedgerTransaction extends DataObject
{

	protected $version='$Revision: 1.33 $';
	
//	public static $multipliers;

	public $base_currency_id;
	public $base_currency_name;
	
	function __construct($tablename)
	{
// Register non-persistent attributes
		$glparams = DataObjectFactory::Factory('GLParams');
		$this->base_currency_id=$glparams->base_currency();
		
		$base_currency = DataObjectFactory::Factory('Currency');
		$base_currency->load($this->base_currency_id);
		
		$this->base_currency_name=$base_currency->currency;
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		
		$this->orderby='transaction_date';
		$this->orderdir='DESC';
			
// Define relationships
		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency_id', 'twin');
 		$this->belongsTo('PaymentTerm', 'payment_term_id', 'payment_term');

		
// Define field formats

// Define enumerated types
		$this->setEnum('status'
							,array('N'=>'New'
								  ,'O'=>'Open'
								  ,'Q'=>'Query'
								  ,'R'=>'Part Paid'
								  ,'P'=>'Paid'
								)
						);
 		
	}
	
	// Appears not to be used
	
	// public static function Factory(&$data, &$errors, $do)
	// {
	
	// 	$data['due_date'] = $data['transaction_date'];
		
	// 	if(isset($data['ext_reference']) && !isset($data['our_reference']))
	// 	{
	// 		$data['our_reference'] = $data['ext_reference'];
	// 	}
	// 	elseif(isset($data['reference']) && !isset($data['our_reference']))
	// 	{
	// 		$data['our_reference'] = $data['reference'];
	// 	}
	
	// 	if(isset($data['description']) && !isset($data['comment']))
	// 	{
	// 		$data['comment'] = $data['description'];
	// 	}
		
	// 	if(isset($data['comment']) && !isset($data['description']))
	// 	{
	// 		$data['description'] = $data['comment'];
	// 	}
	
	// 	$mult				= static::$multipliers[$data['source']][$data['transaction_type']];
	// 	$data['net_value']	= $data['net_value']*$mult;
	
	// 	self::setCurrency($data);
	
	// 	//the outstanding (os) values are the gross values to begin with
	// 	$prefixes = array('','base_','twin_');
	
	// 	foreach($prefixes as $prefix)
	// 	{
	// 		$data[$prefix.'os_value'] = $data[$prefix.'gross_value'];
	// 	}
		
	// 	// Validate the Ledger Transactions
	// 	if (empty($data['status']))
	// 	{
	// 		$data['status'] = 'O';
	// 	}

	// 	return parent::Factory($data, $errors, $do);
	// }
	
	public function saveForPayment(&$errors = array())
	{
		if (parent::save() === false)
		{
			$db=DB::Instance();
			$errors[] = 'Error saving transaction : '.$db->errorMsg();
			return FALSE;
		}
		
		return $this->update_owner_balance($errors);
	}
	
	public function save($debug = false)
	{
		
		if (!parent::save())
		{
			return false;
		}
		
		return $this->update_owner_balance();	

	}
	
	public static function saveTransaction(&$data, &$errors = [])
	{
		$db=DB::Instance();
		$db->StartTrans();
		
		// Validate the Ledger Transactions
		$trans = self::Factory($data, $errors, get_called_class());
		
		// Need to save the ledger transaction after the CB, as this adds information
		// and before the GL which references the ledger transaction
		if (!$trans 
			|| !$trans->saveCBTransaction($data, $errors)
			|| !$trans->saveForPayment($errors)
			|| !$trans->saveGLTransaction($data, $errors))
		{
			$errors[] = $db->ErrorMsg();
			$db->FailTrans();
		}
		
		return $db->CompleteTrans();
	}
	
	public function saveCBTransaction(&$data, &$errors = [])
	{
		//	Need to write cash transactions to cashbook
		if ($data['transaction_type']=='P'
			|| $data['transaction_type']=='RP'
			|| $data['transaction_type']=='R'
			|| $data['transaction_type']=='RR')
		{
			
			$db=DB::Instance();
			$db->StartTrans();
			
			$data['company_id'] = $this->getOwner()->company_id;
			
			if(isset($data['cb_account_id']))
			{
				$bank_account = DataObjectFactory::Factory('CBAccount');
				$bank_account->load($data['cb_account_id']);
				
				$data['glaccount_id']	= $bank_account->glaccount_id;
				$data['glcentre_id']	= $bank_account->glcentre_id;
			}
			
			$data['net_value']	= ($data['transaction_type']=='R' || $data['transaction_type']=='RR')?$data['net_value']*-1:$data['net_value'];
			$data['type']		= $data['transaction_type'];
			
			if (!CBTransaction::savePayment($data, $errors))
			{
				$errors[] = 'Error saving Cashbook entry';
				$db->FailTrans();
			}
			
			$this->our_reference = $data['our_reference'] = $data['reference'];
			
			if(isset($data['description']) && !isset($data['comment']))
			{
				$this->comment = $data['comment'] = $data['description'];
			}
			
			return $db->CompleteTrans();
		}
		
		return true;
	}
		
	public function saveGLTransaction(&$data, &$errors = [])
	{

		$db=DB::Instance();
		$db->StartTrans();
		
		//		Write to General Ledger
		$gl_trans = GLTransaction::makeFromLedgerJournal($this, $data, $errors);
		
		if ($gl_trans===false || !GLTransaction::saveTransactions($gl_trans, $errors))
		{
			$db->FailTrans();
		}
		else
		{
			//		Return the Ledger Transaction Id
			$data['ledger_transaction_id']	= $this->id;
			$data['payment_value']			= $this->gross_value;
		}
		
		return $db->CompleteTrans();
	}
	
	public static function setCurrency (&$data)
	{
		
		if (empty($data['rate']))
		{
			$currency = DataObjectFactory::Factory('Currency');
			$currency->load($data['currency_id']);
			
			$data['rate'] = $currency->rate;
		}
		
		$glparams = DataObjectFactory::Factory('GLParams');
		$twin_currency = DataObjectFactory::Factory('Currency');
		$twin_currency->load($glparams->twin_currency());
		$data['twin_rate'] = $twin_currency->rate;
		$data['twin_currency_id'] = $twin_currency->id;
		
		if(empty($data['tax_value']))
		{
			$data['tax_value'] = 0;
		}

		$data['gross_value'] = bcadd($data['net_value'],$data['tax_value']);
		
		$data['basecurrency_id'] = $glparams->base_currency();
		
		if ($data['basecurrency_id'] == $data['currency_id'])
		{
			$data['base_gross_value']	= $data['gross_value'];
			$data['base_tax_value']		= $data['tax_value'];
			$data['base_net_value']		= $data['net_value'];
		}
		else
		{
			$data['base_gross_value']	= bcadd(round(bcdiv($data['gross_value'],$data['rate'],4),2),0);
			$data['base_tax_value']		= bcadd(round(bcdiv($data['tax_value'],$data['rate'],4),2),0);
			$data['base_net_value']		= bcadd(round(bcsub($data['base_gross_value'],$data['base_tax_value']),2),0);
		}
		
		if ($twin_currency->id==$data['currency_id']
			&& $twin_currency->rate == $data['rate'])
		{
			$data['twin_gross_value']	= $data['gross_value'];
			$data['twin_tax_value']		= $data['tax_value'];
			$data['twin_net_value']		= $data['net_value'];
		}
		elseif ($twin_currency->id == $base_currency->id)
		{
			$data['twin_gross_value']	= $data['base_gross_value'];
			$data['twin_tax_value']		= $data['base_tax_value'];
			$data['twin_net_value']		= $data['base_net_value'];
		}
		else
		{
			$data['twin_gross_value']	= bcadd(round(bcmul($data['base_gross_value'],$data['twin_rate'],4),2),0);
			$data['twin_tax_value']		= bcadd(round(bcmul($data['base_tax_value'],$data['twin_rate'],4),2),0);
			$data['twin_net_value']		= bcadd(round(bcsub($data['twin_gross_value'],$data['twin_tax_value']),2),0);
		}
		
	}

	public function setTwinBaseValue ($fieldname)
	{
		
		$basefield='base_'.$fieldname;
		$twinfield='twin_'.$fieldname;
		
		if ($this->currency->rate==1)
		{
			$this->$basefield = $this->$fieldname;
		}
		else
		{
			// use bcadd to format result
			$this->$basefield = bcadd(round(bcdiv($this->$fieldname,$this->rate,4),2),0);
		}
		
		if ($this->twin_currency_id==$this->currency_id)
		{
			$this->$twinfield = $this->$fieldname;
		}
		else
		{
			// use bcadd to format result
			$this->$twinfield = bcadd(round(bcmul($this->$basefield,$this->twin_rate,4),2),0);
		}

	}
	
	public function outstandingBalance($cc = null)
	{
		if (empty($cc))
		{
			$cc = new ConstraintChain();
		}
		
		$cc->add(new Constraint('status', '!=', 'N'));
		$cc->add(new Constraint('status', '!=', 'P'));
		
		return $this->getSum('os_value', $cc);
	}
	
	/*
	 * Statuses
	 */
	public function open()
	{
		return 'O';
	}
	
	public function partPaid()
	{
		return 'R';
	}
	
	public function Paid()
	{
		return 'P';
	}
	
	public function Query()
	{
		return 'Q';
	}
	
	/*
	 * Transaction Types
	 */
	public function invoice()
	{
		return 'I';
	}
	
	public function creditNote()
	{
		return 'C';
	}
	
	public function journal()
	{
		return 'J';
	}
	
	public function payment()
	{
		return 'P';
	}
	
	public function receipt()
	{
		return 'R';
	}
	
	/*
	 * Private Functions
	 */
	private function update_owner_balance(&$errors = array())
	{
		
		$owner = $this->getOwner();
		
		if (!$owner->isLoaded())
		{
			$errors[] = 'Error loading '.get_class($owner).' to update balance';
			return FALSE;
		}
		
		if (!$owner->updateBalance($this))
		{
			$db = DB::Instance();
			$errors[] = 'Error updating '.get_class($owner).' balance : '.$db->ErrorMsg();
			return FALSE;
		}
		
		return TRUE;
	}
}

// End of LedgerTransaction

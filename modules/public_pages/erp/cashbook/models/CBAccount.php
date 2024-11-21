<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CBAccount extends DataObject
{
	
	protected $version = '$Revision: 1.13 $';
	
	protected $defaultDisplayFields = array('name'
											,'primary_account'
											,'description'
											,'bank_name'
											,'bank_account_name'
											,'bank_address'
											,'bank_iban_number' => 'IBAN'
											,'bank_bic_code' => 'BIC/Swift'
											,'currency'
											,'balance');
	
	function __construct($tablename='cb_accounts')
	{
// Register non-persistent attributes

// Construct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField='id';
		
// Define validation
		$this->validateUniquenessOf('name');
		$this->getField('glaccount_id')->addValidator(new PresenceValidator());
		$this->getField('glcentre_id')->addValidator(new PresenceValidator());
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre', array('glaccount_id'=>'glaccount_id', 'glcentre_id'=>'glcentre_id')));
		
// Define relationships
		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 
		$this->hasOne('Currency', 'currency_id', 'currency_detail');
 		
// Define field formats
		
// Define enumerated types

// Define system defaults
		$this->getField('balance')->setDefault('0.00');
		$this->getField('statement_balance')->setDefault('0.00');
		$this->getField('statement_page')->setDefault('0');
		
	}

	public function updateBalance(CBTransaction $cb_trans)
	{
		$amount = $cb_trans->gross_value;
		
		$db = DB::Instance();
		
		$db->StartTrans();
		
		$this->balance = bcadd($this->balance,$amount);
		
		if($this->save()!==false)
		{
			return $db->CompleteTrans();
		}
		
		return false;		
	}
	
	public function glbalance()
	{
		
		// return last unclosed period, year
		$glperiod = DataObjectFactory::Factory('GLPeriod');
		
		$glperiod->getCurrentPeriod();	
		
		$year = $glperiod->year;

		// get future periods from the last period 0
		$temp_future_periods=$glperiod->getFuturePeriods(-1, $year);

		// reconstruct periods array, as we cannot send a value (year + period), but period_id
		$future_periods = array_keys($temp_future_periods);
		
		array_fill_keys($future_periods, NULL);

		// get sum of periods
		$glb = new GLBalance();
		$sum = $glb->getSum($future_periods, $this->_data['glaccount_id'], $this->_data['glcentre_id']);
		
		return $sum;
	}

	function revalue($data, &$errors = array())
	{
		
		if (empty($data['transaction_date']))
		{
			$data['transaction_date'] = date(DATE_FORMAT);
		}
		
		$glperiod = GLPeriod::getPeriod(fix_date($data['transaction_date']));
		
		if ((!$glperiod) || (count($glperiod) == 0))
		{
			$errors[] = 'No period exists for this date';
			return false;
		}
		
		$data['value'] = bcsub($this->glbalance(), $data['new_balance']);
		
		$data['glperiods_id'] = $glperiod['id'];
		
		$data['source']	= 'C';
		$data['type']	= 'V';
		
		$data['glaccount_id']	= $this->currency_detail->writeoff_glaccount_id;
		$data['glcentre_id']	= $this->currency_detail->glcentre_id;
		
		GLTransaction::setTwinCurrency($data);
		
		$gl_trans[] = GLTransaction::Factory($data, $errors);
		
		$data['value']			= bcmul($data['value'], -1);
		$data['glaccount_id']	= $this->glaccount_id;
		$data['glcentre_id']	= $this->glcentre_id;
		
		GLTransaction::setTwinCurrency($data);
		
		$gl_trans[] = GLTransaction::Factory($data, $errors);
		
		$db = DB::Instance();
		$db->StartTrans();
		
		if (count($errors)==0
			&& GLTransaction::saveTransactions($gl_trans, $errors))
		{
			return $db->CompleteTrans();
		}
		
		$errors[] = 'Failed to save GL Transaction';
		
		$db->FailTrans();
		$db->CompleteTrans();
		
		return false;
		
	}

	public static function getPrimaryAccount()
	{
		$account = DataObjectFactory::Factory('CBAccount');
		
		$account->loadBy('primary_account', TRUE);
		
		return $account;
	}
	
	public function getDefaultAccount($_account_id)
	{
		// Load the primary account if it exists
		$this->loadBy('primary_account', 't');
		
		if (!$this->isLoaded())
		{
			// otherwise load the account of the supplier account id
			$this->load($_account_id);
		}
	}
	
}

// End of CBAccount

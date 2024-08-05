<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ELTransaction extends LedgerTransaction {

	protected $version='$Revision: 1.12 $';

	protected $defaultDisplayFields = array('employee'
											,'our_reference'
											,'transaction_date'
											,'ext_reference'
											,'gross_value'
											,'currency'
											,'transaction_type'
											,'status'
											,'for_payment');

	public static $multipliers = array(
		'E'=>array(
			'E'=>1,
			'C'=>-1,
			'J'=>1,
			'P'=>-1
			)	
	);

	function __construct($tablename='eltransactions')
	{

		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField = 'id';

		$this->orderby = 'transaction_date';
		$this->orderdir = 'DESC';

		// Define relationships
		$this->belongsTo('Employee', 'employee_id', 'supplier');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency_id', 'twin');

		// Define field formats

		// set formatters, more set in load() function

		// Define enumerated types
 		$this->setEnum('transaction_type'
							,array('E'=>'Expense'
								  ,'C'=>'Expense Credit'
								  ,'J'=>'Journal'
								  ,'P'=>'Payment'
								)
						);

		$this->setEnum('status'
							,array('N'=>'New'
								  ,'O'=>'Open'
								  ,'Q'=>'Query'
								  ,'P'=>'Paid'
								)
						);

		// Define default values

		// Define field formatting

		// Define link rules for related items

	}

	public function getOwner()
	{
		$employee = DataObjectFactory::Factory('Employee');
		// If we are here, then need access to employee
		// by overriding any policy constraints
		$employee->clearPolicyConstraint();

		$employee->load($this->employee_id);

		return $employee;
	}

	public static function currencyAdjustment (&$data, &$errors = [])
	{
		$data['original_source'] = 'E';
		$data['reference']		 = '';
		$data['comment']		 = 'Expense Allocation Currency Adjustment';

		$db = DB::Instance();

		$db->startTrans();

		if (!GLTransaction::currencyAdjustment($data, $errors))
		{
			$db->FailTrans();
		}

		return $db->CompleteTrans();

	}

}

// End of ELTransaction

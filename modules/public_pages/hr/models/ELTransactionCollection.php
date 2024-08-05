<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ELTransactionCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.5 $';

	public $field;

	function __construct($do = 'ELTransaction', $tablename = 'eltransactionsoverview')
	{

		parent::__construct($do, $tablename);

	}

	function forPayment($cc='')
	{
		$sh = new SearchHandler($this, false);

		$sh->addConstraint(new Constraint('for_payment', 'is', 'true'));
		$sh->addConstraint(new Constraint('status', '=', 'O'));

		if (!empty($cc) && $cc instanceof ConstraintChain)
		{
			$sh->addConstraintChain($cc);
		}

		$fields = array('employee_id', 'employee', 'company_id', 'currency', 'payment_type', 'currency_id', 'payment_type_id');

		$sh->setGroupBy($fields);

		$sh->setOrderby('supplier');

		$fields[] = 'sum(os_value) as payment';
		$sh->setFields($fields);

		$this->load($sh);		
	}

	function getPaid ($data)
	{
		$sh = new SearchHandler($this, false);

		$sh->addConstraint(new Constraint('for_payment', 'is', 'true'));
		$sh->addConstraint(new Constraint('status', '=', 'O'));
		$sh->addConstraint(new Constraint('employee_id', '=', $data['employee_id']));
		$sh->addConstraint(new Constraint('currency_id', '=', $data['currency_id']));
		$sh->addConstraint(new Constraint('payment_type_id', '=', $data['payment_type_id']));

		$this->load($sh);		
	}

	function summaryPayments()
	{
		$sh = new SearchHandler($this, false);

		$sh->addConstraint(new Constraint('for_payment', 'is', 'true'));
		$sh->addConstraint(new Constraint('status', '=', 'O'));

		$fields = array('currency', 'payment_type', 'currency_id', 'payment_type_id');

		$sh->setGroupBy($fields);

		$sh->setOrderby($fields);

		$fields[] = 'sum(os_value) as payment';
		$fields[] = 'count(*) as records';

		$sh->setFields($fields);

		$this->load($sh);		
	}

	function paidList($payment_id)
	{
		$sh = new SearchHandler($this, false);

		$sh->addConstraint(new Constraint('status', '=', 'P'));
		$sh->addConstraint(new Constraint('transaction_type', '=', 'P'));
		$sh->addConstraint(new Constraint('cross_ref', '=', $payment_id));

		$sh->setOrderby(array('employee', 'ext_reference'));

		$this->load($sh);		
	}

	function remittanceList($trans_id)
	{
		$sh = new SearchHandler($this, false);

		$sh->addConstraint(new Constraint('status', '=', 'P'));
		$sh->addConstraint(new Constraint('cross_ref', '=', $trans_id));

		$sh->setOrderby('transaction_date');

		$this->load($sh);		
	}

}

// End of ELTransactionCollection

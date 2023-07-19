<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PLTransactionCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.16 $';
	
	public $field;
	
	protected $agedBalances = array();
	
	public $agedMonths = 6;
	
	function __construct($do = 'PLTransaction', $tablename = 'pltransactionsoverview')
	{
		parent::__construct($do, $tablename);
			
	}
	
	function forPayment($cc = '')
	{
		$sh = new SearchHandler($this, false);
		
		$sh->addConstraint(new Constraint('for_payment', 'is', 'true'));
		$sh->addConstraint(new Constraint('status', '=', 'O'));
		
		if (!empty($cc) && $cc instanceof ConstraintChain)
		{
			$sh->addConstraintChain($cc);
		}
		
		$fields=array('plmaster_id', 'supplier', 'payee_name', 'company_id', 'currency', 'payment_type', 'currency_id', 'payment_type_id');
		
		$sh->setGroupBy($fields);
		
		$sh->setOrderby('supplier');
		
		$fields[]='sum(os_value-cast(include_discount as integer)*coalesce(settlement_discount,0)) as payment';
		
		$sh->setFields($fields);
		
		$this->load($sh);		
	}

	function getPaid ($data)
	{
		$sh = new SearchHandler($this, false);
		
		$sh->addConstraint(new Constraint('for_payment', 'is', 'true'));
		$sh->addConstraint(new Constraint('status', '=', 'O'));
		$sh->addConstraint(new Constraint('plmaster_id', '=', $data['plmaster_id']));
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
		
		// Sum the os_value of all rows marked for_payment
		// less the settlement_discount for those rows marked for_payment and include_discount
		$fields[] = 'sum(os_value-cast(include_discount as integer)*coalesce(settlement_discount,0)) as payment';
		$fields[] = 'count(*) as records';
		
		$sh->setFields($fields);
		
		$this->load($sh);		
	}

	function paidList($payment_id, $requires_remittance_advice=false)
	{
		$sh = new SearchHandler($this, false);
		
		$sh->addConstraint(new Constraint('status', '=', 'P'));
		$sh->addConstraint(new Constraint('transaction_type', '=', 'P'));
		$sh->addConstraint(new Constraint('cross_ref', '=', $payment_id));

		if ($requires_remittance_advice) {
			$sh->addConstraint(new Constraint('remittance_advice', 'IS', true));
		}
		
		$sh->setOrderby(array('supplier', 'ext_reference'));
		
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

	function agedSummary()
	{
		$this->_tablename = 'pl_aged_creditors_summary';
		
		$this->orderby = array('id');
		
		$sh = new SearchHandler($this, false);
		
		$this->load($sh);
		
		$this->agedBalances['Total'] = 0;
		
		for ($i=0; $i<=$this->agedMonths; $i++)
		{
			$this->agedBalances[$i] = 0;
		}
		
		$total = 0;
		
		foreach ($this as $agedcreditors)
		{
			$total=bcadd($total, $agedcreditors->value);
			
			if ($agedcreditors->id > $this->agedMonths)
			{
				$this->agedBalances[$this->agedMonths] += $agedcreditors->value;
			}
			else
			{
				$this->agedBalances[$agedcreditors->id] = $agedcreditors->value;
			}
		}
		
		$this->agedBalances['Total'] = $total;
		
		return $this->agedBalances;
	}

}

// End of PLTransactionCollection

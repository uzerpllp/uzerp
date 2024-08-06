<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class pltransactionsSearch extends BaseSearch
{

	protected $version = '$Revision: 1.17 $';
	protected $fields = array();

	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new pltransactionsSearch($defaults);

		$pltrans = DataObjectFactory::Factory('PLTransaction');

// Search by Customer
		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			0,
			'advanced'
			);
		$supplier	= DataObjectFactory::Factory('PLSupplier');
		$options	= array('0'=>'All');
		$suppliers	= $supplier->getAll(null, false, true);
		$options	+=$suppliers;
		$search->setOptions('plmaster_id',$options);

// Search by Invoice Number
		$search->addSearchField(
			'our_reference',
			'our_reference',
			'equal',
			'',
			'advanced'
		);

// Search by Sales Order Number
		$search->addSearchField(
			'transaction_date',
			'transaction_date_between',
			'between',
			'',
			'advanced'
		);

// Search by Customer Reference
		$search->addSearchField(
			'ext_reference',
			'customer reference',
			'equal',
			'',
			'advanced'
		);

// Search by Invoice Date
		$search->addSearchField(
			'due_date',
			'due_date_between',
			'between',
			'',
			'advanced'
		);

// Search by Transaction Type
		$search->addSearchField(
			'transaction_type',
			'transaction_type',
			'select',
			'',
			'advanced'
			);
		$options=array_merge(array(''=>'All')
						  	,$pltrans->getEnumOptions('transaction_type'));
		$search->setOptions('transaction_type',$options);

// Search by Status
		$search->addSearchField(
			'status',
			'status',
			'select',
			'',
			'advanced'
			);
		$options=array_merge(array(''=>'All')
						  	,$pltrans->getEnumOptions('status'));
		$search->setOptions('status',$options);

		$search->setSearchData($search_data,$errors);

		return $search;
	}

	public static function payments($search_data = null,&$errors = array(), $defaults = null)
	{
		$search = new pltransactionsSearch($defaults);

		$search->account('basic');
		$search->currency('basic', TRUE);
		$search->payment_type('basic', TRUE);
		$search->payment_date();

		// Search by Status
		$search->addSearchField(
				'status',
				'status',
				'multi_select',
				array(),
				'advanced'
		);

		$options = array(''=>'All');
		$status = DataObjectFactory::Factory('PLPayment');
		$options += $status->getEnumOptions('status');
		$search->setOptions('status',$options);

		// Search by Reference
		$search->addSearchField(
				'reference',
				'reference_contains',
				'contains',
				'',
				'advanced'
		);

		$search->setSearchData($search_data,$errors,'payments');

		return $search;

	}

	public static function select_payments($search_data = null,&$errors = array(), $defaults = null)
	{
		$search = new pltransactionsSearch($defaults);

		$search->supplier();
		$search->currency('basic');
		$search->payment_type('basic');
		$search->due_date('basic');

		$search->setSearchData($search_data,$errors,'select_payments');

		return $search;

	}

	public static function paymentsSummary($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new pltransactionsSearch($defaults);

		$search->supplier();
		$search->currency();
		$search->payment_type();

		$search->setSearchData($search_data,$errors,'paymentsSummary');

		return $search;

	}

	private function account($group = 'advanced', $all = FALSE)
	{
	// Search by Currency
		$this->addSearchField(
			'cb_account_id',
			'account',
			'select',
			'',
			$group
		);

		$account = DataObjectFactory::Factory('CBAccount');
		$options = array(''=>'All');
		$options += $account->getAll();
		$this->setOptions('cb_account_id',$options);
	}

	private function currency($group = 'advanced', $all = FALSE)
	{
	// Search by Currency
		$this->addSearchField(
			'currency_id',
			'currency',
			'select',
			'',
			$group
		);

		if ($all)
		{
			$options = array(''=>'All');
		}
		else
		{
			$options = array();
		}
		$currency = DataObjectFactory::Factory('Currency');
		$options += $currency->getAll();
		$this->setOptions('currency_id',$options);
	}

	private function due_date($group = 'advanced') {
	// Search by Due Date
		$this->addSearchField(
			'due_date',
			'due_date_between',
			'between',
			'',
			$group
		);
	}

	private function ext_reference($group = 'advanced')
	{
	// Search by Customer Reference
		$this->addSearchField(
			'ext_reference',
			'customer reference',
			'equal',
			'',
			$group
		);
	}

	private function our_reference($group = 'advanced')
	{
	// Search by Our Reference
		$this->addSearchField(
			'our_reference',
			'our_reference',
			'equal',
			'',
			$group
		);

	}

	private function payment_date($group = 'advanced') {
	// Search by Due Date
		$this->addSearchField(
			'payment_date',
			'payment_date_between',
			'between',
			'',
			$group
		);
	}

	private function payment_type($group = 'advanced', $all = FALSE)
	{
	// Search by Currency
		$this->addSearchField(
			'payment_type_id',
			'payment_type',
			'select',
			'',
			$group
		);
		$paymenttype = DataObjectFactory::Factory('PaymentType');

		if ($all)
		{
			$options = array(''=>'All');
		}
		else
		{
			$options = array();
		}
		$cc = new ConstraintChain();
		$cc->add(new Constraint('method_id', 'is not', 'NULL'));

		$options += $paymenttype->getAll($cc);
		$this->setOptions('payment_type_id',$options);

	}

	private function status($group = 'advanced')
	{
	// Search by Status
		$this->addSearchField(
			'status',
			'status',
			'select',
			'',
			$group
			);
		$options=array(''=>'All'
					  ,'O'=>'Open'
					  ,'P'=>'Paid');
		$this->setOptions('status',$options);
	}

	private function supplier($group = 'advanced')
	{

	// Search by Supplier
		$this->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			0,
			$group
			);
		$supplier	= DataObjectFactory::Factory('PLSupplier');
		$options	= array('0'=>'All');
		$suppliers	= $supplier->getAll(null, false, true);
		$options	+=$suppliers;
		$this->setOptions('plmaster_id',$options);
	}

	private function transaction_date($group = 'advanced')
	{
	// Search by Transaction Date
		$this->addSearchField(
			'transaction_date',
			'transaction_date_between',
			'between',
			'',
			$group
		);
	}

	private function transaction_type($group = 'advanced')
	{
		$pltrans = DataObjectFactory::Factory('PLTransaction');

		// Search by Transaction Type
		$this->addSearchField(
			'transaction_type',
			'transaction_type',
			'select',
			'',
			$group
			);
		$options=array_merge(array(''=>'All')
						  	,$pltrans->getEnumOptions('transaction_type'));

		$this->setOptions('transaction_type',$options);
	}

}

// End of pltransactionsSearch

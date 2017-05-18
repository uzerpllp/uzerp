<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class cbtransactionsSearch extends BaseSearch {

	protected $version='$Revision: 1.8 $';

	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new cbtransactionsSearch($defaults);

		$cbtrans = DataObjectFactory::Factory('CBTransaction');

// Search by Account
		$search->addSearchField(
			'cb_account_id',
			'Account',
			'select',
			0,
			'advanced'
			);
		$cbaccount = DataObjectFactory::Factory('CBAccount');
		$options=array('0'=>'All');
		$cbaccounts=$cbaccount->getAll();
		$options+=$cbaccounts;
		$search->setOptions('cb_account_id',$options);

// Search by Customer
		$search->addSearchField(
			'company_id',
			'Company',
			'select',
			0,
			'advanced'
			);
		$company = DataObjectFactory::Factory('Company');
		$options=array('0'=>'All');
		$companies=$company->getAll();
		$options+=$companies;
		$search->setOptions('company_id',$options);

// Search by Person
		$search->addSearchField(
			'person',
			'person',
			'contains',
			'',
			'advanced'
		);

// Search by Source
		$search->addSearchField(
				'source',
				'Source',
				'select',
				'',
				'advanced'
				);

		$options=array(''=>'All');
		$options+=$cbtrans->getEnumOptions('source');
		$search->setOptions('source',$options);

// Search by Reference
		$search->addSearchField(
			'reference',
			'Reference',
			'equal',
			'',
			'advanced'
		);

// Search by Description
		$search->addSearchField(
			'description',
			'Description',
			'contains',
			'',
			'advanced'
		);

// Search by Transaction Date
		$search->addSearchField(
			'transaction_date',
			'Transaction Date between',
			'between',
			'',
			'advanced'
		);

// Search by status
		$search->addSearchField(
				'status',
				'Status',
				'select',
		         ''
				);

		$options=array(''=>'All'
					  ,'R'=>'Reconciled'
					  ,'N'=>'Unreconciled'
					  );
		$search->setOptions('status',$options);

// Search by Transaction Date
        $search->addSearchField(
            'statement_date',
            'Statement Date between',
            'between',
            '',
            'advanced'
        );

// Search by Type
		$search->addSearchField(
				'type',
				'Type',
				'multi_select',
				array(),
				'advanced'
				);
		$options=array(''=>'All');
		$options+=$cbtrans->getEnumOptions('type');
		$search->setOptions('type',$options);

		$search->setSearchData($search_data,$errors);
		return $search;
	}

}

// End of cbtransactionsSearch

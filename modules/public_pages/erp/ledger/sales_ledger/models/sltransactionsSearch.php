<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class sltransactionsSearch extends BaseSearch
{

	protected $version='$Revision: 1.9 $';
	protected $fields=array();
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null)
	{
		$search = new sltransactionsSearch($defaults);

		$sltrans = DataObjectFactory::Factory('SLTransaction');
		
// Search by Customer
		$search->addSearchField(
			'slmaster_id',
			'Customer',
			'select',
			0,
			'advanced'
			);
		$customer = DataObjectFactory::Factory('SLCustomer');
		$options=array('0'=>'All');
		$customers=$customer->getAll(null, false, true);
		$options+=$customers;
		$search->setOptions('slmaster_id',$options);
		
// Search by Person
		$search->addSearchField(
			'person',
			'person',
			'contains',
			'',
			'advanced'
		);

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
						  	,$sltrans->getEnumOptions('transaction_type'));
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
						  	,$sltrans->getEnumOptions('status'));
		$search->setOptions('status',$options);
		
		$search->setSearchData($search_data,$errors);
		
		return $search;
		
	}
		
}

// End of sltransactionsSearch

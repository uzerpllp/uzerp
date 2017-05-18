<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class PeriodicPaymentsSearch extends BaseSearch {

	protected $version='$Revision: 1.6 $';
	
	public static function useDefault(&$search_data=null, &$errors=array(), $defaults=null) {
		$search = new PeriodicPaymentsSearch($defaults);

		$search->basicFields($search);
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}

	public static function makePayments(&$search_data=null, &$errors=array(), $defaults=null) {
		$search = new PeriodicPaymentsSearch($defaults);
		
		$search->basicFields($search);

		$search_data['status']='A';
		
		$search->setSearchData($search_data,$errors,'makePayments');
		
		return $search;
			
	}

	protected function basicFields ($search) {
		
// Search by Source
		$search->addSearchField(
			'source',
			'source',
			'select',
			'',
			'basic'
			);
		$pp = new PeriodicPayment();
		$options=array(''=>'All');
		$sources=$pp->getEnumOptions('source');
		$options+=$sources;
		$search->setOptions('source',$options);

// Search by Frequency
		$search->addSearchField(
			'frequency',
			'frequency',
			'select',
			'',
			'basic'
			);
		$options=array(''=>'All');
		$frequencies=$pp->getEnumOptions('frequency');
		$options+=$frequencies;
		$search->setOptions('frequency',$options);

// Search by Bank Account
		$search->addSearchField(
				'cb_account_id',
				'Bank Account',
				'select',
				0,
				'advanced'
				);
		$cb = new CBAccount();
		$cbs=$cb->getAll();
		$options=array('0'=>'All');
		$options+=$cbs;
		$search->setOptions('cb_account_id',$options);
		
// Search by Status
		$search->addSearchField(
			'status',
			'status',
			'select',
			'',
			'advanced'
			);
		$options=array(''=>'All');
		$statuses=$pp->getEnumOptions('status');
		$options+=$statuses;
		$search->setOptions('status',$options);

// Search by Source
		$search->addSearchField(
			'company_id',
			'company',
			'select',
			0,
			'advanced'
			);
		$company = new Company();
		$options=array('0'=>'All');
		$companies=$company->getAll();
		$options+=$companies;
		$search->setOptions('company_id',$options);
			
// Search by Next Due Date
		$search->addSearchField(
			'next_due_date',
			'Due Between',
			'between',
			'',
			'advanced'
			);
	}
	
}
?>

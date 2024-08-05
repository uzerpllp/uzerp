<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class sttransactionsSearch extends BaseSearch
{

	protected $version='$Revision: 1.8 $';

	public static function useDefault(&$search_data=null, &$errors=array(), $defaults=null)
	{
		$search = new sttransactionsSearch($defaults);

// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'select',
			'',
			'basic'
		);
		$stitem = DataObjectFactory::Factory('STItem');
		$options = array('' => 'All');
		$stitems = $stitem->getAll();
		$options += $stitems;
		$search->setOptions('stitem_id', $options);

// Search by Status
		$search->addSearchField(
			'status',
			'Status',
			'select',
			'',
			'advanced'
		);
		$transaction = DataObjectFactory::Factory('STTransaction');
		$options = array('' => 'All');
		$statuses = $transaction->getEnumOptions('status');
		$options += $statuses;
		$search->setOptions('status', $options);

// Search by Location
		$search->addSearchField(
			'whlocation_id',
			'Location',
			'select',
			'',
			'advanced'
		);
		$whlocation = new WHLocationCollection;
		$options = array('' => 'All');
		$whlocations = $whlocation->getLocationList();
		$options += $whlocations;
		$search->setOptions('whlocation_id', $options);

// Search by Process
		$search->addSearchField(
			'process_name',
			'Process',
			'select',
			'',
			'advanced'
		);
		$options = array('' => 'All');
		$statuses = $transaction->getEnumOptions('process_name');
		$options += $statuses;
		$search->setOptions('process_name', $options);

// Search by Date
		$search->addSearchField(
			'created',
			'Date between',
			'between',
			'',
			'advanced'
		);

		$search->setSearchData($search_data,$errors);
		return $search;
	}
}

// End of sttransactionsSearch

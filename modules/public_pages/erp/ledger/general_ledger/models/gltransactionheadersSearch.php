<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class gltransactionheadersSearch extends BaseSearch
{

	protected $version='$Revision: 1.2 $';
	
	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new gltransactionheadersSearch($defaults);

		$trans = DataObjectFactory::Factory('GLTransactionHeader');
		
// Search by Transaction Date
		$search->addSearchField(
			'transaction_date',
			'Transaction Date between',
			'between',
			'',
			'advanced'
		);
		
// Search by Period
		if (!isset($search_data['clear']) && (isset($search_data['transaction_date']) || isset($defaults['search_id'])))
		{
			$default_period = array();
		}
		else
		{
			$currentPeriod = DataObjectFactory::Factory('GLPeriod');
			$currentPeriod->getCurrentPeriod();
			if ($currentPeriod)
			{
				$default_period = array($currentPeriod->id);
			}
			else
			{
				$default_period = array(0);
			}
		}
		$search->addSearchField(
				'glperiods_id',
				'Period',
				'multi_select',
				$default_period,
				'advanced'
				);
		$glperiod = DataObjectFactory::Factory('GLPeriod');
		$search->setOptions('glperiods_id', $glperiod->getAll());

// Search by Status
		$search->addSearchField(
				'status',
				'Status',
				'multi_select',
				array($trans->newStatus()),
				'advanced'
				);
		$search->setOptions('status', $trans->getEnumOptions('status'));

// Search by Type
		$search->addSearchField(
				'type',
				'Type',
				'multi_select',
				array(''),
				'advanced'
				);
		$search->setOptions('type', $trans->getEnumOptions('type'));

// Search by Doc Ref
		$search->addSearchField(
			'docref',
			'docref',
			'is',
			'',
			'advanced'
		);
		
// Search by Reference
		$search->addSearchField(
				'reference',
				'Reference',
				'contains',
				'',
				'advanced'
		);
		
// Search by Comment
		$search->addSearchField(
				'comment',
				'Comment',
				'contains',
				'',
				'advanced'
		);
		
// Search by Accrual
		$search->addSearchField(
				'accrual',
				'Accrual',
				'hide',
				'',
				'advanced'
		);
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}

// End of gltransactionheadersSearch

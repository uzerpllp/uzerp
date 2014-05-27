<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class gltransactionsSearch extends BaseSearch
{

	protected $version='$Revision: 1.13 $';
	
	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new gltransactionsSearch($defaults);

		$trans = DataObjectFactory::Factory('GLTransaction');
		
// Search by Account
		$search->addSearchField(
			'glaccount_id',
			'Account',
			'multi_select',
			array(0),
			'advanced'
			);
		$glaccount = DataObjectFactory::Factory('GLAccount');
		$search->setOptions('glaccount_id', $glaccount->getAll());

// Search by Centre
		$search->addSearchField(
			'glcentre_id',
			'Centre',
			'multi_select',
			array(0),
			'advanced'
			);
		$glcentre = DataObjectFactory::Factory('GLCentre');
		$search->setOptions('glcentre_id', $glcentre->getAll());

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

// Search by Source
		$search->addSearchField(
				'source',
				'Source',
				'multi_select',
				array(''),
				'advanced'
				);
		$search->setOptions('source', $trans->getEnumOptions('source'));

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
			'contains',
			'',
			'advanced'
		);
		
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}

// End of gltransactionsSearch

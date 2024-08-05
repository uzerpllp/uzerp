<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class whlocationsSearch extends BaseSearch
{

	protected $version='$Revision: 1.10 $';

	public static function useDefault(&$search_data=null, &$errors=array(), $defaults=null)
	{
		$search = new whlocationsSearch($defaults);

// Search by Store Id
		$search->addSearchField(
			'whstore_id',
			'Store',
			'select',
			'',
			'basic'
			);
		$store	= DataObjectFactory::Factory('WHStore');
		$stores	= $store->getAll();
		$search->setOptions('whstore_id', $stores);

		$search->setSearchData($search_data,$errors);
		return $search;
	}

	public static function withinLocation(&$search_data=null, &$errors=array(), $defaults=null)
	{
		$search = new whlocationsSearch($defaults);

// Set context
		$search->addSearchField(
			'whlocation_id',
			'',
			'hidden',
			'',
			'hidden'
			);

// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'select',
			'',
			'advanced'
			);
		$item	 = DataObjectFactory::Factory('STitem');
		$items	 = $item->getAll();
		$options = array(''=>'All');
		$search->setOptions('stitem_id', $options+$items);

// Search by Balance
		$search->addSearchField(
			'balance',
			'Show Zero Balances',
			'show',
			'',
			'advanced'
			);

		$search->setSearchData($search_data,$errors,'withinLocation');
		return $search;
	}

	public static function transactions(&$search_data=null, &$errors=array(), $defaults=null)
	{
		$search = new whlocationsSearch($defaults);

// Set context
		$search->addSearchField(
			'whlocation_id',
			'',
			'hidden',
			'',
			'hidden'
			);

// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'select',
			'',
			'advanced'
			);
		$item	 = DataObjectFactory::Factory('STitem');
		$items	 = $item->getAll();
		$options = array(''=>'All');
		$search->setOptions('stitem_id', $options+$items);

// Search by Date
		$search->addSearchField(
			'created',
			'Date between',
			'between',
			'',
			'advanced'
		);

		$search->setSearchData($search_data,$errors,'transactions');
		return $search;
	}

	public function toConstraintChain()
	{
		$cc = new ConstraintChain();

		if($this->cleared)
		{
			return $cc;
		}

		debug('BaseSearch::toConstraintChain Fields: '.print_r($this->fields, true));

		foreach($this->fields as $group)
		{
			foreach($group as $field=>$searchField)
			{
				if ($field=='balance')
				{
					$cc1 = new ConstraintChain();

					if ($searchField->getValue()=='')
					{
						$cc1->add(new Constraint('balance', '>', '0'));
					}

					$cc->add($cc1);
				}
				elseif ($field!='parent_id' && $field!='search_id')
				{
					$c = $searchField->toConstraint();

					if($c!==false)
					{
						$cc->add($c);
					}
				}
			}
		}
		debug('BaseSearch::toConstraintChain Constraints: '.print_r($cc, true));

		return $cc;
	}
}

// End of whlocationsSearch

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class whbinsSearch extends BaseSearch
{

	protected $version='$Revision: 1.9 $';
	
	public static function useDefault(&$search_data=null, &$errors=array(), $defaults=null)
	{
		$search = new whbinsSearch($defaults);

// Search by Location Id - hidden as this sets the context
		$search->addSearchField(
			'whlocation_id',
			'Location',
			'equal',
			'',
			'hidden'
			);
		$search->setSearchData($search_data,$errors);
			
// Search by Bin Id
		$search->addSearchField(
			'id',
			'Bin',
			'select',
			'',
			'basic'
			);
		$options = array(''=>'All');
		
		if (isset($search_data['whlocation_id']))
		{
			$bin = DataObjectFactory::Factory('WHBin');
			$cc=new ConstraintChain();
			$cc->add(new Constraint('whlocation_id', '=', $search_data['whlocation_id']));
			$bins = $bin->getAll($cc);
			$options+=$bins;
		}
		
		$search->setOptions('id', $options);
		
		$search->setSearchData($search_data,$errors);
		
		return $search;
	}

	public static function withinBin(&$search_data=null, &$errors=array(), $defaults=null)
	{
		$search = new whbinsSearch($defaults);

// Set context
		$search->addSearchField(
			'whbin_id',
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
		$item = DataObjectFactory::Factory('STitem');
		$items = $item->getAll();
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

		$search->setSearchData($search_data,$errors,'withinBin');
		return $search;
	}

	public static function transactions(&$search_data=null, &$errors=array(), $defaults=null)
	{
		$search = new whbinsSearch($defaults);

// Set context
		$search->addSearchField(
			'whbin_id',
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
		$item = DataObjectFactory::Factory('STitem');
		$items = $item->getAll();
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

// End of whbinsSearch

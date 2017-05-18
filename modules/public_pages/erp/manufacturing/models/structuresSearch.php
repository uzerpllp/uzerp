<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class structuresSearch extends BaseSearch {

	protected $version='$Revision: 1.7 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new structuresSearch($defaults);
// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'hidden',
			$search_data['stitem_id'],
			'hidden'
		);
// Search by Date
		$search->addSearchField(
			'start_date/end_date',
			'Date',
			'betweenfields',
			date(DATE_FORMAT),
			'basic'
		);
// Search by Stock Item Used
		$search->addSearchField(
			'ststructure_id',
			'Stock Item Used',
			'select',
			'',
			'advanced'
		);
		$stitem = DataObjectFactory::Factory('STItem');
		$chain = new ConstraintChain();
		$chain->add(new Constraint('comp_class','=','M'));
		if (isset($search_data['stitem_id'])) {
			$chain->add(new Constraint('id', '!=', $search_data['stitem_id']));
		}
		$stitems=$stitem->getAll($chain);
		$options=array(''=>'All');
		$options=$options+$stitems;
		$search->setOptions('ststructure_id',$options);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}

// End of structuresSearch

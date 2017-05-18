<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class costSheetSearch extends BaseSearch {

	protected $version='$Revision: 1.6 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new costSheetSearch($defaults);
		
// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'hidden',
			'',
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
// Search by Type
		$search->addSearchField(
			'type',
			'Type',
			'select',
			'Latest',
			'basic',
			false
		);
		$stcost = new STCost;
		$options = $stcost->getEnumOptions('type');
		$search->setOptions('type', $options);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}
?>
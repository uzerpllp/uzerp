<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class operationsSearch extends BaseSearch {

	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new operationsSearch($defaults);
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
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}
?>
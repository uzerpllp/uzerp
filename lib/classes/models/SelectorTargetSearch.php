<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SelectorTargetSearch extends BaseSearch {

	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {

		$search = new SelectorTargetSearch($defaults);
		
		$search->addSearchField(
			'description',
			'Name',
			'contains',
			'',
			'basic'
		);
		
		$search->setSearchData($search_data,$errors);
		
		return $search;
	}
		
}
?>
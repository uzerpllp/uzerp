<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class glcentresSearch extends BaseSearch {

	protected $version='$Revision: 1.4 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new glcentresSearch($defaults);
// Search by Cost Centre
		$search->addSearchField(
			'cost_centre',
			'Cost Centre',
			'is',
			'',
			'basic'
		);
// Search by Description
		$search->addSearchField(
			'description',
			'Description',
			'contains',
			'',
			'basic'
		);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}
?>
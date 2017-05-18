<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class datamappingsSearch extends BaseSearch {

	protected $version='$Revision: 1.3 $';
	
	public static function useDefault(&$search_data=null, &$errors=array(), $defaults=null) {
		$search = new datamappingsSearch($defaults);

// Search by name
		$search->addSearchField(
			'name',
			'Name contains',
			'contains',
			'',
			'basic'
			);

// Search by internal type
		$search->addSearchField(
			'internal_type',
			'Type contains',
			'contains',
			'',
			'basic'
			);

// Search by internal attribute
		$search->addSearchField(
			'internal_attribute',
			'Attribute contains',
			'contains',
			'',
			'basic'
			);


// Search by parent type
		$search->addSearchField(
			'parent_type',
			'parent Type contains',
			'contains',
			'',
			'advanced'
			);

// Search by parent attribute
		$search->addSearchField(
			'parent_attribute',
			'parent Attribute contains',
			'contains',
			'',
			'advanced'
			);

		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}
?>
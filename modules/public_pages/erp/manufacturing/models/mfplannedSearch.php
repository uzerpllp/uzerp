<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class mfplannedSearch extends BaseSearch {
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		
		$search = new mfplannedSearch($defaults);

// Search by Item Code
		$search->addSearchField(
			'item_code',
			'Item Code',
			'contains',
			'',
			'basic'
        );
		$search->setSearchData($search_data,$errors, 'useDefault');
        return $search;
    }
}
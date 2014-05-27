<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class whtransfersSearch extends BaseSearch {

	protected $version='$Revision: 1.4 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new whtransfersSearch($defaults);

// Search by Transfer Id
		$search->addSearchField(
			'transfer_number',
			'Transfer Number',
			'equal',
			'',
			'advanced'
			);

		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}
?>
<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CalendarSearch extends BaseSearch {

	protected $fields=array();
	
	public static function useDefault($search_data=null,&$errors=array()) {
		$search = new CalendarSearch();

		// search by calendar name
		$search->addSearchField(
			'name',
			'name',
			'contains',
			'',
			'basic'
		);
				
		// search by calendar owner
		$search->addSearchField(
			'owner',
			'owner',
			'hidden',
			'',
			'hidden'
		);	
		
		$search_data['owner']=EGS_USERNAME;
		$search->setSearchData($search_data,$errors);
		
		return $search;
	}
}
?>
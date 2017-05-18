<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class InjectorSearch extends BaseSearch {
	protected $fields=array();
		
	public static function useDefault($search_data=null, &$errors, $defaults=null) {
		$search = new InjectorSearch($defaults);
		$search->addSearchField(
			'name',
			'name',
			'contains',
			'',
			'basic'
			);
		// Search by Product
		$search->addSearchField(
			'class_name',
			'Class Name',
			'contains',
			'',
			'basic'
			);
			
		// Search by Retailer
		$search->addSearchField(
			'category',
			'Category',
			'select',
			'',
			'basic'
			);
		$injector = new InjectorClass();
		$options=array_merge(array(''=>'All')
							,$injector->getEnumOptions('category')
							);
		$search->setOptions('category',$options);
			
		$search->setSearchData($search_data,$errors);
		return $search;
	}

}
?>
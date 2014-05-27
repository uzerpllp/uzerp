<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SLDiscountSearch extends BaseSearch {

	protected $version='$Revision: 1.4 $';
	protected $fields=array();
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {

		$search = new SLDiscountSearch($defaults);

// Customer
		$search->addSearchField(
			'slmaster_id',
			'Customer',
			'select',
			0,
			'advanced'
			);
		$customer = new SLCustomer();
		$options=array('0'=>'All');
		$customers=$customer->getAll(null, false, true);
		$options+=$customers;
		$search->setOptions('slmaster_id',$options);
				
// Product Group
		$search->addSearchField(
			'prod_group_id',
			'Product Group',
			'select',
			'',
			'advanced'
		);
		$prodgroup=new STProductgroup();
		$prodgroup_list=$prodgroup->getAll();
		$options=array(''=>'All');
		$options+=$prodgroup_list;
		$search->setOptions('prod_group_id',$options);
		

// Execute Search
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}
?>
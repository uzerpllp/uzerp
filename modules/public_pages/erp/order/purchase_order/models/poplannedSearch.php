<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class poplannedSearch extends BaseSearch {
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		
		$search = new poplannedSearch($defaults);

		// Search by Supplier
		$search->addSearchField('plmaster_id', 'Supplier', 'select', 0, 'basic');
		$supplier = DataObjectFactory::Factory('PLSupplier');
		$options = array(
			'0' => 'All'
		);
		$suppliers = $supplier->getAll(null, false, true, '', '');
		$options += $suppliers;
		$search->setOptions('plmaster_id', $options);

		// Order date search
		$search->addSearchField('order_date', 'order_date_between', 'between', '', 'basic');

		// Search by Item Code
		$search->addSearchField(
			'item_code',
			'Item Code',
			'contains',
			'',
			'advanced'
        );
		$search->setSearchData($search_data,$errors, 'useDefault');
        return $search;
    }
}
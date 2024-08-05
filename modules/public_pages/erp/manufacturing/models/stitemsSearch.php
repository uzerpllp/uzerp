<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class stitemsSearch extends BaseSearch {

	protected $version='$Revision: 1.23 $';

	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {

		$search = new stitemsSearch($defaults);

// Search by Item Code
		$search->addSearchField(
			'item_code',
			'Item Code',
			'contains',
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

// Search by Obsolete Status
		$search->addSearchField(
			'show_items',
			'Show Items',
			'select',
			'notobsolete',
			'basic',
			false
		);

// Search by Alpha Code
		$search->addSearchField(
			'alpha_code',
			'Alpha Code',
			'is',
			'',
			'advanced'
		);

// Search by Product Group
		$search->addSearchField(
			'prod_group_id',
			'Product Group',
			'select',
			'',
			'advanced'
		);
		$options = array('' => 'All');
		$prod_group = DataObjectFactory::Factory('STProductgroup');
		$prod_groups = $prod_group->getAll();
		$options += $prod_groups;
		$search->setOptions('prod_group_id', $options);

// Search by Type Code
		$search->addSearchField(
			'type_code_id',
			'Type Code',
			'select',
			'',
			'advanced'
		);
		$options = array('' => 'All');
		$type_code = DataObjectFactory::Factory('STTypecode');
		$type_codes = $type_code->getAll();
		$options += $type_codes;
		$search->setOptions('type_code_id', $options);

// Search by Comp Class
		$search->addSearchField(
			'comp_class',
			'Comp Class',
			'select',
			'',
			'advanced'
		);

// Search by ABC Class
		$search->addSearchField(
			'abc_class',
			'ABC Class',
			'select',
			'',
			'advanced'
		);

// Search by Reference
		$search->addSearchField(
			'ref1',
			'Reference',
			'contains',
			'',
			'advanced'
		);
		$options = array('all' => 'All', 'obsolete' => 'Obsolete', 'notobsolete' => 'Not Obsolete');
		$search->setOptions('show_items', $options);
		$item = DataObjectFactory::Factory('STItem');
		$options = array('' => 'All');
		$comp_classes = $item->getEnumOptions('comp_class');
		$options += $comp_classes;
		$search->setOptions('comp_class', $options);
		$options = array('' => 'All');
		$abc_classes = $item->getEnumOptions('abc_class');
		$options += $abc_classes;
		$search->setOptions('abc_class', $options);

// Search by Date
		$date_type = 'afterornull';
		$default_date = date(DATE_FORMAT);
		$include=true;
		if (isset($search_data['show_items'])) {
			switch ($search_data['show_items']) {
				case 'obsolete':
					$date_type = 'before';
					// No break - we need to execute the code below too
				case 'notobsolete':
					if ((!isset($search_data['obsolete_date'])) ||
					   (!$search_data['obsolete_date']) || (! empty($search_data['clear']))) {
						$search_data['obsolete_date'] = $default_date;
					} else {
						$default_date = $search_data['obsolete_date'];
					}
					break;
				case 'all':
					// need to exclude from constraints to see all rows
					if (isset($search_data['obsolete_date'])) {
						unset($search_data['obsolete_date']);
					}
					$include=false;
					break;
			}
		}

		$search->addSearchField(
			'obsolete_date',
			'Date',
			$date_type,
			$default_date,
			'basic',
			$include
		);

		$search->setSearchData($search_data,$errors, 'useDefault');
		return $search;
	}

	public static function itemSearch(&$search_data=null, &$errors=array(), $defaults=null) {

		$search = new stitemsSearch($defaults);

// Search by Item Code
		$search->addSearchField(
			'stitem_id',
			'Item Code',
			'hidden',
			'',
			'hidden'
		);

// Search by Works Order
		$search->addSearchField(
			'wo_number',
			'wo_number',
			'is',
			'',
			'advanced'
		);

// Search by Status
		$search->addSearchField(
			'status',
			'Status',
			'multi_select',
			array('N','R','O'),
			'advanced'
		);
		$wo = DataObjectFactory::Factory('MFWorkorder');
		$options = $wo->getEnumOptions('status');
		$search->setOptions('status', $options);

		$search->setSearchData($search_data,$errors,'itemSearch');
		return $search;
	}

	public static function itemTransactions(&$search_data=null, &$errors=array(), $defaults=null) {

		$search = new stitemsSearch($defaults);

// Search by Item Code
		$search->addSearchField(
			'stitem_id',
			'Item Code',
			'hidden',
			'',
			'hidden'
		);

// Search by Date
		$date_type = 'between';
		$default_date = date(DATE_FORMAT);
		$search->addSearchField(
			'created',
			'Created between',
			$date_type,
			$default_date,
			'basic'
		);

// Search by To Location
		$search->addSearchField(
			'whlocation_id',
			'To Location',
			'select',
			'',
			'advanced'
		);
		$location = DataObjectFactory::Factory('WHLocation');
		$options = array('' => 'All');
		$locations = $location->getAll();
		$options += $locations;
		$search->setOptions('whlocation_id', $options);

// Search by Quantity
		$search->addSearchField(
			'qty',
			'qty greater than',
			'greater',
			0,
			'advanced'
		);

		$search->setSearchData($search_data,$errors,'itemTransactions');
		return $search;
	}

	public static function viewPOProducts(&$search_data=null, &$errors=array(), $defaults=null) {

		$search = new stitemsSearch($defaults);

// Search by Item Code
		$search->addSearchField(
			'stitem_id',
			'Item Code',
			'hidden',
			'',
			'hidden'
		);

// Search by Supplier
		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			'',
			'advanced'
		);
		$suppliers = DataObjectFactory::Factory('PLSupplier');
		$options = array('All');
		$options += $suppliers->getAll(null, false, true);
		$search->setOptions('plmaster_id', $options);

// Search by Start Date; default is to show rows current at today's date
		$search->addSearchField(
			'start_date/end_date',
			'current at',
			'betweenfields',
			date(DATE_FORMAT),
			'advanced'
			);

		$search->setSearchData($search_data,$errors,'viewPOProducts');
		return $search;
	}

	public static function viewPurchaseorders(&$search_data=null, &$errors=array(), $defaults=null) {

		$search = new stitemsSearch($defaults);

// Search by Item Code
		$search->addSearchField(
			'stitem_id',
			'Item Code',
			'hidden',
			'',
			'hidden'
		);

// Search by Works Order
		$search->addSearchField(
			'order_number',
			'Order Number',
			'is',
			'',
			'advanced'
		);

// Search by Status
		$search->addSearchField(
			'status',
			'Status',
			'multi_select',
			array('N', 'A', 'P'),
			'advanced'
		);	
		$so = DataObjectFactory::Factory('POrderLine');
		$options = $so->getEnumOptions('status');
		$search->setOptions('status', $options);

		$search->setSearchData($search_data,$errors,'viewPurchaseorders');
		return $search;
	}

	public static function viewPurchaseinvoices(&$search_data=null, &$errors=array(), $defaults=null) {

		$search = new stitemsSearch($defaults);

// Search by Item Code
		$search->addSearchField(
			'stitem_id',
			'Item Code',
			'hidden',
			'',
			'hidden'
		);

// Search by Customer
		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			'',
			'advanced'
		);
		$suppliers = DataObjectFactory::Factory('PLSupplier');
		$options = array('All');
		$options += $suppliers->getAll(null, false, true);
		$search->setOptions('plmaster_id', $options);

// Search by Invoice Date
		$search->addSearchField(
			'invoice_date',
			'invoice_date',
			'between',
			'',
			'advanced'
		);	

		$search->setSearchData($search_data,$errors,'viewPurchaseinvoices');
		return $search;
	}

	public static function viewSOProducts(&$search_data=null, &$errors=array(), $defaults=null) {

		$search = new stitemsSearch($defaults);

// Search by Item Code
		$search->addSearchField(
			'stitem_id',
			'Item Code',
			'hidden',
			'',
			'hidden'
		);

// Search by Customer
		$search->addSearchField(
			'slmaster_id',
			'Customer',
			'select',
			'',
			'basic'
		);
		$customers = DataObjectFactory::Factory('SLCustomer');
		$options = array('All');
		$options += $customers->getAll(null, false, true);
		$search->setOptions('slmaster_id', $options);

		$search->addSearchField(
			'so_price_type_id',
			'SO Price Type',
			'select',
			0,
			'basic'
			);
		$options=array('0'=>'All');
		$pricetype = DataObjectFactory::Factory('SOPriceType');
		$pricetypes=$pricetype->getAll();
		$options+=$pricetypes;
		$search->setOptions('so_price_type_id',$options);

// Search by Start Date; default is to show rows current at today's date
		$search->addSearchField(
			'start_date/end_date',
			'current at',
			'betweenfields',
			date(DATE_FORMAT),
			'advanced'
			);

		$search->setSearchData($search_data,$errors,'viewSOProducts');
		return $search;
	}

	public static function viewSalesorders(&$search_data=null, &$errors=array(), $defaults=null) {

		$search = new stitemsSearch($defaults);

// Search by Item Code
		$search->addSearchField(
			'stitem_id',
			'Item Code',
			'hidden',
			'',
			'hidden'
		);

// Search by Works Order
		$search->addSearchField(
			'order_number',
			'Order Number',
			'is',
			'',
			'advanced'
		);

// Search by Status
		$search->addSearchField(
			'status',
			'Status',
			'multi_select',
			array('N', 'R', 'S', 'P'),
			'advanced'
		);	
		$so = DataObjectFactory::Factory('SOrderLine');
		$options = $so->getEnumOptions('status');
		$search->setOptions('status', $options);

		$search->setSearchData($search_data,$errors,'viewSalesorders');
		return $search;
	}

	public static function viewSalesinvoices(&$search_data=null, &$errors=array(), $defaults=null) {

		$search = new stitemsSearch($defaults);

// Search by Item Code
		$search->addSearchField(
			'stitem_id',
			'Item Code',
			'hidden',
			'',
			'hidden'
		);

// Search by Customer
		$search->addSearchField(
			'slmaster_id',
			'Customer',
			'select',
			'',
			'advanced'
		);
		$customers = DataObjectFactory::Factory('SLCustomer');
		$options = array('All');
		$options += $customers->getAll(null, false, true);
		$search->setOptions('slmaster_id', $options);

// Search by Invoice Date
		$search->addSearchField(
			'invoice_date',
			'invoice_date',
			'between',
			'',
			'advanced'
		);	

		$search->setSearchData($search_data,$errors,'viewSalesinvoices');
		return $search;
	}


}

// End of stitemsSearch

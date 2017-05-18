<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class assetsSearch extends BaseSearch {

	protected $version='$Revision: 1.4 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		
		$search = new assetsSearch($defaults);

		$trans = DataObjectFactory::Factory('ARTransaction');

// Search by Code
		$search->addSearchField(
			'code',
			'code starts with',
			'begins',
			'',
			'basic'
		);

// Search by Description
		$search->addSearchField(
			'description',
			'description contains',
			'contains',
			'',
			'basic'
		);

// Search by Serial Number
		$search->addSearchField(
			'serial_no',
			'Serial No.',
			'contains',
			'',
			'advanced'
		);

// Search by Supplier
		$search->addSearchField(
				'plmaster_id',
				'Supplier',
				'select',
				0,
				'advanced'
				);
		$supplier = DataObjectFactory::Factory('PLSupplier');
		$options = array('0'=>'All');
		$suppliers = $supplier->getAll(null, false, true);
		$options += $suppliers;
		$search->setOptions('plmaster_id',$options);
		
// Search by Group
		$search->addSearchField(
			'argroup_id',
			'Group',
			'multi_select',
			array(0),
			'advanced'
			);
		$argroup = DataObjectFactory::Factory('ARGroup');
		$search->setOptions('argroup_id',$argroup->getAll());

// Search by Location
		$search->addSearchField(
			'arlocation_id',
			'Location',
			'multi_select',
			array(0),
			'advanced'
			);
		$arlocation = DataObjectFactory::Factory('ARLocation');
		$search->setOptions('arlocation_id',$arlocation->getAll());

// Search by Analysis
		$search->addSearchField(
				'aranalysis_id',
				'Analysis',
				'select',
				0,
				'advanced'
				);
		$ar_analysis = DataObjectFactory::Factory('ARAnalysis');
		$options=array('0'=>'All');
		$ar_analysiss = $ar_analysis->getAll();
		$options += $ar_analysiss;
		$search->setOptions('aranalysis_id',$options);
		
// Search by Purchase Date
		$search->addSearchField(
			'purchase_date',
			'Purchase Date between',
			'between',
			'',
			'advanced'
		);
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
	public static function transactions($search_data=null, &$errors=array(), $defaults=null) {
		
		$search = new assetsSearch($defaults);

		$trans = DataObjectFactory::Factory('ARTransaction');
		
// Search by Transaction Date
		$search->addSearchField(
			'transaction_date',
			'Transaction Date between',
			'between',
			'',
			'basic'
		);
		
// Search by Asset
		$search->addSearchField(
				'armaster_id',
				'Asset',
				'select',
				0,
				'basic'
				);
		$asset = DataObjectFactory::Factory('Asset');
		$options = array('0'=>'All');
		$assets  = $asset->getAll();
		$options+= $assets;
		$search->setOptions('armaster_id', $options);
		
// Search by Transaction Type
		$search->addSearchField(
				'transaction_type',
				'Transaction Type',
				'multi_select',
				array(''),
				'advanced'
				);
		$search->setOptions('transaction_type', $trans->getEnumOptions('transaction_type'));

// Search by Group
		$search->addSearchField(
			'to_group_id',
			'Group',
			'multi_select',
			array(0),
			'advanced'
			);
		$argroup = DataObjectFactory::Factory('ARGroup');
		$search->setOptions('to_group_id', $argroup->getAll());

// Search by Location
		$search->addSearchField(
			'to_location_id',
			'Location',
			'multi_select',
			array(0),
			'advanced'
			);
		$arlocation = DataObjectFactory::Factory('ARLocation');
		$search->setOptions('to_location_id', $arlocation->getAll());

		$search->setSearchData($search_data, $errors);
		return $search;
	}
		
}

// End of assetsSearch

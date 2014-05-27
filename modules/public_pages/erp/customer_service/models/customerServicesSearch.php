<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class customerServicesSearch extends BaseSearch
{

	protected $version='$Revision: 1.8 $';
	
	public static function useDefault($search_data=null,&$errors=array(),$customerservice)
	{
		
		$search = new customerServicesSearch();
// Search by Product Group
		$search->addSearchField(
			'product_group',
			'Product Group',
			'select',
			'All',
			'basic'
		);
		$options = array('' => 'All');
		$options += $customerservice->productGroupList();
		$search->setOptions('product_group', $options);

// Search by Customer
		$search->addSearchField(
			'slmaster_id',
			'Customers',
			'select',
			'All',
			'basic'
		);
		$options = array('' => 'All');
		$options += $customerservice->customerList();
		$search->setOptions('slmaster_id', $options);

// Search by Start Period
		$search->addSearchField(
			'start',
			'Start Period',
			'select',
			'All',
			'advanced'
		);
		$options = array('' => 'All');
		$options += $customerservice->periodList();
		$search->setOptions('start', $options);
			
// Search by End Period
		$search->addSearchField(
			'end',
			'End Period',
			'select',
			'All',
			'advanced'
		);
		$search->setOptions('end', $options);
			
		$search->setSearchData($search_data,$errors);
		
		return $search;
		
	}
		
	public static function failureCodes($search_data=null,&$errors=array(),$customerservice)
	{
		
		$search = new customerServicesSearch();
// Search by Failure Code
		$search->addSearchField(
			'cs_failurecode_id',
			'Failure Code',
			'select',
			'All',
			'basic'
		);
		
		$failurecodes=new CSFailureCode();
		$options=array(''=>'All');
		$options+=$failurecodes->getAll();
		$search->setOptions('cs_failurecode_id', $options);

// Search by Start Period
		$search->addSearchField(
			'start',
			'Start Period',
			'select',
			'All',
			'advanced'
		);
		$options = array('' => 'All');
		$options += $customerservice->periodList();
		$search->setOptions('start', $options);
			
// Search by End Period
		$search->addSearchField(
			'end',
			'End Period',
			'select',
			'All',
			'advanced'
		);
		$search->setOptions('end', $options);
			
		$search->setSearchData($search_data,$errors,'failureCodes');
		
		return $search;
		
	}

}

// End of customerServicesSearch

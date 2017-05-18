<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class glaccountsSearch extends BaseSearch {

	protected $version='$Revision: 1.5 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new glaccountsSearch($defaults);
// Search by Account
		$search->addSearchField(
			'account',
			'Account',
			'is',
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
// Search by Type
		$search->addSearchField(
			'actype',
			'Type',
			'select',
			'',
			'basic'
		);
// Search by Control Account
		$search->addSearchField(
			'control',
			'Control Accounts Only',
			'hide',
			'',
			'advanced'
		);
// Search by Analysis Code
		$search->addSearchField(
			'glanalysis_id',
			'Analysis Code',
			'select',
			'',
			'advanced'
		);
		$glaccount = new GLAccount;
		$types = $glaccount->getEnumOptions('actype');
		$options = array('' => 'All');
		$options += $types;
		$search->setOptions('actype', $options);
		$glanalysis = new GLAnalysis;
		$codes = $glanalysis->getAll();
		$options = array('' => 'All');
		$options += $codes;
		$search->setOptions('glanalysis_id', $options);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}
?>
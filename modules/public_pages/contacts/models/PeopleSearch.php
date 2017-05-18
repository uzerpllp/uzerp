<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PeopleSearch extends BaseSearch
{

	protected $version = '$Revision: 1.9 $';
	
	protected $fields = array();
		
	public static function useDefault($search_data=null, &$errors, $defaults=null)
	{
		
		$search = new PeopleSearch($defaults);
		
		// Search by First Name
		$search->addSearchField(
			'firstname',
			'firstname',
			'contains'
		);
		
		// Search by Surname
		$search->addSearchField(
			'surname',
			'surname',
			'begins'
		);
		
		// Search by Comapny name
		$search->addSearchField(
			'company',
			'company_name',
			'begins'
		);
		
		// Search by Assigned to Me
		$search->addSearchField(
			'assigned_to',
			'assigned_to_me',
			'hide',
			false,
			'advanced'
		);
		
		$search->setOnValue('assigned_to',EGS_USERNAME);
		
		// Search by Phone Number
		$search->addSearchField(
			'phone',
			'phone_number',
			'begins',
			'',
			'advanced'
		);

		// Search by Mobile Phone Number
		$search->addSearchField(
			'mobile',
			'mobile',
			'begins',
			'',
			'advanced'
		);

		// Search by Email Address
		$search->addSearchField(
			'email',
			'email',
			'contains',
			'',
			'advanced'
		);
		
		// Search by Town
		$search->addSearchField(
			'town',
			'town',
			'contains',
			'',
			'advanced'
		);
		
		// Search by Post Code
		$search->addSearchField(
			'postcode',
			'postcode',
			'contains',
			'',
			'advanced'
		);

		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}

// End of PeopleSearch

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class reportsSearch extends BaseSearch
{

	protected $version = '$Revision: 1.1 $';
	
	protected $fields = array();
	
	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new reportsSearch($defaults);

// Search by Description
		$search->addSearchField(
			'description',
			'Description',
			'contains',
			'',
			'advanced'
			);

// Search by Tablename
		$search->addSearchField(
			'tablename',
			'Tablename',
			'contains',
			'',
			'advanced'
		);

// Search by Owner
		$search->addSearchField(
			'owner',
			'Owner',
			'contains',
			'',
			'advanced'
		);

		$search->setSearchData($search_data, $errors);
		return $search;
	}

}

// End of reportsSearch

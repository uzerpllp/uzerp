<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class modulesSearch extends BaseSearch
{

	protected $version = '$Revision: 1.1 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null)
	{
		$search = new modulesSearch($defaults);
		
		// Search by Name
		$search->addSearchField(
			'name',
			'Name',
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
			'advanced'
		);
		
		// Search by Location
		$search->addSearchField(
			'location',
			'Location',
			'contains',
			'',
			'advanced'
		);
		
		// Search by Enabled
		$search->addSearchField(
			'registered',
			'Registered',
			'select',
			'',
			'advanced'
		);
		$search->setOptions('registered', array(''		=> 'All'
											   ,'TRUE'	=> 'True'
											   ,'FALSE'	=> 'False'));
		
		// Search by Enabled
		$search->addSearchField(
			'enabled',
			'enabled',
			'select',
			'',
			'advanced'
		);
		$search->setOptions('enabled', array(''			=> 'All'
											,'TRUE'		=> 'True'
											,'FALSE'	=> 'False'));
		
		$search->setSearchData($search_data,$errors);
		
		return $search;
	}
		
}

// End of modulesSearch

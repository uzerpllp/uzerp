<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class UzletSearch extends BaseSearch
{
	
	protected $version='$Revision: 1.1 $';
	
	protected $fields=array();
		
	public static function Uzlets($search_data=null, &$errors, $defaults=null)
	{
		$search = new UzletSearch($defaults);
		
		// Search by Name
		$search->addSearchField(
			'name',
			'Name',
			'contains',
			'',
			'basic'
		);
		
		// Search by Title
		$search->addSearchField(
			'title',
			'Title',
			'contains',
			'',
			'basic'
		);		
		
		// Search by preset
		$search->addSearchField(
			'preset',
			'preset',
			'select',
			'',
			'advanced'
		);
		$search->setOptions('preset', array(''		=> 'All'
										   ,'TRUE'	=> 'True'
										   ,'FALSE'	=> 'False'));
		
		// Search by preset
		$search->addSearchField(
			'enabled',
			'enabled',
			'select',
			'',
			'advanced'
		);
		$search->setOptions('enabled', array(''		 => 'All'
											,'TRUE'	 => 'True'
											,'FALSE' => 'False'));
		
		// Search by preset
		$search->addSearchField(
			'dashboard',
			'dashboard',
			'select',
			'',
			'advanced'
		);
		$search->setOptions('dashboard', array(''		=> 'All'
											  ,'TRUE'	=> 'True'
											  ,'FALSE'	=> 'False'));
		
		$search->setSearchData($search_data, $errors, 'uzlets');
		
		return $search;
	}
	
}

// End of AdminSearch

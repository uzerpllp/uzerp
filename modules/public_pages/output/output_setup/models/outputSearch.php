<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class outputSearch extends BaseSearch
{

	protected $version='$Revision: 1.1 $';
	
	protected $fields=array();
		
	public static function useDefault($search_data=null, &$errors, $defaults=null)
	{
		
		$search = new outputSearch($defaults);
		
		// Name
		$search->addSearchField(
			'name',
			'name_contains',
			'contains',
			'',
			'basic'
		);
		
		// Report Type
		$search->addSearchField(
			'report_type_id',
			'report_type',
			'select',
			'',
			'basic'
		);
		$reporttype = DataObjectFactory::Factory('ReportType');
		$reporttypes = $reporttype->getAll(null, TRUE, TRUE);
		$options=array(''=>'all');
		$options += $reporttypes;
		$search->setOptions('report_type_id',$options);
		
		// User Defined
		$search->addSearchField(
			'user_defined',
			'user_defined',
			'select',
			'',
			'advanced'
		);
		$search->setOptions('user_defined', array(''=>'All'
												 ,'true'=>'True'
												 ,'false'=>'False'));
		
		$search->setSearchData($search_data,$errors);
		
		return $search;
	
	}
		
	
}

// End of outputSearch

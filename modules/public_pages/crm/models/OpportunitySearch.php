<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class OpportunitySearch extends BaseSearch {

	protected $version='$Revision: 1.5 $';
	
	protected $fields=array();
		
	public static function useDefault($search_data=null, &$errors, $defaults=null) {
		
		$search = new OpportunitySearch($defaults);
		
		// Search by Open Only
		$search->addSearchField(
			'open',
			'open_only',
			'hide',
			'checked'
		);
		
		// Search by Name
		$search->addSearchField(
			'name',
			'name_contains',
			'contains'
		);
		
		// Search by Company
		$search->addSearchField(
			'company',
			'company_name_begins_with',
			'begins'
		);
		
		// Search by Company
		$search->addSearchField(
			'person',
			'person_name_contains',
			'contains'
		);
		
		// Search by Assigned to Me
		$search->addSearchField(
			'assigned',
			'assigned_to_me',
			'hide',
			false
		);
		$search->setOnValue('assigned',EGS_USERNAME);
		
		// Search By Source
		$search->addSearchField(
			'source_id',
			'source',
			'select',
			'',
			'advanced'
		);
		$model=new OpportunitySource();
		$options=array(''=>'All');
		$options+=$model->getAll();
		$search->setOptions('source_id',$options);
		
		// Search By Status
		$search->addSearchField(
			'status_id',
			'status',
			'select',
			'',
			'advanced'
		);
		$model=new OpportunityStatus();
		$options=array(''=>'All');
		$options+=$model->getAll();
		$search->setOptions('status_id',$options);
		
		// Search By Type
		$search->addSearchField(
			'type_id',
			'type',
			'select',
			'',
			'advanced'
		);
		$model=new OpportunityType();
		$options=array(''=>'All');
		$options+=$model->getAll();
		$search->setOptions('type_id',$options);
		
		$search->addSearchField(
			'cost',
			'cost_equal_or_greater_than',
			'greater_or_equal',
			0,
			'advanced'
		);
		
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}
?>
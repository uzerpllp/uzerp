<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CampaignSearch extends BaseSearch {

	protected $version='$Revision: 1.1 $';
	
	protected $fields=array();
		
	public static function useDefault($search_data=null, &$errors, $defaults=null) {
		
		$search = new CampaignSearch($defaults);
		
		// Search by Open Only
		$search->addSearchField(
			'active',
			'active_only',
			'hide',
			'checked'
		);
		
		// Search by Name
		$search->addSearchField(
			'name',
			'name_contains',
			'contains'
		);
		
		// Search By Campaign Status
		$search->addSearchField(
			'campaign_status_id',
			'status',
			'select',
			'',
			'advanced'
		);
		$model=new CampaignStatus();
		$options=array(''=>'All');
		$options+=$model->getAll();
		$search->setOptions('campaign_status_id',$options);
		
		// Search By Type
		$search->addSearchField(
			'campaign_type_id',
			'type',
			'select',
			'',
			'advanced'
		);
		$model=new CampaignType();
		$options=array(''=>'All');
		$options+=$model->getAll();
		$search->setOptions('campaign_type_id',$options);
		
		// Search By Start Date
		$search->addSearchField(
			'startdate/enddate',
			'current at',
			'betweenfields',
			'',
			'advanced'
		);
		
		// Search By Actual Cost
		$search->addSearchField(
			'actual_cost',
			'actual_cost_equal_or_greater',
			'greater_or_equal',
			0,
			'advanced'
		);
		
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}
?>
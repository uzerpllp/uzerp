<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectSearch extends BaseSearch {

	protected $version='$Revision: 1.7 $';
	
	protected $fields=array();
		
	public static function useDefault($search_data=null, &$errors, $defaults=null) {
		$search = new ProjectSearch($defaults);
		
		$search->addSearchField(
			'job_no',
			'job_no',
			'equal'
		);

		$search->addSearchField(
			'status',
			'status',
			'multi_select'
		);
		
		$project = new Project;
		$options = array('' => 'All');
		$statuses = $project->getEnumOptions('status');
		$options += $statuses;
		$search->setOptions('status', $options);
		
		$search->addSearchField(
			'name',
			'name_contains',
			'contains'
		);
		
		$search->addSearchField(
			'company',
			'company_name',
			'begins',
			null
		);

		$search->addSearchField(
			'category_id',
			'category',
			'select',
			'',
			'advanced'
		);
		$cat = new ProjectCategory();
		$cats = $cat->getAll();
		$options=array(''=>'all');
		$options += $cats;
		$search->setOptions('category_id',$options);

		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
	public static function issues($search_data, &$errors, $defaults=null) {
		$search = new ProjectSearch($defaults);
		$search->addSearchField(
			'problem_description',
			'description_contains',
			'contains'
		);
		$search->addSearchField(
			'closed',
			'show_closed',
			'show'
		);
		$search->addSearchField(
			'project',
			'project_name',
			'begins'
		);
		$search->addSearchField(
			'assigned_to',
			'assigned_to_me',
			'hide',
			false
		);
		$search->setOnValue('assigned_to',EGS_USERNAME);
		$search->setSearchData($search_data,$errors,'issues');
		return $search;
	}
	
}
?>
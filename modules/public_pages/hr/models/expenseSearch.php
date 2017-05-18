<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class expenseSearch extends BaseSearch
{

	protected $version='$Revision: 1.4 $';
	
	protected $fields=array();
		
	public static function useDefault($search_data=null, &$errors, $defaults=null)
	{
		
		$search = new expenseSearch($defaults);
		
		// Employee Name
		$search->addSearchField(
			'employee',
			'name_contains',
			'contains',
			'',
			'basic'
		);
		
		// Status
		$search->addSearchField(
			'status',
			'status',
			'multi_select',
			array('A', 'O', 'W'),
			'basic'
		);
		$expense = DataObjectFactory::Factory('Expense');
		$options=array(''=>'all');
		$options += $expense->getEnumOptions('status');
		$search->setOptions('status',$options);
		
		// Reference
		$search->addSearchField(
			'our_reference',
			'Reference',
			'contains',
			'',
			'advanced'
		);
		
		// Description
		$search->addSearchField(
			'description',
			'Description',
			'contains',
			'',
			'advanced'
		);
		
		// Project
		$search->addSearchField(
			'project_id',
			'Project',
			'select',
			'',
			'advanced'
		);
		$project = DataObjectFactory::Factory('Project');
		$projects = $project->getAll(null, TRUE, TRUE);
		$options=array(''=>'all');
		$options += $projects;
		$search->setOptions('project_id',$options);
		
		// Task
		$search->addSearchField(
			'task_id',
			'Task',
			'select',
			'',
			'advanced'
		);
		$task = DataObjectFactory::Factory('Task');
		$tasks = $task->getAll(null, TRUE, TRUE);
		$options=array(''=>'all');
		$options += $tasks;
		$search->setOptions('task_id',$options);
		
		$search->setSearchData($search_data,$errors);
		
		return $search;
	
	}
		
	public static function myExpenses($search_data=null, &$errors, $defaults=null)
	{
		$search = self::useDefault($search_data, $errors, $defaults);
		
		$search->removeSearchField('employee');
		
		return $search;
	}
	
}

// End of expenseSearch

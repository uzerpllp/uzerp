<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HoursSearch extends BaseSearch
{

	protected $version='$Revision: 1.4 $';

	protected $fields=array();

	public static function useDefault($search_data = null, &$errors = [], $defaults = null, $params = []) {

		$search = new HoursSearch($defaults);

		$search->addSearchField(
			'start_time',
			'start_date',
			'between'
		);

		$search->addSearchField(
			'person_id',
			'name',
			'select',
			'',
			'advanced'
		);
		$user = DataObjectFactory::Factory('person');
		$users = $user->getAll();
		$options=array(''=>'all');
		$options += $users;
		$search->setOptions('person_id',$options);

		foreach ($params as $option)
		{
			$option='add'.$option;
			$search->$option($search);
		}

		$default_fields=array(
							'opportunity'	=> 'opportunity_id',
							'project'		=> 'project_id',
							'task'			=> 'task_id',
							'ticket'		=> 'ticket_id'
		);
		foreach ($default_fields as $option=>$field)
		{
			foreach($search->fields as $group)
			{
				if(!isset($group[$field]) && isset($search_data[$field]))
				{
					$search->addSearchField(
						$field,
						$option,
						'hidden',
						'',
						'hidden'
					);
				}
			}
		}

		$search->setSearchData($search_data,$errors);

		return $search;

	}

	public static function person($search_data = null, &$errors = [], $defaults = null, $params = []) {

		$search = new HoursSearch($defaults);

		$search->addSearchField(
			'start_time',
			'start_date',
			'between'
		);

		$search->addSearchField(
			'person_id',
			'name',
			'hidden'
		);

		foreach ($params as $option)
		{
			$option='add'.$option;
			$search->$option($search);
		}

		$default_fields=array(
							'opportunity'	=> 'opportunity_id',
							'project'		=> 'project_id',
							'task'			=> 'task_id',
							'ticket'		=> 'ticket_id'
		);
		foreach ($default_fields as $option=>$field)
		{
			foreach($search->fields as $group)
			{
				if(!isset($group[$field]) && isset($search_data[$field]))
				{
					$search->addSearchField(
						$field,
						$option,
						'hidden',
						'',
						'hidden'
					);
				}
			}
		}

		$search->setSearchData($search_data,$errors);

		return $search;

	}

	public static function useMySearch($search_data = null, &$errors = [], $defaults = null, $params = [])
	{

		$search = new HoursSearch($defaults);

		$search->addSearchField(
			'start_time',
			'start_date',
			'between'
		);

		$user=getCurrentUser();

		$search->addSearchField(
			'person_id',
			'name',
			'hidden',
			$user->person_id,
			'hidden'
		);

		foreach ($params as $option)
		{
			$option='add'.$option;
			$search->$option($search);
		}

		$search->setSearchData($search_data,$errors);

		return $search;

	}

	private function addOpportunity($search)
	{

		$search->addSearchField(
			'opportunity_id',
			'opportunity',
			'select',
			'',
			'advanced'
		);
		$opportunity = DataObjectFactory::Factory('Opportunity');
		$opportunitys = $opportunity->getAll();
		$options=array(''=>'all');
		$options += $opportunitys;
		$search->setOptions('opportunity_id',$options);

	}

	private function addProject($search)
	{

		$search->addSearchField(
			'project_id',
			'project',
			'select',
			'',
			'advanced'
		);
		$project = DataObjectFactory::Factory('Project');
		$projects = $project->getAll();
		$options=array(''=>'all');
		$options += $projects;
		$search->setOptions('project_id',$options);

	}

	private function addTask($search)
	{

		$search->addSearchField(
			'task_id',
			'task',
			'select',
			'',
			'advanced'
		);
		$task = DataObjectFactory::Factory('Task');
		$tasks = $task->getAll();
		$options=array(''=>'all');
		$options += $tasks;
		$search->setOptions('task_id',$options);

	}

	private function addTicket($search)
	{

		$search->addSearchField(
			'ticket_id',
			'ticket',
			'select',
			'',
			'advanced'
		);
		$ticket = DataObjectFactory::Factory('Ticket');
		$tickets = $ticket->getAll();
		$options=array(''=>'all');
		$options += $tickets;
		$search->setOptions('ticket_id',$options);

	}

}

// End of HoursSearch

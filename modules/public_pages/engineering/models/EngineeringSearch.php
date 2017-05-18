<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EngineeringSearch extends BaseSearch
{

	protected $version='$Revision: 1.4 $';
	
	protected $fields=array();
		
	public static function workNotes($search_data=null, &$errors, $defaults=null)
	{
		
		$search = new EngineeringSearch($defaults);
		
		// Search by Title
		$search->addSearchField(
			'title',
			'title',
			'contains'
			);
		
		// Search by Note
		$search->addSearchField(
			'note',
			'note',
			'contains',
			'',
			'advanced'
			);
		
		// Search by Work Schedule
		$search->addSearchField(
			'work_schedule_id',
			'work_schedule',
			'select',
			'',
			'advanced'
		);
		$work_schedule = DataObjectFactory::Factory('WorkSchedule');
		$work_schedules = $work_schedule->getAll();
		$options=array(''=>'all');
		$options += $work_schedules;
		$search->setOptions('centre_id', $options);

		$search->setSearchData($search_data, $errors, 'workNotes');
		return $search;
	}
	
	public static function workSchedules($search_data=null, &$errors, $defaults=null)
	{
		
		$search = new EngineeringSearch($defaults);
		
		// Search on Job Number
		$search->addSearchField(
			'job_no',
			'job_number',
			'equal'
		);
		
		// Search on Job Number
		$search->addSearchField(
			'description',
			'description',
			'contains'
		);
		
		// Search on Status
		$search->addSearchField(
			'status',
			'status',
			'multi_select',
			array('A')
			);
		$project = DataObjectFactory::Factory('WorkSchedule');
		$options = array('' => 'All');
		$statuses = $project->getEnumOptions('status');
		$options += $statuses;
		$search->setOptions('status', $options);
		
		// Search on Work Centre
		$search->addSearchField(
			'centre_id',
			'centre',
			'select',
			'',
			'advanced'
		);
		$centre = DataObjectFactory::Factory('MFCentre');
		$centres = $centre->getAll();
		$options=array(''=>'all');
		$options += $centres;
		$search->setOptions('centre_id',$options);

		// Search on Downtime Code
		$search->addSearchField(
			'mf_downtime_code_id',
			'Downtime Code',
			'select',
			'',
			'advanced'
		);
		$downtimecode = DataObjectFactory::Factory('MFDowntimeCode');
		$downtimecodes = $downtimecode->getAll();
		$options=array(''=>'all');
		$options += $downtimecodes;
		$search->setOptions('mf_downtime_code_id',$options);

		$search->setSearchData($search_data, $errors, workSchedules);
		return $search;
	}
	
}

// End of EngineeringSearch

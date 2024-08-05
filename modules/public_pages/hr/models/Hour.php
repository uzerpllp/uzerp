<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Hour extends DataObject
{

	protected $version = '$Revision: 1.7 $';

	protected $defaultDisplayFields = array('person'
										   ,'start_time'
										   ,'duration'
										   ,'description'
										   ,'project'
										   ,'task'
										   ,'ticket'
										   ,'opportunity'
										   ,'billable'
										   );

	function __construct($tablename = 'hours')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->orderby='start_time';
		$this->orderdir='DESC';

// Define relationships
		$cc=new ConstraintChain();
		$cc->add(new Constraint('company_id', '=', EGS_COMPANY_ID));
 		$this->belongsTo('Person', 'person_id', 'person',  $cc, "surname || ', ' || firstname");
		$this->belongsTo('Opportunity', 'opportunity_id', 'opportunity'); 
		$this->belongsTo('Project', 'project_id', 'project');
 		$this->belongsTo('Task', 'task_id', 'task');
 		$this->belongsTo('Ticket', 'ticket_id', 'ticket');
		$this->belongsTo('HourType','type_id','type');

// Define field formats

// Define field defaults
		//TODO: base these on a setting
		$this->getField('start_time')->setDefault(mktime(SystemCompanySettings::DAY_START_HOURS,SystemCompanySettings::DAY_START_MINUTES));
		$this->getField('duration')->setDefault(array(SystemCompanySettings::DAY_LENGTH,'hours'));

// Define validation
		$this->getField('duration')->addValidator(new IntervalValidator());
		$this->getField('duration')->blockValidator('NumericValidator');

		$times = array('start_time','end_time');

		foreach($times as $time)
		{
			$this->getField($time)->addValidator(new DateValidator);
		}

// Define enumerated types

	}

	public static function getForTimesheet(ConstraintChain $hours_cc=null)
	{
		$db=DB::Instance();

		$query = 'select to_char(h.start_time, \'YYYY-MM-DD\') AS day, ht.name AS type, p.name AS project, t.name AS task, h.description, h.billable, h.duration
			FROM hours h LEFT JOIN projects p ON (h.project_id=p.id)
			LEFT JOIN tasks t ON (h.task_id=t.id)
			LEFT JOIN hour_types ht ON (ht.id=h.type_id)';

		$where = $hours_cc->__toString('h');

		if(!empty($where))
		{
			$query.=' WHERE '.$where;
		}

		$query.=' ORDER BY h.start_time';

		$hours = $db->GetArray($query);

		return $hours;
	}

}

// End of Hour

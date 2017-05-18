<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Holidayentitlement extends DataObject
{

	protected $version = '$Revision: 1.9 $';
	
	protected $defaultDisplayFields = array('num_days'=>'Number of Days'
										   ,'start_date'=>'Start Date'
										   ,'end_date'=>'End Date'
										   ,'statutory_days'=>'Statutory Days'
										   ,'lastupdated'=>'Last Updated');

	function __construct($tablename = 'holiday_entitlements')
	{
		parent::__construct($tablename);
		
		$this->idField = 'id';
		
		$this->hasMany('HolidayExtraday','extra_days');
		
		$this->orderby = 'lastupdated';
		
 		$this->belongsTo('Employee', 'employee_id', 'employee'); 

	}

	function loadEntitlement ($employee_id, $date)
	{
		$db = DB::Instance();
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('employee_id', '=', $employee_id));
		
		$cc->add(new Constraint($db->qstr($date), 'between', 'start_date and end_date'));
		
		$this->loadBy($cc);
	}
	
	/**
	 * Check any overlap Holiday Entitlement.
	 * 
	 * @param	string	the start date of the holiday entitlement.
	 * @param	string	the end date of the holiday entitlement.
	 * @param	string	the employee id
	 * @return  true/false.
 	 *
	 */
	public function overlap_entitlement($data)
	{
		
		$db = DB::Instance();
		
		$start_date	= $db->qstr(fix_date($data['start_date']));
		$end_date	= $db->qstr(fix_date($data['end_date']));

		$overlaps = DataObjectFactory::Factory('Holidayentitlement');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('employee_id', '=', $data['employee_id']));
		
		if (!empty($data[$overlaps->idField]))
		{
			$cc->add(new Constraint($overlaps->idField, '!=', $data[$overlaps->idField]));
		}
		
		$cc1 = new ConstraintChain();
		
		$cc1->add(new Constraint('start_date', 'between', $start_date . ' and ' . $end_date));
		$cc1->add(new Constraint('end_date', 'between', $start_date . ' and ' . $end_date),'OR');
		
		$cc->add($cc1);
		
		$overlapp_count = $overlaps->getCount($cc);
		
		if ($overlapp_count > 0)
		{
			return true;
		}
		
		return false;
	}

	/**
	 * Check the next holiday period start date.
	 * 
	 * @param	string	the employee id
	 * @return	int 	the next holiday period start date
 	 *
	 */
	public function getNextStartDate($_employee_id = '', &$errors = array())
	{
		
		$employee = DataObjectFactory::Factory('Employee');
		
		if (empty($_employee_id) && $this->isLoaded())
		{
			$_employee_id = $this->employee_id;
		}
		
		$employee->load($_employee_id);
		
		if (!$employee->isLoaded())
		{
			$errors[] = 'Cannot find employee details';
			return '';
		}
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('employee_id', '=', $_employee_id));
		
		$latest_end_date = $this->getMax('end_date', $cc);
		
		if (!empty($latest_end_date))
		{
			return fix_date(date(DATE_FORMAT, strtotime($latest_end_date.' +1 day')));
		}
		
		return $employee->start_date;
		
	}	

	/**
	 * Check the number of days left for the holiday period.
	 * 
	 * @param	string	the date within the requested holiday period.
	 * @param	string	the employee id
	 * @return	int 	the total number of requested days
 	 *
	 */
	public function get_total_days_left($date, $employee_id)
	{

		if (!$this->isLoaded())
		{
			$this->loadEntitlement($employee_id, $date);
		}
		
		if (!$this->isLoaded())
		{
			return 0;
		}
		
		$days_left = $this->num_days;

		$extra_days = DataObjectFactory::Factory('Holidayextraday');
		$days_left += $extra_days->extraDays($this->id);
		
		$holidays = DataObjectFactory::Factory('Holidayrequest');
		$days_left -= $holidays->authorisedDays($employee_id, $this->start_date, $this->end_date);
		
		return $days_left;
		
	}	

	public function get_totals($date, $employee_id)
	{

		if (!$this->isLoaded())
		{
			$this->loadEntitlement($employee_id, $date);
		}
		
		if (!$this->isLoaded())
		{
			return 0;
		}
		
		$extra_days	= DataObjectFactory::Factory('Holidayextraday');
		
		$totals = array('entitlement'	=> $this->num_days
					   ,'extra_days'	=> $extra_days->extraDays($this->id));
		
		$holidayrequest = DataObjectFactory::Factory('Holidayrequest');
		$holidays	= new HolidayrequestCollection;
		
		$outstanding = $totals['entitlement'] + $totals['extra_days'];
		
		$holidays->sumByStatus($holidayrequest->setConstraints($employee_id, $this->start_date, $this->end_date));
		
		if (!empty($holidays))
		{
			foreach ($holidays as $holidayrequest)
			{
				$totals[$holidayrequest->getFormatted('status')] = $holidayrequest->num_days;
				if ($holidayrequest->awaitingAuthorisation() || $holidayrequest->authorised())
				{
					$outstanding -= $holidayrequest->num_days;
				}
			}
		}
		
		$totals['days_left'] = $outstanding;
		
		return $totals;
		
	}	

}

// End of Holidayentitlement

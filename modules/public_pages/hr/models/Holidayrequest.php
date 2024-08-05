<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Holidayrequest extends DataObject
{

	protected $version = '$Revision: 1.10 $';

	protected $defaultDisplayFields = array('employee'				=>'Employee'
										   ,'start_date'			=>'Start Date'
										   ,'end_date'				=>'End Date'
										   ,'num_days'				=>'Number of Days'
										   ,'special_circumstances'	=>'Special Circumstances'
										   ,'status'				=>'Status');	

	protected $status_types = array('authorised'			=> 'A'
								   ,'cancelled'				=> 'C'
								   ,'declined'				=> 'D'
								   ,'waitingAuthorisation'	=> 'W');

	function __construct($tablename = 'holiday_requests')
	{

		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField = 'id';
		$this->orderby = 'created';

		// Define relationships
		$this->belongsTo('Employee', 'employee_id', 'employee');
 		$this->hasOne('Employee', 'approved_by', 'approved_by');

		// Define field formats

		// set formatters, more set in load() function

		// Define enumerated types
		// Enums really should be associated directly with the field to which they relate!
		$this->setEnum('status'
					  ,array($this->status_types['authorised']				=> 'Authorised'
							,$this->status_types['cancelled']				=> 'Cancelled'
							,$this->status_types['declined']				=> 'Declined'
							,$this->status_types['waitingAuthorisation']	=> 'Waiting Authorisation'
							)
					  );

		// Define default values
		$this->getField('status')->setDefault($this->status_types['waitingAuthorisation']);	

		// Define field formatting

		// Define link rules for related items
	}

	function authorisedDays ($employee_id, $start_date, $end_date)
	{
		$cc = $this->setConstraints($employee_id, $start_date, $end_date);

		$cc->add(new Constraint('status', 'in', "('".$this->status_types['authorised']."', '".$this->status_types['waitingAuthorisation']."')"));

		return $this->getSum('num_days', $cc);
	}

	function setConstraints ($_employee_id = '', $_start_date = '', $_end_date = '')
	{
		$cc=new ConstraintChain();

		if (!empty($_employee_id))
		{
			$cc->add(new Constraint('employee_id', '=', $_employee_id));
		}

		if (!empty($_start_date))
		{
			$cc->add(new Constraint('start_date', '>=', $_start_date));
		}

		if (!empty($_end_date))
		{
			$cc->add(new Constraint('end_date', '<=', $_end_date));
		}

		return $cc;

	}

	function awaitingAuthorisation ()
	{
		return ($this->status == $this->status_types['waitingAuthorisation']);
	}

	function authorised ()
	{
		return ($this->status == $this->status_types['authorised']);
	}

	function cancelled ()
	{
		return ($this->status == $this->status_types['cancelled']);
	}

	function declined ()
	{
		return ($this->status == $this->status_types['declined']);
	}

	function authorise ()
	{
		return $this->status_types['authorised'];
	}

	function cancel ()
	{
		return $this->status_types['cancelled'];
	}

	function decline ()
	{
		return $this->status_types['declined'];
	}

	function newRequest ()
	{
		return $this->status_types['waitingAuthorisation'];
	}

	static function Factory ($data, &$errors = array(), $do_name = null)
	{

		if (is_string($do_name))
		{
			$do_name = DataObjectFactory::Factory($do_name);
		}

		if (get_class($do_name) == __CLASS__)
		{
			$employee = DataObjectFactory::Factory('employee');

			$do_name->belongsTo('Employee', 'employee_id', 'employee', $employee->authorisationPolicy($employee->holiday_model()));
		}

		return parent::Factory($data, $errors, $do_name);
	}

}

// End of Holidayrequest

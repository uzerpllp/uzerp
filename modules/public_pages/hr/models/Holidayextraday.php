<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Holidayextraday extends DataObject
{

	protected $version='$Revision: 1.6 $';

	protected $defaultDisplayFields = array('num_days'=>'Number of Days'
										   ,'reason'=>'Reason'
										   ,'authorisedby'=>'Authorised By'
										   ,'authorised_on'=>'Authorised On');

	function __construct($tablename = 'holiday_extra_days')
	{
		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField='id';

		// Define relationships
 		$this->belongsTo('Entitlement', 'entitlement_period_id', 'entitlement');
 		$this->belongsTo('Employee', 'employee_id', 'employee');
 		$this->belongsTo('User', 'authorisedby', 'authorisedby_user'); 

 		// Define field formats

		// set formatters, more set in load() function

		// Define enumerated types

		// Define default values
		//$this->_autohandlers['authorised_on'] = new CurrentTimeHandler();
 		$this->_autohandlers['authorisedby'] = new CurrentUserHandler(false,'EGS_USERNAME');

		// Define field formatting

		// Define link rules for related items
	}

	function extraDays ($entitlement_id)
	{
		$cc=new ConstraintChain();

		$cc->add(new Constraint('entitlement_period_id', '=', $entitlement_id));

		return $this->getSum('num_days', $cc);
	}

}

// End of Holidayextraday

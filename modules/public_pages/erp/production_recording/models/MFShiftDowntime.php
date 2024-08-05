<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFShiftDowntime extends DataObject
{

	protected $version = '$Revision: 1.4 $';
	
	protected $defaultDisplayFields = array('shift'
											,'downtime_code'
											,'down_time'
											,'time_period'
											);
	
	function __construct($tablename = 'mf_shift_downtime')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField = 'id';

// Define relationships
 		$this->belongsTo('MFShift', 'mf_shift_id', 'shift');
 		$this->belongsTo('MFCentreDowntimeCode', 'mf_centre_downtime_code_id', 'downtime_code');
 		$this->hasOne('MFShift', 'mf_shift_id', 'shift_detail');
 		$this->hasOne('MFCentreDowntimeCode', 'mf_centre_downtime_code_id', 'centre_downtime');

// Define field formats

// Define system defaults
		$this->getField('time_period')->setDefault('M');
		
// Define enumerated types
		$this->setEnum('time_period',array('S'=>'Second'
										  ,'M'=>'Minute'
										  ,'H'=>'Hour'));
 		
 		
	}

}

// End of MFShiftDowntime

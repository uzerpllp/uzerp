<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WorkSchedulePart extends DataObject
{
	
	protected $version = '$Revision: 1.2 $';
	
	protected $defaultDisplayFields = array( 'order_qty'
											,'uom_name'
											,'description'
											,'job_no'
											,'order_number'
											,'status'=>'Order Status'
											,'work_schedule_id'
											,'productline_header_id'
											,'order_id'
											);
	
	function __construct($tablename = 'eng_work_schedule_parts')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->idField			= 'id';
		
		$this->identifierField	= 'id';
//		$this->orderby			= 'lastupdated';
//		$this->orderdir			= 'DESC';
		
		$this->setTitle('work_schedule_parts');
		
		// Define relationships
		$this->belongsTo('WorkSchedule', 'work_schedule_id', 'job_no');
		$this->belongsTo('POProductLineHeader', 'productline_header_id', 'description');
		$this->belongsTo('POrder', 'order_id', 'order_number');
		
		$this->hasOne('POrder', 'order_id', 'order_detail');
		
		// Define field formats
		
		// Define field defaults
		
		// Define validation
		
		// Define enumerated types
		
		// Define Access Rules
		
		// Define link rules for sidebar related view
							
	}

}

// end of WorkSchedulePart

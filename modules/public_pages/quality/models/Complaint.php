<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Complaint extends DataObject
{

	protected $version = '$Revision: 1.14 $';

	protected $defaultDisplayFields = array(
		'complaint_number',
		'date',
		'retailer',
		'customer',
		'complaint_code',
		'product',
		'date_complete',
		'assigned_to',
		'problem'
	);
	
	function __construct($tablename = 'qc_complaints')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField	='id';
		$this->orderby	= 'complaint_number';
		$this->orderdir	= 'desc';
		
// Define relationships
		$this->belongsTo('SLCustomer', 'slmaster_id', 'retailer');
		$this->belongsTo('STItem', 'stitem_id', 'product');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
		$this->belongsTo('ComplaintCode', 'complaint_code_id', 'complaint_code');
		$this->belongsTo('SupplementaryComplaintCode', 'supplementary_code_id', 'supplmentary_code');
		$this->belongsTo('User', 'assignedto', 'assigned_to');
		
// Define field formats

// Define validation
		
// Define enumerated types

// Define system defaults
	}
	
	static function Factory($data, &$errors, $do)
	{
		if (!isset($data['id']) || $data['id']=='')
		{
		
			$generator = new ComplaintNumberHandler();
			$data['complaint_number'] = $generator->handle(new $do);
		
		}
		
		return parent::Factory($data, $errors, $do);

	}

}

// End of Complaint

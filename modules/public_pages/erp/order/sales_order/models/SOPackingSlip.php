<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SOPackingSlip extends DataObject
{

	protected $version = '$Revision: 1.4 $';

	protected $defaultDisplayFields = array('order_number'
											,'name'
											,'tracking_code'
											,'courier'
											,'courier_service'
											,'contents'
											);

	function __construct($tablename = 'so_packing_slips')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField = 'id';

// Define relationships
 		$this->belongsTo('SOrder', 'order_id', 'order_number');
 		$this->hasOne('SOrder', 'order_id', 'order_detail');

// Define field formats

// Define enumerated types

// Define system defaults

	}

}

// End of SOPackingSlip

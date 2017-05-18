<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ARTransaction extends DataObject
{

	protected $version = '$Revision: 1.9 $';
	
	protected $defaultDisplayFields = array('transaction_date'
											,'transaction_type'
											,'armaster'=>'Asset Code'
											,'from_group'
											,'from_location'
											,'to_group'
											,'to_location'
											,'value'
											,'description'
											,'armaster_id'
											,'from_group_id'
											,'from_location_id'
											,'to_group_id'
											,'to_location_id'
											);
											
	function __construct($tablename = 'ar_transactions')
	{
		
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics		
		$this->idField = 'id';
		
// Define validation
		
// Define relationships
		$this->belongsTo('ARMaster', 'armaster_id', 'armaster'); 
		$this->belongsTo('Currency', 'currency_id', 'currency'); 
		$this->belongsTo('ARGroup', 'from_group_id', 'from_group'); 
		$this->belongsTo('ARLocation', 'from_location_id', 'from_location'); 
		$this->belongsTo('ARGroup', 'to_group_id', 'to_group'); 
		$this->belongsTo('ARLocation', 'to_location_id', 'to_location'); 
		
// Define field formats		
		
// Define enumerated types
		$this->setEnum(
			'transaction_type',
			array(
				'A'	=> 'Addition',
				'D'	=> 'Disposal',
				'T'	=> 'Transfer'
			)
		);
		
	}
	
	function cb_loaded()
	{
		
		// then set these formatters here because they depend on the loaded currency_id
 		$this->getField('value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
 		
	}

	function add($data, $type, &$errors)
	{
		$data['transaction_type'] = $type;
		return DataObject::Factory($data, $errors, 'ARTransaction');
	}
	
	function addition()
	{
		return $this->getEnumKey('transaction_type', 'Addition');
	}
	
	function disposal()
	{
		return $this->getEnumKey('transaction_type', 'Disposal');
	}

}

// end of ARTransaction.php

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ARGroup extends DataObject
{

	protected $version = '$Revision: 1.7 $';
	
	function __construct($tablename='ar_groups')
	{

		$this->defaultDisplayFields = array('description'
											,'depn_method'=>'Depreciation Method'
											,'depn_term'=>'Depreciation Term (years)'
											,'depn_rate1'=>'Depreciation Rate 1'
											,'depn_rate2'=>'Depreciation Rate 2'
											,'asset_cost_account'
											,'asset_depreciation_account'
											,'depreciation_charge_account'
											,'disposals_account');
		
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics		
		$this->idField='id';
		
		$db = DB::Instance();
		
		$this->identifierField = array('description', $db->qstr('-').' as c1', 'depn_term', $db->qstr('years').' as c2');
		
		$this->identifierFieldJoin = ' ';
		
// Define validation
		$this->validateUniquenessOf(array('description', 'depn_term')); 
		
// Define relationships
		$this->belongsTo('GLAccount', 'asset_cost_glaccount_id', 'asset_cost_account');
 		$this->belongsTo('GLAccount', 'asset_depreciation_glaccount_id', 'asset_depreciation_account');
 		$this->belongsTo('GLAccount', 'depreciation_charge_glaccount_id', 'depreciation_charge_account');
 		$this->belongsTo('GLAccount', 'disposals_glaccount_id', 'disposals_account');
		
// Define field formats		
 		
// Define enumerated types
 		$this->setEnum('depn_method'
					  ,array('E'=>'Economic Life Table'
							,'P'=>'Percentage'
							,'R'=>'Reducing Balance'
							,'S'=>'Straight Line'));
	}

}

// End of ARGroup

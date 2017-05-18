<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class WHBin extends DataObject {

	protected $defaultDisplayFields = array('bin_code'
											,'description'
											);

	function __construct($tablename='wh_bins') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->identifierField="bin_code ||'-'|| description";		
		$this->orderby='bin_code';
		
 		$this->validateUniquenessOf(array('whlocation_id','bin_code'));
 		
// Define relationships
 		$this->belongsTo('WHLocation', 'whlocation_id', 'whlocation'); 
 		$this->hasMany('STBalance', 'balances', 'whbin_id'); 
 		
// Define field formats

// Define enumerated types
 		
	}

	static function getBinList($whlocation_id) {
		$bins=new WHBin();
		$cc=new ConstraintChain();
		$cc->add(new Constraint('whlocation_id', '=', $whlocation_id));
		return $bins->getAll($cc);
	}

	static function validBinLocation ($bin_id, $location_id) {
		$whbin=new WHBin();
		$whbin->load($bin_id);
		if (!$whbin || $whbin->whlocation_id<>$location_id) {
			return false;
		} else {
			return true;
		}
	}
	
}
?>
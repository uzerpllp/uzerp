<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFDept extends DataObject {

	protected $version='$Revision: 1.5 $';
	
	function __construct($tablename='mf_depts') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->orderby='dept_code';
		
		$this->identifierField='dept_code||\' - \'||dept';
 		$this->validateUniquenessOf('dept_code'); 
 		
// Define relationships
 		$this->hasMany('MFCentre','centres','mfdept_id');
 		
// Define field formats

// Define enumerated types
 		
	}
	
	public function save() {
		$db=DB::Instance();
		$db->StartTrans();
		$result=parent::save();
		if ($result && $this->production_recording=='false') {
			foreach ($this->centres as $centre) {
				if ($centre->production_recording=='t') {
					$centre->production_recording=false;
					if (!$centre->save()) {
						$result=false;
						$db->FailTrans();
						break;
					}
				}
			}
		}
		$db->CompleteTrans();
		return $result;
	}

}
?>
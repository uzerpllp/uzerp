<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SLAllocation extends DataObject {

	protected $version='$Revision: 1.1 $';
	
	protected $defaultDisplayFields = array('customer'
											,'transaction_date'
											,'transaction_type'
											,'payment_value'
											,'statement'
											);

	function __construct($tablename='sl_allocation_details') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		
// Define relationships
		$this->belongsTo('SLTransaction','sl_transaction_id','transaction');
		
// Define field formats

// Define system defaults
		
// Define enumerated types
		
	}

	static function saveAllocation ($transactions, &$errors = []) {
		$db=DB::Instance();
		$db->StartTrans();
		$alloc_id=$db->GenID('sl_allocation_id_seq');
		foreach($transactions as $id=>$value) {
//			$trans = new SLTransaction();
//			$trans->load($id);
			$data=array('allocation_id'=>$alloc_id,
						'transaction_id'=>$id,
//						'payment_value'=>$trans->gross_value,
						'payment_value'=>$value);
			$alloc = DataObject::Factory($data, $errors, 'SLAllocation');
			if (count($errors)>0 || !$alloc->save()) {
				break;
			}
		}
		
		if (count($errors)>0) {	
			$db->FailTrans();
			$db->CompleteTrans();
			return false;
		}
		return $db->CompleteTrans();
	}
}
?>

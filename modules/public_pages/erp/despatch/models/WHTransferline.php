<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class WHTransferline extends DataObject {

	protected $version='$Revision: 1.3 $';
	
	protected $defaultDisplayFields = array('stitem'
										   ,'uom_name'
										   ,'transfer_qty'
										   ,'remarks');

	function __construct($tablename='wh_transfer_lines') {
		parent::__construct($tablename);
		$this->idField='id';
//		$this->identifierField="action_name";
		
		$this->belongsTo('WHLocation', 'from_whlocation_id', 'from_location');
		$this->belongsTo('WHLocation', 'to_whlocation_id', 'to_location');
		$this->belongsTo('WHTransfer', 'wh_transfer_id', 'transfer_number');
		$this->belongsTo('STItem', 'stitem_id', 'stitem');
		$this->belongsTo('STuom', 'stuom_id', 'uom_name');
				
	}

	function moveStock ($whtransfer, &$errors) {
		$data=array();
		$data['stitem_id']=$this->stitem_id;
		$data['whaction_id']=$whtransfer->transfer_action;
		$data['process_name']='T';
		$data['process_id']=$whtransfer->id;
		$data['remarks']=$this->remarks;
		$data['from_whlocation_id']=$this->from_whlocation_id;
		$data['to_whlocation_id']=$this->to_whlocation_id;
		$data['qty']=$this->transfer_qty;
		
		$db=DB::Instance();
		$db->StartTrans();
		
		$transactions=array();
		$transactions=STTransaction::prepareMove($data, $errors);
		
		if (count($transactions)>0 && count($errors)==0) {
			foreach ($transactions as $transaction) {
				if (!$transaction->save($errors)) {
					$errors[]='Error saving transaction';
					break;
				}
			}
		}
		if (count($errors)>0) {
			$db->FailTrans();
		} else {
			$db->CompleteTrans();
		}
		
	}

}
?>

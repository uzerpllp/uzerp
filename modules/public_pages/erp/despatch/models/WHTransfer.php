<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class WHTransfer extends DataObject {

	protected $version='$Revision: 1.3 $';
	
	protected $defaultDisplayFields = array('transfer_number'
										   ,'due_transfer_date'
										   ,'status'
										   ,'actual_transfer_date'
										   ,'from_location'
										   ,'to_location'
										   ,'description');

	private $unsaved_lines=array();
	
	function __construct($tablename='wh_transfers') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField="transfer_number";
		$this->orderby='transfer_number';
		
		$this->belongsTo('Companyaddress', 'from_address_id', 'from_address');
		$this->belongsTo('Companyaddress', 'to_address_id', 'to_address');
		$this->belongsTo('WHLocation', 'from_whlocation_id', 'from_location');
		$this->belongsTo('WHLocation', 'to_whlocation_id', 'to_location');
		$this->belongsTo('WHAction', 'transfer_action', 'action_name');
		$this->hasMany('WHTransferline', 'transfer_lines', 'wh_transfer_id');
		
		$this->setEnum('status'
							,array('C'=>'Cancelled'
								  ,'N'=>'Awaiting Transfer'
								  ,'T'=>'Transferred'
								)
						);
	}

	public static function awaitingTransferStatus() {
		return 'N';
	}
	
	public static function transferredStatus() {
		return 'T';
	}
	
	function awaitingTransfer() {
		return ($this->status=='N');
	}
	
	function transferred() {
		return ($this->status=='T');
	}
	
	function cancel() {
		$this->status='C';
		return parent::save();
	}
	
	function transfer() {
		$this->status='T';
		return parent::save();
	}
	
	public static function WHTFactory($header_data, $lines_data, &$errors = []) {

		if (!isset($header_data['transfer_number'])) {
			$gen_id=new WarehouseTransferNumberHandler();
			$header_data['transfer_number']=$gen_id->handle(new WHTransfer());
		}
		
		$transfer_header=DataObject::Factory($header_data, $errors, 'WHTransfer');
		
		if(count($errors)==0 && $transfer_header) {
			if (count($lines_data)>0) {
				foreach ($lines_data as $line) {
					$line['wh_transfer_id']=$transfer_header->id;
					$line['from_whlocation_id']=$header_data['from_whlocation_id'];
					$line['to_whlocation_id']=$header_data['to_whlocation_id'];
					$transfer_line=DataObject::Factory($line, $errors, 'WHTransferline');
					if ($transfer_line) {
						$transfer_header->unsaved_lines[]=$transfer_line;
					} else {
						$errors[]='Errors in Transfer Line';
						break;
					}
				}
			} else {
				$errors[]='No Transfer Lines';
			}
		}
		
		return $transfer_header;
	}
	
	function save($debug=false, &$errors = []) {
		if (parent::save()) {
			foreach ($this->unsaved_lines as $line) {
				if (!$line->save()) {
					$errors[]='Failed to save Transfer Line';
					break;
				}
			}
			if (count($errors) > 0) return false;
		} else {
			$errors[]='Failed to save Transfer Header';
			return false;
		}
		return true;
	}
	
}
?>

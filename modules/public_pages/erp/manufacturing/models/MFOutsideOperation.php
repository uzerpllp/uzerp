<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFOutsideOperation extends DataObject {

	protected $defaultDisplayFields = array('op_no'
											,'start_date'
											,'end_date'
											,'stitem_id'
											,'description'
											,'latest_osc'
											);
	
	function __construct($tablename='mf_outside_ops') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField='id';
		
		
 		$this->validateUniquenessOf(array('stitem_id', 'op_no'));
 		$this->belongsTo('STItem', 'stitem_id', 'stitem');
	}

	public static function globalRollOver() {
		$db = DB::Instance();
		$date = date('Y-m-d');
		$query = "UPDATE mf_outside_ops
					SET std_osc=latest_osc
					WHERE (start_date <= '".$date."' OR start_date IS NULL) AND (end_date > '".$date."' OR end_date IS NULL) AND usercompanyid=".EGS_COMPANY_ID;
		return ($db->Execute($query) !== false);
	}

}
?>
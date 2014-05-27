<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Opportunitystatus extends DataObject {

	function __construct($tablename='opportunitystatus') {
		parent::__construct($tablename);
		$this->idField='id';

		$this->orderby = 'position';
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Company', 'companyid', 'companyid');
		$this->hasMany('Opportunity','opportunities','status_id');
	}
	
	public function getTotalCost(ConstraintChain $cc=null) {
// TODO : replace with getSum function in DataObject class
		$db = DB::Instance();
		$query = 'SELECT COALESCE(sum(cost),0) FROM opportunities WHERE status_id='.$db->qstr($this->id).' AND usercompanyid='.$db->qstr(EGS_COMPANY_ID);
		if($cc!=null) {
			$where = $cc->__toString();
			if(!empty($where)) {
				$query.=' AND '.$where;
			}
		}
		
		$total = $db->GetOne($query);
		if($total===false) {
			die($db->ErrorMsg());
		}
		return $total;
	}

}
?>

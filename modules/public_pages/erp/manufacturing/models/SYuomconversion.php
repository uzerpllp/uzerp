<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SYuomconversion extends DataObject {

	function __construct($tablename='sy_uom_conversions') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->orderby='from_uom_name';
		
		$this->belongsTo('STuom', 'from_uom_id', 'from_uom_name');
		$this->belongsTo('STuom', 'to_uom_id', 'to_uom_name');
		
	}

	public function convertFrom($fromUoM
								, $toUoM
								, $value) {
// Converts the value expressed as fromUoM
// Returns the value expressed as toUoM
		$db=&DB::Instance();
		$cc=new ConstraintChain();
		$cc->add(new Constraint('from_uom_id', '=', $fromUoM));
		$cc->add(new Constraint('to_uom_id', '=', $toUoM));
		$uom=$this->loadBy($cc);
		if ($uom) {
			return ($value*$uom->conversion_factor);
		}

		$cc=new ConstraintChain();
		$cc->add(new Constraint('to_uom_id', '=', $fromUoM));
		$cc->add(new Constraint('from_uom_id', '=', $toUoM));
		$uom=$this->loadBy($cc);
		if ($uom) {
			return ($value/$uom->conversion_factor);
		} else {
			return false;
		}
	}
		
	public function getUomList($uom_id) {
		$db=&DB::Instance();
		$query="SELECT to_uom_id
					, to_uom_name
				FROM sy_uomconversionsoverview
				WHERE from_uom_id=".$uom_id.
				" UNION
				SELECT from_uom_id
					, from_uom_name
				FROM sy_uomconversionsoverview
				WHERE to_uom_id=".$uom_id;
		return $db->GetAssoc($query);
	}

}
?>
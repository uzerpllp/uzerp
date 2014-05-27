<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class STuomconversion extends DataObject {

	protected $version='$Revision: 1.10 $';
	
	protected $defaultDisplayFields = array('stitem'			=> 'Stock Item'
											,'from_uom_name'
											,'conversion_factor'
											,'to_uom_name'
											);
	
	function __construct($tablename='st_uom_conversions') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField=array('stitem_id');
		$this->setTitle('Stock Conversion');
		
		$this->belongsTo('STItem', 'stitem_id', 'stitem');
		$this->belongsTo('STuom', 'from_uom_id', 'from_uom_name');
		$this->belongsTo('STuom', 'to_uom_id', 'to_uom_name');
		
	}

	public function convertFrom($stitem
								, $fromUoM
								, $toUoM
								, $value) {
// Converts the value expressed as fromUoM
// Returns the value expressed as toUoM
		$db=&DB::Instance();
		$cc=new ConstraintChain();
		$cc->add(new Constraint('stitem_id', '=', $stitem));
		$cc->add(new Constraint('from_uom_id', '=', $fromUoM));
		$cc->add(new Constraint('to_uom_id', '=', $toUoM));
		$uom=$this->loadBy($cc);
		if ($uom) {
			return ($value*$uom->conversion_factor);
		}

		$cc=new ConstraintChain();
		$cc->add(new Constraint('stitem_id', '=', $stitem));
		$cc->add(new Constraint('to_uom_id', '=', $fromUoM));
		$cc->add(new Constraint('from_uom_id', '=', $toUoM));
		$uom=$this->loadBy($cc);
		if ($uom) {
			return ($value/$uom->conversion_factor);
		} else {
			return false;
		}
	}
	
	public function getUomList($stitem_id, $uom_id) {
		$db=&DB::Instance();
		$query="SELECT to_uom_id
					, to_uom_name
				FROM st_uomconversionsoverview
				WHERE stitem_id=".$stitem_id.
				" AND from_uom_id=".$uom_id.
				" UNION
				SELECT from_uom_id
					, from_uom_name
				FROM st_uomconversionsoverview
				WHERE stitem_id=".$stitem_id.
				" AND to_uom_id=".$uom_id;
		return $db->GetAssoc($query);
		
	}
	
	function getStockItem($stitem_id) {
		$db = DB::Instance();
		$query = "select item_code||' - '||description from st_items where id=".$db->qstr($stitem_id);
		$getStockItem = $db->GetOne($query);
		return $getStockItem;
	}

	function getFromUoM($from_uom_id) {
		$db = DB::Instance();
		$query = "select uom_name from st_uoms where id=".$db->qstr($from_uom_id);
		$getFromUoM = $db->GetOne($query);
		return $getFromUoM;
	}

}
?>

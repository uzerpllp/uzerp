<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class STuom extends DataObject {

	function __construct($tablename='st_uoms') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->orderby='uom_name';
		
		$this->identifierField='uom_name';
		$this->validateUniquenessOf('uom_name'); 

	}

	public function getUomName() {
		return $this->uom_name;
	}

	/**
	 * Get the id of the uom by name
	 *
	 * @param string $uom_name
	 * @return integer
	 */
	function getUomID($uom_name) {
		$cc=new ConstraintChain();
		$cc->add(new Constraint('lower(uom_name)', '=', strtolower($uom_name)));
		$uom=$this->loadBy($cc);
		if ($uom) {
			return ($uom->id);
		} else {
			return false;
		}
	}

}
?>
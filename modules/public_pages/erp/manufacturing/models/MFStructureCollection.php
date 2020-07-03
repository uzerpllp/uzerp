<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFStructureCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='MFStructure', $tablename='mf_structuresoverview') {
		parent::__construct($do, $tablename);
		$this->title='Item Structure';
	}

	/**
	 * Return the the structures (BOM) of on item
	 *
	 * @param boolean $stitem_id
	 * @return MFStructureCollection
	 */
	public static function getCurrent($stitem_id=false) {
		$structures = new self;
		$cc1 = new ConstraintChain();
		$cc1->add(new Constraint('stitem_id', '=', $stitem_id));
		$cc1->add(new Constraint('start_date', '<=', fix_date(date(DATE_FORMAT))));

		$cc2=new ConstraintChain();
		$cc2->add(new Constraint('end_date', '>=', fix_date(date(DATE_FORMAT))));
		$cc2->add(new Constraint('end_date', 'is', 'NULL'),'OR');

		$sh = new SearchHandler($structures, false);
		$sh->addConstraintChain($cc1);
		$sh->addConstraintChain($cc2);
		$structures->load($sh);
		return $structures;
	}

}
?>
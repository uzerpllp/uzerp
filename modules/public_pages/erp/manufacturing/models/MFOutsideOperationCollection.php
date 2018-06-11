<?php
 
/** 
 *	(c) 2018 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFOutsideOperationCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='MFOutsideOperation', $tablename='mf_outside_opsoverview') {
		parent::__construct($do, $tablename);
			
	}

	/**
	 * Load the Operations by stock item, curent at the specified date
	 * 
	 * @param integer
	 *    $stitem_id Stock item record id
	 * @param string $at_date
	 *    Load operations active at a particular date (ISO date string, 'YYYY-MM-DD')
	 */
	function loadItemOutsideOperations($stitem_id, $at_date='')
	{
		$sh = new SearchHandler($this, false);
		$cc = new ConstraintChain;
		$cc->add(new Constraint('stitem_id', '=', $stitem_id));
		$cd = currentDateConstraint($at_date);
		$cc->add($cd);
		$sh->addConstraintChain($cc);
		$sh->setOrderby('op_no', 'asc');
		$this->load($sh);
	}
}
?>
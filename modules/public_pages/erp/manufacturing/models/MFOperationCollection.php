<?php
 
/** 
 *	(c) 2018 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFOperationCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='MFOperation', $tablename='mf_operationsoverview') {
		parent::__construct($do, $tablename);
		$this->title='Manufacturing Operations';
	}

	/**
	 * Load the Operations by stock item, curent at the specified date
	 * 
	 * @param integer
	 *    $stitem_id Stock item record id
	 * @param string $at_date
	 *    Load operations active at a particular date (ISO date string, 'YYYY-MM-DD')
	 */
	function loadItemOperations($stitem_id, $at_date='')
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
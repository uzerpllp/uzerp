<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLPeriodEndBalance extends DataObject {

	protected $version='$Revision: 1.1 $';
	
	function __construct($tablename='gl_period_end_balances') {
		parent::__construct($tablename);
		$this->idField='id';
		
 		$this->validateUniquenessOf(array('glperiods_id', 'glaccount_id', 'glcentre_id'));
		$this->belongsTo('GLAccount', 'glaccount_id', 'account');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'cost_centre');
 		$this->belongsTo('GLPeriod', 'glperiods_id', 'glperiod'); 
 		
	}

}
?>
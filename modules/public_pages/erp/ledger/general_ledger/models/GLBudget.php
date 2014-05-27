<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLBudget extends DataObject {

	protected $version='$Revision: 1.4 $';
	
	function __construct($tablename='gl_budgets') {
		
		$this->defaultDisplayFields = array('account'=>'Account'
											,'centre'=>'Centre'
											,'periods'=>'Period'
											,'value'=>'Value'
											,'glaccount_id'=>'glaccount_id'
											,'glcentre_id'=>'glcentre_id'
											,'glperiods_id'=>'glperiods_id');
		
		parent::__construct($tablename);
		$this->idField='id';

		$this->orderby=array('centre','account');
		
 		$this->belongsTo('GLAccount', 'glaccount_id', 'account');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'centre');
 		$this->belongsTo('GLPeriod', 'glperiods_id', 'periods'); 
		
 		$this->validateUniquenessOf(array('glperiods_id','glcentre_id', 'glaccount_id'));
	}

}
?>
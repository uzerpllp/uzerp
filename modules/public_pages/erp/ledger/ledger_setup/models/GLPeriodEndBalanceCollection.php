<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLPeriodEndBalanceCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.1 $';
	public $field;
		
	function __construct($do='GLPeriodEndBalance', $tablename='gl_period_end_balances_overview') {
		parent::__construct($do, $tablename);
			
	}

	public function create($period) {
		
		$balance=new GLBalance();
		// Set the identifier field - this is used by bulk_insert
		// to count the distinct rows that should be inserted
		$balance->identifierField="glcentre_id||'-'||glaccount_id";
		$balance->orderby='';
		$balances=new GLBalanceCollection($balance);
		$balances->setTablename('gl_year_to_date_summary');
		$balances->orderby='';
		$sh=new SearchHandler($balances, false);

		$fields=array($period->id.' as glperiods_id');
		$fields=array('glcentre_id'
					, 'glaccount_id'
					, 'glperiods_id'
					, 'mth_actual'
					, 'ytd_actual'
					, 'usercompanyid'
					, "'".EGS_USERNAME."' as createdby"
					, "'".EGS_USERNAME."' as alteredby");
		$sh->setFields($fields);
		$sh->addConstraint(new Constraint('year', '=', $period->year));
		$sh->addConstraint(new Constraint('period', '=', $period->period));
		
		//	Insert the year to date values
		$result=$this->bulk_insert(array('glcentre_id'
		    							,'glaccount_id'
		    							,'glperiods_id'
		    							,'mth_actual'
		    							,'ytd_actual'
		     							,'usercompanyid'
		     							,'createdby'
		     							,'alteredby')
		    						,$sh);

		return $result;
	}
	
}
?>
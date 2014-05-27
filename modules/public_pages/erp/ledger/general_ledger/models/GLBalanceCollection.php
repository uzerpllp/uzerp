<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLBalanceCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.11 $';
	public $field;
	
	function __construct($do='GLBalance', $tablename='glbalancesoverview') {
		parent::__construct($do, $tablename);

		$this->orderby=array('centre','account');
	}

	function getYTD ($ytd_periods, $print=false, $use_saved_search) {

		if (count($ytd_periods)>0) {
			$sh=new SearchHandler($this, $use_saved_search);
			if ($print) {
				$sh->setLimit(0);
			} else {
				$sh->extract();
			}
			$fields=array('centre||account', 'centre', 'account');
			$sh->setOrderBy($fields);
			$fields=array_merge($fields, array('glcentre_id', 'glaccount_id'));
			$sh->setGroupBy($fields);
			$fields[]='sum(value) as value';
			$sh->setFields($fields);
			$sh->addConstraint(new Constraint('glperiods_id', 'in', '('.implode(',', $ytd_periods).')'));
			$this->load($sh);
		}

	}

	function getYearEndBalances ($glperiod_ids, $type) {
		if (count($glperiod_ids)>0) {
			$sh=new SearchHandler($this, false);
			switch ($type) {
				case 'P':
					$fields=array('actype');
					break;
				case 'B':
					$fields=array('glcentre_id||\'-\'||glaccount_id', 'glcentre_id', 'glaccount_id');
					break;
			}
			$sh->setGroupBy($fields);
			$sh->setOrderBy($fields);
			$fields[]='sum(value) as value';
			$sh->setFields($fields);
			$periodsYTD='('.implode(',', $glperiod_ids).')';
			$sh->addConstraint(new Constraint('actype', '=', $type));
			$sh->addConstraint(new Constraint('glperiods_id', 'in', $periodsYTD));
			$this->load($sh);
		}
		
	}
	
}
?>
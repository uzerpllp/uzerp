<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class glbalancesSearch extends BaseSearch {

	protected $version='$Revision: 1.9 $';
	
	public static function useDefault(&$search_data=null, &$errors=array(), $defaults=null) {
		$search = new glbalancesSearch($defaults);

// Search by Account
			$search->addSearchField(
				'glaccount_id',
				'Account',
				'multi_select',
				array(),
				'advanced'
				);
			$glaccount = new GLAccount();
			$search->setOptions('glaccount_id',$glaccount->getAll());
// Search by Centre
			$search->addSearchField(
				'glcentre_id',
				'Centre',
				'multi_select',
				array(),
				'advanced'
				);
			$glcentre = new GLCentre();
			$search->setOptions('glcentre_id',$glcentre->getAll());

// Search by Period
		$currentPeriod=new GLPeriod();
		$currentPeriod->getCurrentPeriod();
		if ($currentPeriod) {
			$default_period = array($currentPeriod->id);
		} else {
			$default_period = array();
		}
		$search->addSearchField(
				'glperiods_id',
				'Period',
				'multi_select',
				$default_period,
				'advanced'
				);
		$glperiod = new GLPeriod();
		$search->setOptions('glperiods_id',$glperiod->getAll());
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
	public static function trialBalance(&$search_data=null, &$errors=array(), $defaults=null) {
		$search = new glbalancesSearch($defaults);

// Search by Period
		$currentPeriod=GLPeriod::getPeriod(date('Y-m-d'));
		if (($currentPeriod) && (count($currentPeriod) > 0)) {
			$default_period = $currentPeriod['id'];
		}
		$search->addSearchField(
				'glperiods_id',
				'Period',
				'select',
				$default_period,
				'basic'
				);
		$glperiod = new GLPeriod();
		$glperiods=$glperiod->getAll();
		$search->setOptions('glperiods_id',$glperiods);
		
		$search->setSearchData($search_data,$errors,'trialBalance');
		return $search;
	}
		
}
?>

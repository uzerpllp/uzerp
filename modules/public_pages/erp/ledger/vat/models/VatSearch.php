<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class VatSearch extends BaseSearch {

	protected $version='$Revision: 1.7 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new VatSearch($defaults);

		$search->addSearchField(
			'box',
			'Box',
			'hidden',
			'',
			'hidden'
		);
		$default_year = date('Y');
		$default_tax_period = 1;
		$glperiod = GLPeriod::getPeriod(date('Y-m-d'));
		if (($glperiod) && (count($glperiod) > 0)) {
			$default_year = $glperiod['year'];
			$default_tax_period = $glperiod['tax_period'];
		}
		$search->addSearchField(
				'year',
				'Year',
				'select',
				$default_year,
				'basic'
			);
		$search->addSearchField(
				'tax_period',
				'Tax Period',
				'select',
				$default_tax_period,
				'basic'
			);
		
		$glperiods = new GLPeriodCollection;
		$sh = new SearchHandler($glperiods, false);
		$sh->setOrderBy('year');
		$glperiods->load($sh);
		$glperiods = $glperiods->getContents();
		$options = array();
		foreach ($glperiods as $glperiod) {
			if (!array_key_exists($glperiod->year, $options)) {
				$options[$glperiod->year] = $glperiod->year;
			}
		}
		$search->setOptions('year',$options);
		
		$tax_periods = GLPeriod::getTaxPeriods();
		$options = array_combine($tax_periods, $tax_periods);
		$search->setOptions('tax_period',$options);
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
	public static function transactions($search_data=null, &$errors=array(), $defaults=null, $date='received_date') {

		$search = new VatSearch($defaults);

		$search->addSearchField(
			$date,
			$date,
			'between',
			array('from'=>date(DATE_FORMAT), 'to'=>date(DATE_FORMAT)),
			'basic'
		);

		$search->setSearchData($search_data,$errors,'transactions');
		return $search;
	}

}
?>
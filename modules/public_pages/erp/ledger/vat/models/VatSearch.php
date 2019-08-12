<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class VatSearch extends BaseSearch {

	protected $version='$Revision: 1.7 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new VatSearch($defaults);

		$glperiod = DataObjectFactory::Factory('GLPeriod');
		$glperiod->getCurrentTaxPeriod();

		$search->addSearchField(
				'year',
				'Year',
				'select',
				$glperiod->year,
				'basic'
			);
		$search->addSearchField(
				'tax_period',
				'Tax Period',
				'select',
				null,
				'advanced'
			);
		$search->addSearchField(
				'tax_period_closed',
				'Tax Period Status',
				'select',
				null,
				'advanced'
			);

		$search->addSearchField(
				'finalised',
				'Submitted',
				'select',
				null,
				'advanced'
			);
		
		$status_options = [
			'0' => 'Any',
			'f' => 'Open',
			't' => 'Closed'
		];

		$search->setOptions('tax_period_closed', $status_options);

		$status_options = [
			'0' => 'All',
			't' => 'Yes',
			'f' => 'No'
		];

		$search->setOptions('finalised', $status_options);

		$glperiods = new GLPeriodCollection;
		$sh = new SearchHandler($glperiods, false);
		$sh->setOrderBy('year');
		$glperiods->load($sh);
		$glperiods = $glperiods->getContents();
		
		$options = array(
			'0' => 'All'
		);
		foreach ($glperiods as $glperiod) {
			if (!array_key_exists($glperiod->year, $options)) {
				$options[$glperiod->year] = $glperiod->year;
			}
		}
		$search->setOptions('year',$options);
		
		$options = array(
			'0' => 'All'
		);
		$tax_periods = GLPeriod::getTaxPeriods();

		$options += array_combine($tax_periods, $tax_periods);
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
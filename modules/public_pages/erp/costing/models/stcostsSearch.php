<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class stcostsSearch extends BaseSearch {

	protected $version='$Revision: 1.4 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new stcostsSearch($defaults);
// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'select',
			'',
			'basic'
		);
// Search by Date
//		$search->addSearchField(
//			'start_date/end_date',
//			'Date',
//			'betweenfields',
//			date(DATE_FORMAT),
//			'basic'
//		);
// Search by Type
		$search->addSearchField(
			'type',
			'Type',
			'select',
			'',
			'basic'
		);
		$stitem = new STItem;
//		$chain = new ConstraintChain();
//		$chain->add(new Constraint('comp_class','=','M'));
//		if (isset($search_data['stitem_id'])) {
//			$chain->add(new Constraint('id', '!=', $search_data['stitem_id']));
//		}
		$stitems = $stitem->getAll();
		$options = array('' => 'All');
		$options += $stitems;
		$search->setOptions('stitem_id', $options);
		$stcost = new STCost;
		$options = array('' => 'All');
		$types = $stcost->getEnumOptions('type');
		$options += $types;
		$search->setOptions('type', $options);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}
?>
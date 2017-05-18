<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class workordersSearch extends BaseSearch {

	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new workordersSearch($defaults);
		
// Search by Order No.
		$search->addSearchField(
			'wo_number',
			'Order No',
			'equal'
		);
		
// Search by Status
		$search->addSearchField(
			'status',
			'status_is',
			'multi_select',
			array()
		);
		$wo=new MFWorkorder();
		$search->setOptions('status',$wo->getEnumOptions('status'));
		
// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'select',
			'',
			'advanced'
			);
		$stitem = new STItem();
		$chain = new ConstraintChain();
		$chain->add(new Constraint('comp_class','=','M'));
		$stitems=$stitem->getAll($chain);
		$options=array(''=>'All');
		$options=$options+$stitems;
		$search->setOptions('stitem_id',$options);

// Search by Required By Date
/* 'between' is not yet implemented
 		$search->addSearchField(
			'required_by',
			'Required Between',
			'between',
			'',
			'advanced'
		);
*/
		$search->addSearchField(
			'required_by',
			'Required Between',
			'between',
			'',
			'advanced'
		);
		
// Search by Stock Type Code
		$search->addSearchField(
			'type_code_id',
			'Type Code',
			'select',
			'',
			'advanced'
			);
		$typecode=new STTypecode();
		$typecodes=$typecode->getAll();
		$options=array(''=>'All');
		$options=$options+$typecodes;
		$search->setOptions('type_code_id',$options);

		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}
?>
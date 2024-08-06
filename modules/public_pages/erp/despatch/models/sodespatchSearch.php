<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class sodespatchSearch extends BaseSearch
{

	protected $version = '$Revision: 1.8 $';

	protected $fields = array();

	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new sodespatchSearch($defaults);

// Search by Customer
		$search->addSearchField(
			'slmaster_id',
			'Customer',
			'select',
			0,
			'advanced'
			);
		$customer = new SLCustomer();
		$options=array('0'=>'All');
		$customers=$customer->getAll(null, false, true);
		$options+=$customers;
		$search->setOptions('slmaster_id',$options);

// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'select',
			0,
			'advanced'
			);
		$stitems = new STItem();
		$options=array('0'=>'All');
		$stitems=$stitems->getAll();
		$options+=$stitems;
		$search->setOptions('stitem_id',$options);

// Search by Despatch Number
		$search->addSearchField(
			'despatch_number',
			'despatch_number',
			'equal',
			'',
			'advanced'
		);

// Search by Order Number
		$search->addSearchField(
			'order_number',
			'order_number',
			'equal',
			'',
			'advanced'
		);

// Search by Invoice Number
		$search->addSearchField(
			'invoice_number',
			'invoice_number',
			'equal',
			'',
			'advanced'
		);

// Search by Despatch Date
		$search->addSearchField(
			'despatch_date',
			'despatch_date_between',
			'between',
			'',
			'advanced'
		);

// Search by Status
		$search->addSearchField(
			'status',
			'status',
			'select',
			'N',
			'basic'
			);
		$options=array(''=>'All'
					  ,'N'=>'New'
					  ,'D'=>'Despatched'
					  ,'X'=>'Cancelled'
					);
		$search->setOptions('status',$options);

		$search->setSearchData($search_data,$errors);
		return $search;
	}

}

// End of sodespatchSearch

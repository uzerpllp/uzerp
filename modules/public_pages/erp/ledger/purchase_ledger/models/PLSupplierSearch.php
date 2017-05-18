<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PLSupplierSearch extends BaseSearch
{
	
	protected $version = '$Revision: 1.10 $';
	
	protected $fields = array();
	
	public static function useDefault($search_data = null, &$errors = array())
	{
		
		// Name
		$search = new PLSupplierSearch();
		$search->addSearchField(
			'name',
			'name_contains',
			'contains'
		);
		
		// Search by Active/Inactive Status
		$search->addSearchField(
			'date_inactive',
			'Show Suppliers',
			'null',
			'null',
			'advanced'
		);
		$options = array(''			=> 'All'
						,'not null'	=> 'Inactive'
						,'null'		=> 'Active');
		$search->setOptions('date_inactive', $options);
		
		// Currency
		$search->addSearchField(
			'currency_id',
			'currency',
			'select',
			'',
			'advanced'
		);
		$currency = DataObjectFactory::Factory('Currency');
		$currency_list = $currency->getAll();
		$options=array(''=>'All');
		$options+=$currency_list;
		$search->SetOptions('currency_id',$options);
		
		// Remittance
		$search->addSearchField(
			'remittance_advice',
			'remittance',
			'select',
			'',
			'advanced'
		);
		$options=array(''=>'All'
					  ,'TRUE'=>'Yes'
					  ,'FALSE'=>'No');
		$search->setOptions('remittance_advice',$options);
		
		// Order Method
		$search->addSearchField(
			'order_method',
			'order_method',
			'select',
			'',
			'advanced'
		);
		$options=array(''=>'All'
				  ,'P'=>'Print'
				  ,'F'=>'Fax'
				  ,'E'=>'Email'
				  ,'D'=>'EDI');
		$search->setOptions('order_method',$options);
		
		// Payment Type
		$search->addSearchField(
			'payment_type_id',
			'payment_type',
			'select',
			'',
			'advanced'
		);
		$payment_type = DataObjectFactory::Factory('PaymentType');
		$options = array(''=>'All');
		$options +=$payment_type->getAll();
		$search->setOptions('payment_type_id',$options);
		
		// Payment Terms
		$search->addSearchField(
			'payment_term_id',
			'payment_term',
			'select',
			'',
			'advanced'
		);
		$payment_term = DataObjectFactory::Factory('PaymentTerm');
		$options = array(''=>'All');
		$options = $payment_term->getAll();
		asort($options);
		$options=array(''=>'All')+$options;
		$search->setOptions('payment_term_id',$options);
		
		// Execute Search
		$search->setSearchData($search_data,$errors);
		return $search;
	}	
}

// End of PLSupplierSearch

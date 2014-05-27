<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintSearch extends BaseSearch {
	
	protected $version = '$Revision: 1.19 $';
	
	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		
		$search = new ComplaintSearch($defaults);
		$search->setSearchData($search_data, $errors);
		
		return $search;
		
	}
		
	public static function rrsearch($search_data = null, &$errors = array(), $defaults = null)
	{
		
		$search = new ComplaintSearch($defaults);

		$search->addSearchField(
			'customer',
			'Customer Name',
			'contains',
			'',
			'advanced'
		);
		
		$search->addSearchField(
			'type',
			'type',
			'hidden',
			'RR',
			'hidden'
		);
		
		$search->defaultFields();
		$search->setSearchData($search_data, $errors, 'rrsearch');
		
		return $search;
		
	}

	public static function sdsearch($search_data = null, &$errors = array(), $defaults = null)
	{
		
		$search = new ComplaintSearch($defaults);

		// Search by type
		$search->addSearchField(
			'type',
			'type',
			'hidden',
			'SD',
			'hidden'
		);
		
		$search->defaultFields();
		$search->setSearchData($search_data, $errors, 'sdsearch');
		
		return $search;
		
	}
	
	private function defaultFields()
	{
		
		// Search by Complaint ID
		$this->addSearchField(
			'complaint_number',
			'Complaint ID',
			'equal',
			'',
			'basic'
		);
		
		// Search by Problem
		$this->addSearchField(
			'problem',
			'problem',
			'contains',
			'',
			'basic'
		);
		
		// Search by Retailer
		$this->addSearchField(
			'slmaster_id',
			'Retailer',
			'select',
			0,
			'basic'
		);
		
		$slcustomer = DataObjectFactory::Factory('SLCustomer');
		
		$options  = array('0' => 'All');
		$options += $slcustomer->getAll(null, false, true);
		
		$this->setOptions('slmaster_id', $options);

		// Search by Type
		$this->addSearchField(
			'date_complete',
			'Status',
			'null',
			'',
			'basic'
		);
		
		$options = array(
			''			=> 'All',
			'NOT NULL'	=> 'Complete',
			'NULL'		=> 'Incomplete'
		);
		
		$this->setOptions('date_complete', $options);
		
		// Search by Complaint Code
		$this->addSearchField(
			'complaint_code_id',
			'Complaint Code',
			'select',
			'0',
			'advanced'
		);
		
		$complaint_code = DataObjectFactory::Factory('ComplaintCode');
		
		$options = array(
			'0'		=> 'All',
			'NULL'	=> 'Unallocated'
		);
		
		$options += $complaint_code->getAll();
		
		$this->setOptions('complaint_code_id', $options);	
				
		// Search by product_complaint
		$this->addSearchField(
			'product_complaint',
			'Product Complaint',
			'select',
			'',
			'advanced'
		);
		
		$options = array(
			''	=> 'All',
			't'	=> 'Yes',
			'f'	=> 'no'
		);
		
		$this->setOptions('product_complaint', $options);
		
		// Search by Product
		$this->addSearchField(
			'stitem_id',
			'Product',
			'select',
			0,
			'advanced'
		);
		
		$stitem = DataObjectFactory::Factory('STItem');
		
		$options  = array('0' => 'All');
		$options += $stitem->getAll();
		
		$this->setOptions('stitem_id', $options);
				
		// Search by Assigned To
		$this->addSearchField(
			'assignedto',
			'Assigned To',
			'select',
			'',
			'advanced'
		);
		
		$user = DataObjectFactory::Factory('User');
		
		$options  = array('' => 'All');
		$options += $user->getAll();
		
		$this->setOptions('assignedto', $options);

		// Search by Transaction Date
		$this->addSearchField(
			'date',
			'Complaint Date between',
			'between',
			'',
			'advanced'
		);

	}
	
}

// end of ComplaintSearch
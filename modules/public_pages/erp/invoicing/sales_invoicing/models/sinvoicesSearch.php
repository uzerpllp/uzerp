<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class sinvoicesSearch extends BaseSearch
{

	protected $version = '$Revision: 1.15 $';
	
	protected $fields = array();
	
	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new sinvoicesSearch($defaults);
		
		$invoice = DataObjectFactory::Factory('SInvoice');

		// Search by Status
		$search->addSearchField(
			'status',
			'status',
			'select',
			'',
			'basic'
			);
		$options = array_merge(array(''=>'All')
					  		,$invoice->getEnumOptions('status'));
		$search->setOptions('status', $options);
	
		// Search by Printed
		$search->addSearchField(
			'date_printed',
			'printed',
			'null',
			'',
			'basic'
		);
		$options = array(''			=> 'All'
						,'Null'		=> 'Not Printed'
						,'Not Null'	=> 'Printed');
		$search->setOptions('date_printed', $options);	
					
		// Search by SL Analysis
		$search->addSearchField(
			'sl_analysis_id',
			'SL Analysis',
			'select',
			0,
			'basic'
			);
		$slanalysis = DataObjectFactory::Factory('SLAnalysis');
		$options = array('0'=>'All');
		$options += $slanalysis->getAll();
		$search->setOptions('sl_analysis_id', $options);
			
		// Search by Invoice Date
		$search->addSearchField(
			'invoice_date',
			'invoice_date_between',
			'between',
			'',
			'basic'
		);
		
		// Search by Customer
		$search->addSearchField(
			'slmaster_id',
			'Customer',
			'select',
			0,
			'basic'
			);
		$customer = DataObjectFactory::Factory('SLCustomer');
		$options = array('0'=>'All');
		$customers = $customer->getAll(null, false, true, '', '');
		$options += $customers;
		$search->setOptions('slmaster_id', $options);

		// Search by Person
		$search->addSearchField(
			'person',
			'person',
			'contains',
			'',
			'basic'
		);

		// Search by Invoice Number
		$search->addSearchField(
			'invoice_number',
			'invoice_number',
			'equal',
			'',
			'advanced'
		);

		// Search by Sales Order Number
		$search->addSearchField(
			'sales_order_number',
			'sales_order_number',
			'equal',
			'',
			'advanced'
		);

		// Search by Customer Reference
		$search->addSearchField(
			'ext_reference',
			'customer reference begins',
			'begins',
			'',
			'advanced'
		);
			
		// Search by Transaction Type
		$search->addSearchField(
			'transaction_type',
			'transaction_type',
			'select',
			'',
			'advanced'
			);
		$options = array_merge(array(''=>'All')
					  		,$invoice->getEnumOptions('transaction_type'));
		$search->setOptions('transaction_type', $options);

		$search->setSearchData($search_data, $errors);
		return $search;
	}
		
	public static function invoices($search_data = null, &$errors = array(), $defaults = null)
	{

		$search = new sinvoicesSearch($defaults);
		
// Name
		$search->addSearchField(
			'slmaster_id',
			'customer',
			'select',
			0,
			'basic'
			);
		$customer = DataObjectFactory::Factory('SLCustomer');
		$options = array('0'=>'All');
		$customers = $customer->getAll(null, false, true, '', '');
		$options += $customers;
		$search->setOptions('slmaster_id', $options);
			
// Print Count
		$search->addSearchField(
			'print_count',
			'print_count',
			'hidden',
			0,
			'hidden'
		);
		
// Status
		$search->addSearchField(
			'status',
			'status',
			'hidden',
			'O',
			'hidden'
		);

// Type
		$search->addSearchField(
			'type',
			'type',
			'hidden',
			'',
			'hidden',
			false
		);
		
// Execute Search
		$search->setSearchData($search_data, $errors, 'invoices');
		return $search;
		
	}
	
}

// End of sinvoicesSearch

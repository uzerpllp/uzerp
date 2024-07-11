<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class productlinesSearch extends BaseSearch
{

	protected $version = '$Revision: 1.29 $';
	
	protected $fields = array();

	public static function supplierDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = productlinesSearch::useDefault($search_data, $errors, 'PLSupplier', $defaults);
		
// Search by Start Date; default is to show rows current at today's date
		$search->addSearchField(
			'start_date/end_date',
			'current at',
			'betweenfields',
			date(DATE_FORMAT),
			'advanced'
			);

// If product is a stock item, then need to restrict stock items by comp_class
			$search->addSearchField(
			'comp_class',
			'',
			'hidden',
			'',
			'hidden'
			);

		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('comp_class', 'is', 'NULL'));
		$cc->add(new Constraint('comp_class', 'in', "('B', 'S')"), 'OR');
		
		$search->setConstraint('comp_class', $cc);
		
		$search->setSearchData($search_data, $errors, 'PLSupplierdefaults');
		
		return $search;
	}

	public static function customerDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = productlinesSearch::useDefault($search_data, $errors, 'SLCustomer', $defaults);

// Search by Start Date; default is to show rows current at today's date
		$search->addSearchField(
			'start_date/end_date',
			'current at',
			'betweenfields',
//			date(DATE_FORMAT),
			'',
			'advanced'
			);

		$search->setSearchData($search_data, $errors, 'SLCustomerdefaults');
		return $search;
	}

	public static function customerPriceUplift($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = productlinesSearch::useDefault($search_data, $errors, 'SLCustomer', $defaults);

// Search by Productline Header
		$search->addSearchField(
			'productline_header_id',
			'productline_header',
			'hidden',
			'',
			'hidden'
		);
				
// Only display rows where the end date is null
		$search->addSearchField(
			'start_date',
			'current at',
			'to',
			date(DATE_FORMAT),
			'advanced'
			);

		$search->addSearchField(
			'end_date',
			'End Date',
			'null',
			'NULL',
			'hidden'
			);

		$search->setSearchData($search_data, $errors, 'SLCustomerPriceUplift');
		return $search;
	}

	public static function supplierItems($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = productlinesSearch::items($search_data, $errors, 'PLSupplier', $defaults);

		$search->addSearchField(
			'comp_class',
			'',
			'hidden',
			'',
			'hidden'
			);

		$cc = new ConstraintChain();
		$cc->add(new Constraint('comp_class', 'is', 'NULL'));
		$cc->add(new Constraint('comp_class', 'in', "('B', 'S')"), 'OR');
		$search->setConstraint('comp_class', $cc);
		
		$search->setSearchData($search_data, $errors, 'PLSupplierItems');
		return $search;
	}

	public static function customerItems($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = productlinesSearch::items($search_data, $errors, 'SLCustomer', $defaults);

		$search->setSearchData($search_data, $errors, 'SLCustomerItems');
		return $search;
	}

	public static function headerDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new productlinesSearch($defaults);

// Search by Start Date; default is to show rows current at today's date
		$search->addSearchField(
			'start_date/end_date',
			'current at',
			'betweenfields',
//			date(DATE_FORMAT),
			'',
			'advanced'
			);

// Search by Description
		$search->addSearchField(
			'description',
			'description begins with',
			'begins',
			'',
			'basic'
		);

// Search by Product Group
		$search->addSearchField(
			'prod_group_id',
			'Product Group',
			'select',
			0,
			'advanced'
			);
		$options = array('0'=>'All');
		$prodgroup = DataObjectFactory::Factory('STProductgroup');
		$prodgroups = $prodgroup->getAll();
		$options += $prodgroups;
		$search->setOptions('prod_group_id',$options);
		
// Search by Stock Item
		$search->addSearchField(
			'stitem',
			'Stock Item begins with',
			'begins',
			'',
			'advanced'
			);
			
// Search by Account
		$search->addSearchField(
			'glaccount_id',
			'Account',
			'select',
			0,
			'advanced'
			);
		$options = array('0'=>'All');
		$glaccount = DataObjectFactory::Factory('GLAccount');
		$accounts = $glaccount->nonControlAccounts();
		$options += $accounts;
		$search->setOptions('glaccount_id',$options);

// Search by Cost Centre
		$search->addSearchField(
			'glcentre_id',
			'Cost Centre',
			'select',
			0,
			'advanced'
			);
		$options = array('0'=>'All');
		$centre = DataObjectFactory::Factory('GLCentre');
		$centres = $centre->getAll();
		$options += $centres;
		$search->setOptions('glcentre_id',$options);

		$search->setSearchData($search_data,$errors,'headerdefaults');
		return $search;
	}
	
	public static function useDefault($search_data = null, &$errors = array(), $do, $defaults = null)
	{
		$search = new productlinesSearch($defaults);

// Search by Description
		$search->addSearchField(
			'description',
			'description begins with',
			'begins',
			'',
			'basic'
		);

// Search by Supplier/Customer
		$field = ($do=='PLSupplier')?'plmaster_id':'slmaster_id';
		$label = ($do=='PLSupplier')?'Supplier':'Customer';
		
		$search->addSearchField(
			$field,
			$label,
			'select',
			0,
			'basic'
			);
		$supplier = DataObjectFactory::Factory($do);
		$options = array('0'=>'All', 'NULL'=>'None');
		$suppliers = $supplier->getAll(null, false, true, '', '');
		$options += $suppliers;
		$search->setOptions($field,$options);

// Search by Supplier/Customer Product Code
		$field = ($do=='PLSupplier')?'supplier_product_code':'customer_product_code';
		$search->addSearchField(
			$field,
			$field,
			'contains',
			'',
			'advanced'
		);

// Search by Stock Item
		$search->addSearchField(
			'stitem',
			'Stock Item begins with',
			'begins',
			'',
			'advanced'
			);
			
// Search by Price Type
		if ($do=='SLCustomer') {
			$search->addSearchField(
				'so_price_type_id',
				'SO Price Type',
				'select',
				0,
				'basic'
				);
			$options = array('0'=>'All', 'NULL'=>'None');
			$pricetype = DataObjectFactory::Factory('SOPriceType');
			$pricetypes = $pricetype->getAll();
			$options += $pricetypes;
			$search->setOptions('so_price_type_id',$options);
		}
		
// Search by Account
		$search->addSearchField(
			'glaccount_id',
			'Account',
			'select',
			0,
			'advanced'
			);
		$options = array('0'=>'All');
		$glaccount = DataObjectFactory::Factory('GLAccount');
		$accounts = $glaccount->nonControlAccounts();
		$options += $accounts;
		$search->setOptions('glaccount_id',$options);

// Search by Cost Centre
		$search->addSearchField(
			'glcentre_id',
			'Cost Centre',
			'select',
			0,
			'advanced'
			);
		$options = array('0'=>'All');
		$centre = DataObjectFactory::Factory('GLCentre');
		$centres = $centre->getAll();
		$options += $centres;
		$search->setOptions('glcentre_id',$options);


// Search by Product Group
		$search->addSearchField(
			'prod_group_id',
			'Product Group',
			'select',
			0,
			'advanced'
			);
		$options = array('0'=>'All');
		$prodgroup = DataObjectFactory::Factory('STProductgroup');
		$prodgroups = $prodgroup->getAll();
		$options += $prodgroups;
		$search->setOptions('prod_group_id',$options);
	
		return $search;
	}
	
	public static function items($search_data = null, &$errors = array(), $do, $defaults = null)
	{
		$search = new productlinesSearch($defaults);

// Search by Stock Item
		$search->addSearchField(
			'stitem',
			'Stock Item begins with',
			'begins',
			'',
			'advanced'
			);

// Search by Product Group
		$search->addSearchField(
			'prod_group_id',
			'Product Group',
			'select',
			'',
			'advanced'
		);
		$options = array('' => 'All');
		$prod_group = DataObjectFactory::Factory('STProductgroup');
		$prod_groups = $prod_group->getAll();
		$options += $prod_groups;
		$search->setOptions('prod_group_id', $options);
		
		return $search;
	}

	public static function poheaderLines($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = productlinesSearch::useDefault($search_data, $errors, 'PLSupplier', $defaults);

		$search->removeSearchField('stitem');
		
		$search->removeSearchField('prod_group_id');
		
// Search by Start Date; default is to show rows current at today's date
		$search->addSearchField(
			'start_date/end_date',
			'current at',
			'betweenfields',
//			date(DATE_FORMAT),
			'',
			'advanced'
			);

// Search by Productline Header
		$search->addSearchField(
			'productline_header_id',
			'productline_header',
			'hidden',
			'',
			'hidden'
		);
				
		$search->setSearchData($search_data, $errors, 'SLCustomerheaderlines');
		return $search;
		
	}

	public static function soheaderLines($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = productlinesSearch::useDefault($search_data, $errors, 'SLCustomer', $defaults);

		$search->removeSearchField('stitem');
		
		$search->removeSearchField('prod_group_id');
		
// Search by Start Date; default is to show rows current at today's date
		$search->addSearchField(
			'start_date/end_date',
			'current at',
			'betweenfields',
//			date(DATE_FORMAT),
			'',
			'advanced'
			);

// Search by Productline Header
		$search->addSearchField(
			'productline_header_id',
			'productline_header',
			'hidden',
			'',
			'hidden'
		);
				
		$search->setSearchData($search_data, $errors, 'SLCustomerheaderlines');
		return $search;
		
	}

	public static function customerInvoices($search_data = null, &$errors = array(), $defaults = null)
	{
		
		$search = productlinesSearch::invoices($search_data, $errors, 'SLCustomer', $defaults);
		
		$search->setSearchData($search_data, $errors, 'SLCustomerInvoices');
		return $search;
		
	}
	
	public static function supplierInvoices($search_data = null, &$errors = array(), $defaults = null)
	{
		
		$search = productlinesSearch::invoices($search_data, $errors, 'PLSupplier', $defaults);
		
		$search->setSearchData($search_data, $errors, 'PLSupplierInvoices');
		return $search;
		
	}
	
	public static function invoices($search_data = null, &$errors = array(), $do, $defaults = null)
	{

		$search = new productlinesSearch($defaults);
		
// Search by Productline Header
		$search->addSearchField(
			'productline_header_id',
			'productline_header',
			'hidden',
			'',
			'hidden'
		);
				
// Search by Supplier/Customer
		$field = ($do=='PLSupplier')?'plmaster_id':'slmaster_id';
		$label = ($do=='PLSupplier')?'Supplier':'Customer';

		$invoice = ($do=='PLSupplier')?DataObjectFactory::Factory('PInvoice'):DataObjectFactory::Factory('SInvoice');
		
		$search->addSearchField(
			$field,
			$label,
			'select',
			0,
			'basic'
			);
		$supplier = DataObjectFactory::Factory($do);
		$options = array('0'=>'All', 'NULL'=>'None');
		$suppliers = $supplier->getAll(null, false, true, '', '');
		$options += $suppliers;
		$search->setOptions($field,$options);

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
	
		return $search;
	
	}

	public static function customerOrders($search_data = null, &$errors = array(), $defaults = null)
	{
		
		$search = productlinesSearch::orders($search_data, $errors, 'SLCustomer', $defaults);
				
		$search->setSearchData($search_data, $errors, 'SLCustomerOrders');
		return $search;
		
	}
	
	public static function supplierOrders($search_data = null, &$errors = array(), $defaults = null)
	{
		
		$search = productlinesSearch::orders($search_data, $errors, 'PLSupplier', $defaults);
		
		$search->setSearchData($search_data, $errors, 'PLSupplierOrders');
		return $search;
		
	}
	
	public static function orders($search_data = null, &$errors = array(), $do, $defaults = null)
	{

		$search = new productlinesSearch($defaults);
		
// Search by Productline Header
		$search->addSearchField(
			'productline_header_id',
			'productline_header',
			'hidden',
			'',
			'hidden'
		);
		
// Search by Supplier/Customer
		$field = ($do=='PLSupplier')?'plmaster_id':'slmaster_id';
		$label = ($do=='PLSupplier')?'Supplier':'Customer';

		$order = ($do=='PLSupplier')?DataObjectFactory::Factory('POrder'):DataObjectFactory::Factory('SOrder');
		
		$search->addSearchField(
			$field,
			$label,
			'select',
			0,
			'basic'
			);
		$supplier = DataObjectFactory::Factory($do);
		$options = array('0'=>'All', 'NULL'=>'None');
		$suppliers = $supplier->getAll(null, false, true, '', '');
		$options += $suppliers;
		$search->setOptions($field, $options);

// Search by Transaction Type
		$search->addSearchField(
			'type',
			'type',
			'select',
			'',
			'advanced'
			);
		$options = array_merge(array(''=>'All')
							  ,$order->getEnumOptions('type')
							  );
		$search->setOptions('type', $options);

// Search by Status
		$search->addSearchField(
			'status',
			'status',
			'select',
			'',
			'basic'
			);
		$options = array_merge(array(''=>'All')
					  		  ,$order->getEnumOptions('status'));
		$search->setOptions('status', $options);
	
		return $search;
	
	}

}

// End of productlinesSearch
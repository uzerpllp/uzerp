<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class pordersSearch extends BaseSearch
{

	protected $version = '$Revision: 1.21 $';
	
	protected $fields = array();

	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new pordersSearch($defaults);

// Search by Raised_By
		$search->addSearchField(
			'order',
			'order_is',
			'porder_status',
			array('Raised by me')
		);

// Search by Order Number
		$search->addSearchField(
			'order_number',
			'order_number',
			'equal',
			'',
			'basic'
		);

// Search by Order Number
		$search->addSearchField(
			'lines',
			'Show Lines',
			'show',
			'',
			'basic',
			false
		);

// Search by Customer
		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			0,
			'advanced'
			);
		$supplier = DataObjectFactory::Factory('PLSupplier');
		$options = array('0'=>'All');
		$suppliers = $supplier->getAll(null, false, true, '', '');
		$options += $suppliers;
		$search->setOptions('plmaster_id',$options);

// Search by Order Date
		$search->addSearchField(
			'order_date',
			'order_date_after',
			'after',
			'',
			'advanced'
		);

// Search by Due Date
		$search->addSearchField(
			'due_date',
			'due_date_before',
			'before',
			'',
			'advanced'
		);
			
// Search by Transaction Type
		$search->addSearchField(
			'type',
			'type',
			'select',
			'',
			'advanced'
			);
		$options = array(''=>'All'
						,'O'=>'Order'
						,'R'=>'Requisition');
		$search->setOptions('type', $options);

// Search by Status
		$search->addSearchField(
			'status',
			'status',
			'select',
			'',
			'advanced'
			);
		$porder = DataObjectFactory::Factory('POrder');
		$options = array_merge(array(''=>'All')
							  ,$porder->getEnumOptions('status')
							  );
		$search->setOptions('status', $options);
		$search->setSearchData($search_data, $errors);
		return $search;
	}
	
	public static function receivedOrders($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new pordersSearch($defaults);

// Search by Customer
		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			0
			);
		$supplier = DataObjectFactory::Factory('PLSupplier');
		$options = array('0'=>'Select Supplier');
		$suppliers = $supplier->getAll(null, false, true, '', '');
		$options += $suppliers;
		$search->setOptions('plmaster_id',$options);

// Search by Stock Item
		$search->addSearchField(
			'stitem',
			'Stock Item begins with',
			'begins'
			);

// Search by Order Number
		$search->addSearchField(
			'order_id',
			'order_number',
			'select',
			0
		);
		$orderlines = DataObjectFactory::Factory('POrder');
		$cc = new ConstraintChain();	
		$cc->add(new Constraint('status', 'in', "('R','P')"));
		$orderlines->orderby='order_number';
		$options = array('0'=>'All');
		$orderlines = $orderlines->getAll($cc);
		$options += $orderlines;
		$search->setOptions('order_id',$options);

// Restrict Search by Received Status
		$search->addSearchField(
			'status',
			'',
			'hidden',
			'',
			'hidden'
			);
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('status', 'in', "('A', 'R')"));
		$cc->add(new Constraint('invoice_id', 'is', 'NULL'));
		$search->setConstraint('status',$cc);
			
		$search->setSearchData($search_data, $errors, 'receivedOrders');
		return $search;
	}

	public static function accrual($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new pordersSearch($defaults);

// Search by Customer
		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			0,
			'advanced'
			);
		$supplier = DataObjectFactory::Factory('PLSupplier');
		$options = array('0'=>'All');
		$suppliers = $supplier->getAll(null, false, true, '', '');
		$options += $suppliers;
		$search->setOptions('plmaster_id', $options);

// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'select',
			0,
			'advanced'
			);
		$stitems = DataObjectFactory::Factory('STItem');
		$options = array('0'=>'All');
		$stitems = $stitems->getAll();
		$options += $stitems;
		$search->setOptions('stitem_id', $options);

// Search by Despatch Number
		$search->addSearchField(
			'gr_number',
			'goods_received_number',
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

// Search by Received Date
		$search->addSearchField(
			'received_date',
			'delivery_date_between',
			'between',
			'',
			'advanced'
		);

// Search by Status
		$search->addSearchField(
			'status',
			'status',
			'hidden',
			'R',
			'hidden'
			);
		
		$search->setSearchData($search_data, $errors, 'accrual');
		return $search;
	}

	public static function grn_write_off($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new pordersSearch($defaults);

// Search by Customer
		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			0,
			'advanced'
			);
		$supplier = DataObjectFactory::Factory('PLSupplier');
		$options = array('0'=>'All');
		$suppliers = $supplier->getAll(null, false, true, '', '');
		$options += $suppliers;
		$search->setOptions('plmaster_id', $options);

// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'select',
			0,
			'advanced'
			);
		$stitems = DataObjectFactory::Factory('STItem');
		$options = array('0'=>'All');
		$stitems = $stitems->getAll();
		$options += $stitems;
		$search->setOptions('stitem_id', $options);

// Search by Despatch Number
		$search->addSearchField(
			'gr_number',
			'goods_received_number',
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

// Search by Received Date
		$search->addSearchField(
			'received_date',
			'delivery_date_between',
			'between',
			'',
			'advanced'
		);

// Search by Status
		$grn = DataObjectFactory::Factory('POReceivedLine');
		
		$search->addSearchField(
			'status',
			'status',
			'hidden',
			$grn->accrualStatus(),
			'hidden'
			);

// Ignore any received/accrued lines that have been invoiced
// NB: received line status only set to invoiced when invoice is posted
		$search->addSearchField(
			'invoice_id',
			'',
			'hidden',
			'',
			'hidden'
			);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('invoice_id', 'is', 'NULL'));
		$search->setConstraint('invoice_id',$cc);
		
		$search->setSearchData($search_data, $errors, 'grn_write_off');
		return $search;
	}

}

// End of pordersSearch

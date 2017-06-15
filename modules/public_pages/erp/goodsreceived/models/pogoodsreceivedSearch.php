<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class pogoodsreceivedSearch extends BaseSearch {

	protected $version='$Revision: 1.12 $';
	
	protected $fields=array();
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new pogoodsreceivedSearch($defaults);

// Search by Customer
		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			0,
			'advanced'
			);
		$supplier = new PLSupplier();
		$options=array('0'=>'All');
		$suppliers=$supplier->getAll(null, false, true);
		$options+=$suppliers;
		$search->setOptions('plmaster_id',$options);

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

// Search by Invoice Number
		$search->addSearchField(
			'invoice_number',
			'invoice_number',
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
			'select',
			'',
			'basic'
			);
		$poreceivedline=new POReceivedLine();
		$options=array_merge(array(''=>'All')
					  		,$poreceivedline->getEnumOptions('status'));
		$search->setOptions('status',$options);
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}

	public static function confirmReceipt($search_data=null, &$errors=array(), $defaults=null) {
		$search = new pogoodsreceivedSearch($defaults);

// Search by Customer
		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			0
			);
		$supplier = new PLSupplier();
		$options=array('0'=>'Select Supplier');
		$suppliers=$supplier->getAll(null, false, true);
		$options+=$suppliers;
		$search->setOptions('plmaster_id',$options);

// Search by Stock Item
		$search->addSearchField(
			'stitem_id',
			'Stock Item',
			'select',
			0
			);
		$stitems=new STItem();
		$options=array('0'=>'All');
		$stitems=$stitems->getAll();
		$options+=$stitems;
		$search->setOptions('stitem_id',$options);

// Search by Order Number
		$search->addSearchField(
			'order_id',
			'order_number',
			'select',
			0
		);
		$orderlines = new POrder();
		$cc=new ConstraintChain();	
		$cc->add(new Constraint('status', 'in', "('A','O','P')"));
		// Only select orders. there may be requistions with status 'P'
		// where an order line has been cancelled by the owner.
		$cc->add(new Constraint('type', '=', "O"));
		$orderlines->orderby='order_number';
		$options=array('0'=>'All');
		$orderlines=$orderlines->getAll($cc);
		$options+=$orderlines;
		$search->setOptions('order_id',$options);

		$search->setSearchData($search_data,$errors,'confirmReceipt');
		// Do not save the order_id
		// - if the order is confirmed, then it will not be in the list on re-query
		if (isset($_SESSION['searches'][get_class($search)]['confirmReceipt']['order_id']))
		{
			unset($_SESSION['searches'][get_class($search)]['confirmReceipt']['order_id']);
		}
		return $search;
	}
	
}
?>
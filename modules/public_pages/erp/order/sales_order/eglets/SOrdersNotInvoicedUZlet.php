<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SOrdersNotInvoicedUZlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.4 $';
	
	protected $template = 'sorders_list.tpl';
	
	function getClassName()
	{
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		
		$orderline	= DataObjectFactory::Factory('SOrderLine');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('status', 'in', "('D', 'P')"));
		
		$order_total = $orderline->getSum('base_net_value', $cc);
		
		$orders = new SOrderLineCollection($orderline);
		
		$orders->setParams();
		
		$sh = new SearchHandler($orders, FALSE);
		
		$fields = array('order_id', 'order_number', 'customer', 'order_date', 'actual_despatch_date');
		
		$sh->setGroupBy($fields);
		$sh->setOrderBy(array('actual_despatch_date', 'order_number', 'customer'));
		
		$fields[] = 'sum(base_net_value) as base_net_value';
		$sh->setFields($fields);
		
		$sh->addConstraint($cc);
		
		$this->setSearchLimit($sh);
		
		$orders->load($sh);
		
		$orders->collection_date_label = 'actual_despatch_date';
		$orders->collection_total_label = 'Total (Base Net Value) Order Lines not Invoiced';
		$orders->collection_total = $order_total;
		
		$this->contents = $orders;
		
	}
	
}

// End of SOrdersNotInvoicedUZlet

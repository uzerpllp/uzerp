<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SOrdersOverdueEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.10 $';

	protected $template = 'sorders_list.tpl';

	function getClassName()
	{
		return 'eglet double_eglet';
	}

	function populate()
	{

		$orderline	= DataObjectFactory::Factory('SOrderLine');

		$cc = new ConstraintChain();

		$cc->add(new Constraint('status', 'in', "('N', 'S', 'R', 'P')"));
		$cc->add(new Constraint('due_despatch_date', '<', fix_date(date(DATE_FORMAT))));

		$order_total = $orderline->getSum('base_net_value', $cc);

		$orders = new SOrderLineCollection($orderline);

		$orders->setParams();

		$sh = new SearchHandler($orders, FALSE);

		$fields = array('order_id', 'order_number', 'customer', 'order_date', 'due_despatch_date');

		$sh->setGroupBy($fields);
		$sh->setOrderBy(array('due_despatch_date', 'order_number', 'customer'));

		$fields[] = 'sum(base_net_value) as base_net_value';
		$sh->setFields($fields);

		$sh->addConstraintChain($cc);

		$this->setSearchLimit($sh);

		$orders->load($sh);

		$orders->collection_date_label = 'due_despatch_date';
		$orders->collection_total_label = 'Total (Base Net Value) Order Lines Overdue';
		$orders->collection_total = $order_total;

		$this->contents = $orders;

	}

}

// End of SOrdersOverdueEGlet

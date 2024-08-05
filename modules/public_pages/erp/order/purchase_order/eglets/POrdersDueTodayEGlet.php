<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POrdersDueTodayEGlet extends SimpleListUZlet
{

	protected $version='$Revision: 1.11 $';

	protected $template = 'porderlines_list.tpl';

	function getClassName()
	{
		return 'eglet double_eglet';
	}

	function populate()
	{
		$orders = new POrderLineCollection();

		$orders->setParams();

		$sh = new SearchHandler($orders,false);

		$sh->addConstraint(new Constraint('status', '=', 'A'));
		$sh->addConstraint(new Constraint('order_status', '!=', "X"));
		$sh->addConstraint(new Constraint('due_delivery_date', '=', fix_date(date(DATE_FORMAT))));

		$this->setSearchLimit($sh);

//		$sh->setOrderBy('due_date');

		$orders->load($sh);

		$this->contents = $orders;
	}

}

// End of POrdersDueTodayEGlet

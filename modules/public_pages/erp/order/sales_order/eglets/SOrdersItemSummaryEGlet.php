<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SOrdersItemSummaryEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.11 $';
	
	protected $template = 'orderitemsummary.tpl';
	
	function getClassName()
	{
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		
		$orders = new SOrderLineCollection();
		
		if (empty($this->params['period']))
		{
			$this->params['period'] = 'today';
		}
		
		if (empty($this->params['type']))
		{
			$this->params['type'] = 'O';
		}
		
		$this->contents = $orders->getOrderItemSummary($this->params['period'], $this->params['type'], '', $this->limit);
		
		foreach($this->params as $param=>$value)
		{
			$this->params[$param] = $param."=".$value;
		}
		
		$this->contents['url'] = '/?module=sales_order&controller=sorders&action=orderitemsummary&'.implode('&',$this->params).'&_target=sorders_item_overview';

	}
	
}

// End of SOrdersItemSummaryEGlet

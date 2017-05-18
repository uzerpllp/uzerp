<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class TopSalesOrdersEGlet extends SimpleEGlet {

	protected $version='$Revision: 1.5 $';
	protected $template = 'sorders_summary.tpl';
	
	function getClassName() {
		return 'eglet double_eglet';
	}
	
	function populate() {
		$orders = new SOrderLineCollection(new SOrderLine);
		$pl = new PageList('top_sales_orders');
		$customerorders=$orders->getTopOrders();
		$this->contents=$customerorders;
	}
		
}
?>
<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class TopSalesInvoicesEGlet extends SimpleEGlet {

	protected $version='$Revision: 1.3 $';
	
	protected $template = 'sorders_summary.tpl';
	
	function getClassName() {
		return 'eglet double_eglet';
	}
	
	function populate() {
		$orders = new SInvoiceLineCollection(new SInvoiceLine);
		$pl = new PageList('top_sales_orders');
		$customersales=$orders->getTopSales(10);
		$this->contents=$customersales;
	}
		
}
?>
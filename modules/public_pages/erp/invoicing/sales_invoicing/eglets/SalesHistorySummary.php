<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SalesHistorySummary extends SimpleEGlet {

	protected $version='$Revision: 1.1 $';
	
	protected $template = 'sales_history_overview.tpl';
	
	function getClassName() {
		return 'eglet double_eglet';
	}
	
	function populate() {
		$sinvoices = new SInvoiceCollection(new SInvoice);
		$customersales=$sinvoices->getSalesHistory();
		$this->contents=$customersales;
	}
	
}
?>
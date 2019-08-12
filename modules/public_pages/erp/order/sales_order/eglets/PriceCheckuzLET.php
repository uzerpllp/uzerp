<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class PriceCheckuzLET extends SimpleEGlet {
	
	protected $version='$Revision: 1.1 $';
	
	protected $template = 'sales_price_check.tpl';

	function getClassName() {
		return 'eglet double_eglet';
	}
	
	function populate() {
//		$orderline=new SOrderLine();
		$this->contents = [];
		$this->contents['orderline']=new SOproductline();
	}
	
}

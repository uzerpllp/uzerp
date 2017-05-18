<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MultiBinBalancesPrintEGlet extends SimpleEGlet {
	protected $template = 'multi_bin_balances_print.tpl';

	function getClassName() {
		return 'eglet double_eglet';
	}
	
	function populate() {
		$store=new WHStore();
		$this->contents['whstore']=$store->getAll();
	}
	
}

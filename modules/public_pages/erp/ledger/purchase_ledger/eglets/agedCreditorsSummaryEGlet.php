<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class agedCreditorsSummaryEGlet extends SimpleEGlet {

	protected $version='$Revision: 1.5 $';
	protected $template = 'agedBalanceSummary.tpl';
	
	function populate() {
		$trans = new PLTransactionCollection(new PLTransaction);
		$pl = new PageList('aged_creditors_summary');
		$this->contents=$trans->agedSummary();
	}

}
?>
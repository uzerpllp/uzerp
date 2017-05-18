<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class agedDebtorsSummaryEGlet extends SimpleEGlet {

	protected $version='$Revision: 1.5 $';
	protected $template = 'agedBalanceSummary.tpl';
	
	function populate() {
		$trans = new SLTransactionCollection(new SLTransaction);
		$pl = new PageList('aged_debtors_summary');
		$this->contents=$trans->agedSummary();
	}

}
?>
<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ClientTicketQuickEntryEGlet extends SimpleEGlet {
	protected $template = 'client_ticket_quick_entry.tpl';
	
	function getClassName() {
		return 'eglet double_eglet';
	}
	
	function populate() {
		$db = DB::Instance();
		$query = "SELECT id, name FROM ticket_severities WHERE usercompanyid='" . EGS_COMPANY_ID . "' ORDER BY index";
		$results = $db->GetAssoc($query);
		if (count($results) >= 1) {
			$this->contents['severities']=$results;
		}
		$query = "SELECT id, name FROM ticket_categories WHERE usercompanyid='" . EGS_COMPANY_ID . "'";
		$results = $db->GetAssoc($query);
		if (count($results) >= 1) {
			$this->contents['categories']=$results;
		}
	}
}
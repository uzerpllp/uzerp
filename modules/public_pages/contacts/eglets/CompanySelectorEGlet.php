<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CompanySelectorEGLet extends SimpleEGlet {
	protected $template='company_selector.tpl';
	function populate() {
		$db = DB::Instance();
		$query = "SELECT usercompanyid, company FROM user_company_accessoverview WHERE username='{$_SESSION['username']}' ORDER BY company";
		$results = $db->GetAssoc($query);
		if (count($results) > 1) {
			$this->contents=$results;
		}
		else {
			$flash=Flash::Instance();
			$flash->addError('CompanySelectorEGLet has no options');
			$this->should_render=false;
		}
	}

	
}
?>

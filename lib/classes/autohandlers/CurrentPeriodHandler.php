<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CurrentPeriodHandler extends AutoHandler {

	function handle(DataObject $model) {
		$db = DB::Instance();
		$query = "SELECT id FROM glperiods WHERE enddate > now() ORDER BY enddate DESC LIMIT 1";
		$return = $db->GetOne($query);
		
		return intval($return);
	}
}
?>

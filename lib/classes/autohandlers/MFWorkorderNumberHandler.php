<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFWorkorderNumberHandler extends AutoHandler {

	function handle(DataObject $model) {
		$db=DB::Instance();
		$query='SELECT max(wo_number) FROM '.$model->getTableName()
		.' WHERE usercompanyid='.EGS_COMPANY_ID;
		$current=$db->GetOne($query);
		return $current+1;

	}
}
?>

<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintNumberHandler extends AutoHandler {

	function handle(DataObject $model) {
		$db=DB::Instance();
		$query='SELECT max(complaint_number) FROM '.$model->getTableName()
		.' WHERE usercompanyid='.EGS_COMPANY_ID
		.' AND "type"='.$db->qstr($model->type);
		$current=$db->GetOne($query);
		return $current+1;

	}
}
?>

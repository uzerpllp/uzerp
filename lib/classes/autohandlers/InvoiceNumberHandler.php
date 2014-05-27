<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class InvoiceNumberHandler extends AutoHandler {

	function handle(DataObject $model) {
	$jn=$model->job_no;
	if(empty($jn)) {
		$db=DB::Instance();
		$query='SELECT max(invoice_number) FROM '.$model->getTableName().' WHERE usercompanyid='.EGS_COMPANY_ID;
		$current=$db->GetOne($query);
		return $current+1;
	}
	}
}
?>

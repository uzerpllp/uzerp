<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class JobNumberHandler extends AutoHandler {

	function handle(DataObject $model) {
	$jn=$model->job_no;
	if(empty($jn)) {
		$db=DB::Instance();
		$query='SELECT max(job_no) FROM projects WHERE usercompanyid='.EGS_COMPANY_ID;
		$current=$db->GetOne($query);
		return $current+1;
	}
	}
}
?>

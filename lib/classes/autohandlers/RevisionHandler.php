<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/*
 * Created on 20-Sep-06
 *
 */
 class RevisionHandler extends AutoHandler {

	function handle(DataObject $model) {
		$db=DB::Instance();
		$id=$db->GenID('webpage_revisions_revision_seq');
		return $id;
	}
}
 
?>

<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class IDGenHandler extends AutoHandler {

	protected $version = '$Revision: 1.3 $';
	
	function handle(DataObject $model)
	{
		
		$db	= DB::Instance();
		$id	= $db->GenID($model->getTableName() . '_id_seq');
		
		return $id;
		
	}
	
}

// end of IDGenHandler.php
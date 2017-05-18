<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class PositionHandler extends AutoHandler {

	private $position_field;
	
	function __construct($position_field='position') {
		$this->position_field=$position_field;
	}
	
	function handle(DataObject $model) {
		$db=&DB::Instance();
		$query = 'SELECT max('.$this->position_field.') FROM '.$model->getTableName();
		if ($model->isField('usercompanyid')) {
			$query .= ' WHERE usercompanyid='.$db->qstr(EGS_COMPANY_ID);
		}
		$position = $db->GetOne($query);
		$position++;
		return $position;
	}

}
?>
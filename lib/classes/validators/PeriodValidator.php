<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PeriodValidator implements FieldValidation {

	function test(DataField $field, Array &$errors=array()) {
		$db = &DB::Instance();
		$value = $field->value;
		if(!strpos($value,"/") && $value !== '')
		{
			return $value;
		}
		
		if($value == '')
		{
			$query = 'select id from glperiods where enddate > now() ORDER BY enddate asc LIMIT 1';
		}
		else{
			$datevalidator = new DateValidator();
			$date = $datevalidator->test($field, $errors);
			if(!empty($errors))
			{
				return false;
			}		
			$query = 'select id from glperiods where enddate > '.$db->qstr($date).' ORDER BY enddate asc LIMIT 1';
		}


		$result = $db->getOne($query);
		return $result;
	}
}
?>

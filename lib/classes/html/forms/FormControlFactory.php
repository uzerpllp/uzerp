<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class FormControlFactory {
	
	public static function Factory($field,$model) {

		$enum = $model->getEnumOptions($field->name);
		if($model->hasParentRelationship($field->name)) {
			return new SelectControl($field);
		}
		
		if(isset($model->belongsToField[$field->name]))
		{
			return false;
		}
		if(isset($model->belongsTo[$field->name]))
		{
			return new SelectControl($field);
		}
		
		if(!empty($enum)){
			return new SelectControl($field);
		}	
		if($field->name=='id')
			return new HiddenControl($field);
		if($field->name=='usercompanyid')
			return new HiddenControl($field);

		switch($field->type) {
			case 'ignore' :
				return false;
			case 'text' :
				return new TextAreaControl($field);
			case 'bool' :
				return new CheckboxControl($field);
			case 'int8' :
				return new NumericControl($field);
			case 'date' :
			case 'timestamp' :
				return new DateControl($field);
			case 'file' :
				return new FileControl($field);
			case 'varchar' :
				if($field->name=='password')
					return new PasswordControl($field);
			default:
				return new TextControl($field);
		}
	}

}
?>

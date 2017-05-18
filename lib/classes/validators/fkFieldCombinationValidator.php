<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class fkFieldCombinationValidator implements ModelValidation{

	protected $version = '$Revision: 1.5 $';
	
	private $fields = array();
	private $fk_do;
	private $message_stub = '%s combination does not exist';
	
	/**
	 * Constructor. Takes a model name for the foreign key definition
	 * and an array of fields of form ('local Field name'=>'fk field name')
	 * 
	 * On validating the model, gets the current values for the model's fields
	 * and does a loadBy of the fk model's fields; returns false if no fk object found
	 */
	function __construct($fk_do, $fields, $message = null)
	{
		
		$this->fk_do = $fk_do;
		
		if (!is_array($fields))
		{
			$fields = array($fields => $fields);
		}
		
		$this->fields = $fields;
		
		if ($message != null)
		{
			$this->message_stub = $message;
		}
		
	}

	function test(DataObject $do, Array &$errors)
	{
		
		$db		= DB::Instance();
		$fk_do	= DataObjectFactory::Factory($this->fk_do);
		$values	= array();
		$fields	= array();
		
		foreach ($this->fields as $field => $fk_field)
		{
			if ($do->getField($field)->notnull || !empty($do->getField($field)->finalvalue))
			{
				$values[]	= $do->getField($field)->finalvalue;
				$fields[]	= $fk_field;
				$names[]	= $do->getField($field)->tag;
			}
		}
		
		if (empty($fields) || ($fk_do->loadBy($fields, $values) && $fk_do->isLoaded()))
		{
			return $do;
		}
		
		$errors[key($this->fields)] = sprintf($this->message_stub, implode(',', $names));
		
		return FALSE;
		
	}
	
}

// end of fkFieldCombinationValidator.php
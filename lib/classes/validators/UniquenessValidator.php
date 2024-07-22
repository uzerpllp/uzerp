<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class UniquenessValidator implements ModelValidation {

	protected $version = '$Revision: 1.4 $';

	private $fields			= array();
	private $ignore_nulls;
	private $message_stub	= '%s needs to be unique';
	private $message_stub2	= '%s needs to be a unique combination';

	/**
	 * Constructor. Takes a fieldname for use when testing
	 * @todo allow passing of an array of fieldnames to be tested in combination
	 */
	function __construct($fields, $message = NULL, $ignore_nulls = FALSE)
	{

		if (!is_array($fields))
		{
			$fields = array($fields);
		}

		$this->fields = $fields;

		$this->ignore_nulls = $ignore_nulls;

		if ($message != NULL)
		{
			$this->message_stub 	= $message;
			$this->message_stub2	= $message;
		}

	}

	function test(DataObject $do, Array &$errors)
	{

		$do_name	= get_class($do);
		$test_do	= new $do_name;
		$values		= array();
		$temp_value = '';

		foreach ($this->fields as $fieldname)
		{
			$temp_value .= $values[] = $do->{$fieldname};
		}

		if (!$this->ignore_nulls || !empty($temp_value))
		{
			$test_do->loadBy($this->fields, $values);
		}

		if ($test_do->isLoaded() && $test_do->getId() != $do->getId())
		{

			if (count($this->fields) == 1)
			{
				$errors[$this->fields[0]] = sprintf($this->message_stub, $do->getField($this->fields[0])->tag);
			}
			else
			{

				$fieldlist = '';

				foreach ($this->fields as $fieldname)
				{
					$fieldlist .= $do->getField($fieldname)->tag . ',';
				}

				$fieldlist = substr($fieldlist, 0, -1);
				$errors[$this->fields[0]] = sprintf($this->message_stub2, $fieldlist);

			}

			return FALSE;

		}

		return $do;

	}

}

// end of UniquenessValidator.php
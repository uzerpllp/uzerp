<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Dataset extends DataObject
{

	protected $version = '$Revision: 1.8 $';

	protected $defaultDisplayFields = array('title'
											,'description');

	function __construct($tablename = 'datasets')
	{

		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField = 'id';

		// Define relationships
		$this->hasMany('DatasetField', 'fields', 'dataset_id');

		// Define field formats

		// Define validation
		$this->validateUniquenessOf('name');
		$this->validateUniquenessOf('title');

		// set formatters, more set in load() function

		// Define enumerated types
		$this->setEnum('field_type', array('character varying'	=> 'Character'
										  ,'boolean'			=> 'Checkbox'
										  ,'date'				=> 'Date'
										  ,'datetime'			=> 'Date/Time'
										  ,'int4'				=> 'Integer'
										  ,'int8'				=> 'Large Integer'
										  ,'numeric'			=> 'Numeric'));

		// Do not allow links to the following
		$this->linkRules = array(
			'fields' => array(
				'actions'	=> array(),
				'rules'		=> array()
			)
		);
	}

	static function get_ADODB_field_type($_field_type)
	{
/*
	ADODB Field Types
	=================
	C:  Varchar, capped to 255 characters.
	X:  Larger varchar, capped to 4000 characters (to be compatible with Oracle). 
	XL: For Oracle, returns CLOB, otherwise the largest varchar size.

	C2: Multibyte varchar
	X2: Multibyte varchar (largest size)

	B:  BLOB (binary large object)

	D:  Date (some databases do not support this, and we return a datetime type)
	T:  Datetime or Timestamp accurate to the second.
	TS: Datetime or Timestamp supporting Sub-second accuracy.
		Supported by Oracle, PostgreSQL and SQL Server currently. 
		Otherwise equivalent to T.

	L:  Integer field suitable for storing booleans (0 or 1)
	I:  Integer (mapped to I4)
	I1: 1-byte integer
	I2: 2-byte integer
	I4: 4-byte integer
	I8: 8-byte integer
	F:  Floating point number
	N:  Numeric or decimal number
*/

		$config	= Config::Instance($_field_type);

		$field_type = get_config('DB_TYPE').'_ADODB_field_type';

		return self::$field_type($_field_type);

	}

	private function pgsql_ADODB_field_type($_field_type)
	{
		return self::postgres_ADODB_field_type($_field_type);
	}

	private function postgres_ADODB_field_type($_field_type)
	{
		$field_types = array('character varying'=> 'C'
							,'text' 			=> 'X'
							,'numeric'			=> 'N'
							,'date'				=> 'D'
							,'datetime'			=> 'T'
							,'int4'				=> 'I4'
							,'int8'				=> 'I8');

		return $field_types[$_field_type] ?? '';
	}

	static function get_fk_field_type ($_field_type)
	{
		$config	= Config::Instance();

		$field_type = get_config('DB_TYPE').'_fk_field_type';

		return self::$field_type($_field_type);
	}

	private function pgsql_fk_field_type($_field_type)
	{
		return self::postgres_fk_field_type($_field_type);
	}

	private function postgres_fk_field_type($_field_type)
	{
		return $this->postgres_ADODB_field_type($_field_type);
	}

}

// End of Dataset

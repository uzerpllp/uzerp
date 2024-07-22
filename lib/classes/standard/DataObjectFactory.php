<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataObjectFactory
{

	protected $version='$Revision: 1.2 $';

	/**
	 * Factory
	 * 
	 * If the specified DataObject class is not already registered,
	 * - instantiates the class and registers it in the classes array
	 * 
	 * If an id is specified, and the required DataObject instance for that id is not registered,
	 * - loads the object and registers it in the classes array
	 * 
	 * Returns the specified DataObject
	 * 
	 * @param	string				$_class_name - a DataObject class name
	 * @param	string (optional)	$_id - primary key value to load the object
	 * @return	DataObject			an instance of the requested DataObject class
	 */
	public static function Factory($_class_name, $_tablename = '')
	{

		static $classes;

		$_tablename = (empty($_tablename)?'NONE':$_tablename);

		$_class_name = strtolower($_class_name);

		if (!isset($classes[$_class_name][$_tablename]))
		{
			if ($_tablename == 'NONE')
			{
				$data_object = new $_class_name();
			}
			else
			{
				$data_object = new $_class_name($_tablename);
			}

			if (!($data_object instanceOf DataObject))
			{
				return $data_object;
			}

			$_class_name = strtolower(get_class($data_object));

			$classes[$_class_name][$_tablename] = $data_object;
		}	

		$data_object = clone $classes[$_class_name][$_tablename];

		return $data_object;

	}

}

// End of DataObjectFactory

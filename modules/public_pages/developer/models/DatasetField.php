<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DatasetField extends DataObject
{

//	protected $defaultDisplayFields = array();

	protected $version = '$Revision: 1.1 $';

	function __construct($tablename = 'dataset_fields')
	{
		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField = 'id';

		$this->orderby = 'position';

		// Define relationships
		$this->belongsTo('ModuleComponent', 'module_component_id', 'fk_link');

		// Define field formats

		// Define validation
		$this->validateUniquenessOf(array('dataset_id', 'name'));
		$this->validateUniquenessOf(array('dataset_id', 'title'));

		// set formatters, more set in load() function

		// Define enumerated types

		// Do not allow links to the following
		$this->linkRules = array();

	}

}

// End of DatasetField

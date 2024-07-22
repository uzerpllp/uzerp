<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Auditlines extends DataObject {

	protected $version='$Revision: 1.5 $';

	function __construct($tablename='audit_lines')
	{

		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField='id';

		// Define relationships

		// Define field formats

		// Define validation

		// Define enumerated types

		// Define default values

		// Define link rules for related items

	}

}

// End of Auditlines

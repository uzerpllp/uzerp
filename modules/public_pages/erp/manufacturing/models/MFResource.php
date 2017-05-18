<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFResource extends DataObject {

	protected $version='$Revision: 1.4 $';
	
	function __construct($tablename='mf_resources') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField='id';
		$this->orderby='resource_code';

		$this->identifierField='resource_code || \' - \' ||description';
		$this->validateUniquenessOf('resource_code'); 
		
// Define relationships
		
// Define field formats
		$this->getField('resource_rate')->addValidator(new NumericValidator());
		$this->getField('resource_rate')->addValidator(new MinimumValueValidator(0));
		
// Define enumerated types

	}

}
?>
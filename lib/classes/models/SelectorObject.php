<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SelectorObject extends DataObject {

	protected $version='$Revision: 1.3 $';
	
	function __construct($tablename = null) {
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->idField='id';
		
		$this->orderby='name';
		$this->identifierField='name';

		$this->setParent();
		
		// Define relationships
		$this->hasParentRelationship('parent_id');
		
		// Define field formats
		
		// set formatters
		
		// set validators
		
		// Define enumerated types
		
		// set defaults
 		
		// Set link rules for 'belongs to' to appear in related view controller sidebar
		
	}

}
?>

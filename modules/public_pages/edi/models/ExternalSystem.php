<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ExternalSystem extends DataObject {

	protected $version='$Revision: 1.5 $';
	protected $linkRules;
	
	function __construct($tablename='external_systems') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField='id';
		$this->identifierField='name';

// Define relationships
		$this->hasMany('DataDefinition', 'data_definitions', 'external_system_id');
		$this->hasMany('DataMappingRule', 'mapping_rules', 'external_system_id');
		$this->hasMany('EDITransactionLog', 'edi_log', 'external_system_id');
		
// Define enumerated types

// Define system defaults
		
// Define field formats		
	
// Define View Related Link Rules		
		$this->linkRules=array('edi_log'=>array('actions'=>array('link')
											 ,'rules'=>array()));
	}

}
?>
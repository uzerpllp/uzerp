<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataDefinitionDetail extends DataObject {

	protected $version='$Revision: 1.8 $';
	protected $defaultDisplayFields = array('element'
											,'position'
											,'data_definition'
											,'parent'
											,'map_to_type'
											,'map_to_attribute'
											,'mapping_rule'
											,'data_mapping_rule_id'
											,'data_mapping_id');
	
	function __construct($tablename='data_definition_details') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField='id';
		$this->identifierField='element';
		$this->orderBy='position';

// Define relationships
		$this->belongsTo('DataDefinition', 'data_definition_id', 'data_definition');
		$this->belongsTo('DataDefinitionDetail', 'parent_id', 'parent');
		$this->actsAsTree('parent_id');
		$this->belongsTo('DataMapping', 'data_mapping_id', 'data_mapping');
		$this->hasOne('DataMapping', 'data_mapping_id', 'data_map');
		$this->belongsTo('DataMappingRule', 'data_mapping_rule_id', 'data_mapping_rule');
		
		$this->hasMany('DataDefinitionDetail', 'sub_definition', 'parent_id');
		
// Define enumerated types

// Define system defaults
		
// Define field formats		
		
	}

	function getAllByDef ($_data_definition_id, $_parent_id) {

//		if ($this->isLoaded()) {
			$cc=new ConstraintChain();
			$cc->add(new Constraint('data_definition_id', '=', $_data_definition_id));
			$cc->add(new Constraint('parent_id', '=', $_parent_id));
			$ddd=new DataDefinitionDetail();
			return $ddd->getAll($cc, true);
//		} else {
//			return array();
//		}

	}
	
}
?>
<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataMappingRule extends DataObject {

	protected $version='$Revision: 1.10 $';
	protected $defaultDisplayFields = array('name'
											,'external_system'
											,'data_mappings'
											,'parent_rule');
	
	protected $linkRules;
	
	function __construct($tablename='data_mapping_rules') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField='id';
		
// Define relationships
		$this->belongsTo('ExternalSystem','external_system_id','external_system');
		$this->belongsTo('DataMapping','data_mapping_id','data_mappings');
		$this->belongsTo('DataMappingRule', 'parent_id', 'parent_rule');

		$this->hasOne('DataMapping','data_mapping_id','data_map');
		
		$this->hasMany('DataDefinitionDetail', 'where_used', 'data_mapping_rule_id');
		$this->hasMany('DataMappingDetail', 'data_translations', 'data_mapping_rule_id');
		
		$this->actsAsTree('parent_id');
		$this->setParent();
		
// Define enumerated types

// Define system defaults
		
// Define field formats		
		
// Define View Related Link Rules		
		$this->linkRules=array('where_used'=>array('actions'=>array('link')
											 ,'rules'=>array()));
	}

	public function addLinkRule($_rule=array()) {
		$this->linkRules=array_merge_recursive($this->linkRules, $_rule);
	}

	public function validate ($_value, &$errors=array()) {
		switch ($this->data_type) {
			case 'date' :
				$date=fix_date($_value, $this->external_format, $errors);
				if ($date===false) {
					return $_value;
				}
				return un_fix_date($date);
				break;
		}
	}
	
}
?>
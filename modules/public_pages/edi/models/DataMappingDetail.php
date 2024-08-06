<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataMappingDetail extends DataObject {

	protected $version='$Revision: 1.8 $';
	protected $defaultDisplayFields = array('internal_code'
											,'external_code'
											,'data_mapping'
											,'data_mapping_rule_id'
											,'parent_id');
	
	function __construct($tablename='data_mapping_details') {
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->identifierField='external_code';

// Define relationships
//		$this->belongsTo('DataMapping', 'data_mapping_id', 'data_mapping');
//		$this->hasOne('DataMapping', 'data_mapping_id', 'data_map');
		$this->hasOne('DataMappingRule', 'data_mapping_rule_id', 'data_map_rule');
		$this->belongsTo('DataMappingDetail', 'parent_id', 'parent');

		$this->hasOne('DataMappingDetail', 'parent_id', 'parent_detail');

		$this->actsAsTree('parent_id');

// Define enumerated types

// Define system defaults

// Define field formats		
		
	}

	function getObject ($_identity, $_external_type, $_external_code) {
		$this->loadBy(array('identity', 'external_type', 'external_code')
					,array($_identity, $_external_type, $_external_code));
		$object=new $this->internal_type;
		if (is_object($object)) {
			$object->load($this->internal_code);
			if ($object->isLoaded()) {
				return $object;
			}
		}
		
		return false;
	}

	function displayValue () {
		
		$data_map=$this->data_map_rule->data_map;
		if (!$data_map->isLoaded()) {
			return '';
		}
		$model=$data_map->getModel();
		
		$attribute=$this->internal_code;
		
		if ($model->idField==$data_map->internal_attribute) {
			$model->load($attribute);
			if ($model->isLoaded()) {
				return $model->getIdentifierValue();
			}
		}
// Need to document what this is doing
// There must be a better way!		
		$hasmany=$model->getHasMany();
		if (isset($hasmany[$data_map->internal_attribute])) {
			$lookup=new $hasmany[$data_map->internal_attribute]['do'];
			$lookup->load($attribute);
			return $lookup->getIdentifierValue();
		}
		if ($data_map->isHasOne()) {
			$hasone=$data_map->getHasOne();
			$lookup=new $hasone['model'];
			if (is_null($hasone['fkfield'])) {
				$lookup->load($attribute);
			} else {
				$lookup->loadBy($hasone['fkfield'], $attribute);
			}
			if ($lookup->isLoaded()) {
				return $lookup->getIdentifierValue();
			}
		}
		
		if ($data_map->isLookupModel($model)) {
			$lookup=$data_map->getLookupModel($model);
			$lookup->load($attribute);
			if ($lookup->isLoaded()) {
				return $lookup->getIdentifierValue();
			}
		}
		
		return $this->internal_code;
	}
	
	function translateCode ($_rule_id, $_value, $_direction='IN') {
		if ($_direction=='IN') {
			$in_attribute='external_code';
			$out_attribute='internal_code';
		} else {
			$in_attribute='internal_code';
			$out_attribute='external_code';
		}
		$this->loadBy(array('data_mapping_rule_id', $in_attribute)
					 ,array($_rule_id, $_value));
					
		if ($this->isLoaded()) {
			return $this->$out_attribute;
		}
		
		return $_value;
	}
	
}
?>
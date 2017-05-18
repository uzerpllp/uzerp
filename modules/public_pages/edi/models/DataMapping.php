<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataMapping extends DataObject {

	protected $version='$Revision: 1.8 $';
	protected $defaultDisplayFields = array('name'
											,'internal_type'
											,'internal_attribute'
											,'parent_type'
											,'parent_attribute');
	
	function __construct($tablename='data_mappings') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField='id';
		$this->identifierField='name';
		$this->orderby=array('name');
		
// Define relationships
		$this->hasMany('DataDefinitionDetail', 'data_definition_details', 'data_mapping_id');
		$this->hasMany('DataMappingRule', 'data_mapping_rules', 'data_mapping_id');
		$this->belongsTo('DataMapping', 'parent_id', 'parent');
		$this->actsAsTree('parent_id');
		
// Define enumerated types

// Define system defaults
		
// Define field formats		
		
	}

	public function getModel () {
		$model=new $this->internal_type;
		if (!is_object($model)) {
			return false;
		} else {
			return $model;
		}
	}
	
	public function getDataOptions ($model='', $cc='') {
		
		if (empty($model)) {
			$model=$this->getModel();
			if (!$model) {
				return false;
			}
		}
		
		$attribute=$this->internal_attribute;
		if ($model->idField==$attribute) {
			return $model->getAll($cc);
		} elseif (isset($model->belongsToField[$attribute])) {
			$x = $model->belongsTo[$model->belongsToField[$attribute]]["model"];
			if (empty($cc) || !($cc instanceof ConstraintChain)) {
				$cc = new ConstraintChain();
			}
			if ($model->belongsTo[$model->belongsToField[$attribute]]["cc"] instanceof ConstraintChain) {
				$cc->add($model->belongsTo[$model->belongsToField[$attribute]]["cc"]);
			}
			$x = new $x();

			return $x->getAll($cc);
		} else {
			return array();
		}
	}

	public function getHasOne ($model='') {
		if (empty($model)) {
			$model=$this->getModel();
			if (!$model) {
				return false;
			}
		}
		
		$attribute=$this->internal_attribute;

		foreach ($model->hasOne as $hasone) {
			if ($hasone['field']==$attribute) {
				return $hasone;
			}
		}

		return false;
		
	}
	
	public function getLookupModel ($model='') {
		if (empty($model)) {
			$model=$this->getModel();
			if (!$model) {
				return false;
			}
		}
		
		$attribute=$this->internal_attribute;

		$x = $model->belongsTo[$model->belongsToField[$attribute]]["model"];
		$x = new $x();
		return $x;

	}
		
	public function getValue($value) {
// Also Check for hasOne and isLookupModel
		$x = new $this->internal_type;
		
		if ($this->internal_attribute==$x->idField) {
			$x->load($value);
		} elseif (($hasmany=$x->getHasMany()) && isset($hasmany[$this->internal_attribute])) {
			$x=new $hasmany[$this->internal_attribute]['do'];
			$x->load($value);
		} else {
			$x->loadBy($this->internal_attribute, $value);
		}
		
		if ($x->isLoaded())
		{
			return $x->getIdentifierValue();
		}
		else
		{
			return false;
		}
		
	}
	
	public function isHasOne ($model='') {
		if (empty($model)) {
			$model=$this->getModel();
			if (!$model) {
				return false;
			}
		}
		
		$attribute=$this->internal_attribute;

		foreach ($model->hasOne as $hasone) {
			if ($hasone['field']==$attribute) {
				return true;
			}
		}
		return false;
	}
	
	public function isLookupModel ($model='') {
		if (empty($model)) {
			$model=$this->getModel();
			if (!$model) {
				return false;
			}
		}
		
		$attribute=$this->internal_attribute;

		if (isset($model->belongsToField[$attribute])) {
			return true;
		} else {
			return false;
		}
	}

}
?>
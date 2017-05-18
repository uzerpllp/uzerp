<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ObjectRoleCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='ObjectRole', $tablename='accesspermissions') {
		parent::__construct($do, $tablename);

		
	}

	function getAllowedIds ($object_type, $accesstype) {
		$this->getRows('', $object_type, $accesstype);
		$object_ids=array(-1);
		foreach ($this as $object) {
			$object_ids[$object->object_id]=$object->object_id;
		}
		return $object_ids;
	}

	function getInIds ($object_type, $accesstype) {
		$this->getRows('' ,$object_type, $accesstype);
		$object_ids=array(-1);
		foreach ($this as $object) {
			$object_ids[$object->object_id]=$object->object_id;
		}
		return '('.implode(',', $object_ids).')';
	}

	public function getRows($object_id='', $object_type, $accesstype='') {
		$sh=new SearchHandler($this, false);
		if (!empty($object_id)) {
			$sh->addConstraint(new Constraint('object_id','=',$object_id));
		}
		$sh->addConstraint(new Constraint('object_type','=',$object_type));
		$sh->addConstraint(new Constraint('username','=',EGS_USERNAME));
		if (!empty($accesstype)) {
			$sh->addConstraint(new Constraint('"'.$accesstype.'"','is',true));
		}
		$this->load($sh);
	}

}
?>

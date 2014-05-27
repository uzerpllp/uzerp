<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ObjectRole extends DataObject {

	function __construct($tablename='objectroles') {
		parent::__construct($tablename);
//		$this->idField='companyid || roleid || username AS id';

 		$this->belongsTo('Role', 'roleid', 'roles_roleid');
// 		$this->belongsTo('Company', 'companyid', 'company'); 
	}

	function getRoleID($object_id, $object_type, $accesstype) {
		$cc = new ConstraintChain();
		$cc->add(new Constraint('object_id','=',$object_id));
		$cc->add(new Constraint('object_type','=',$object_type));
		$cc->add(new Constraint('"'.$accesstype.'"','is',true));

		$this->idField='role_id';
		$this->identifierField='role_id';
		
		return $this->getAll($cc);

	}

	function getIds($object_id, $object_type, $accesstype='') {
		$cc = new ConstraintChain();
		$cc->add(new Constraint('object_id','=',$object_id));
		$cc->add(new Constraint('object_type','=',$object_type));
		if (!empty($accesstype)) {
			$cc->add(new Constraint('"'.$accesstype.'"','is',true));
		}
		$this->idField='id';
		$this->identifierField='id';
		
		return $this->getAll($cc);

	}

	function deleteAll($ids) {
		if (is_array($ids) && count($ids)>0) {
			foreach ($ids as $id) {
				$this->delete($id);	
			}
		}
	}
	
}
?>

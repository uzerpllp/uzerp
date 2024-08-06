<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class HasReport extends DataObject {

	protected $version='$Revision: 1.2 $';

	function __construct($tablename='hasreport') {

		parent::__construct($tablename);
		$this->idField='id';

		$this->identifierField='report_id';

 		$this->belongsTo('Permission', 'permissions_id', 'permission'); 
		$this->belongsTo('Report', 'report_id', 'report');
		$this->belongsTo('Role', 'role_id', 'role');

	}

	public function getByPermission () {

		$system=system::Instance();

		if (!empty($system->pid))
		{
			return $this->getByRoles($system->pid);
		}

		return array();

	}

	public function getByRoles ($_permissions_id='') {

		$this->idField='report_id';
		$this->identifierField='description';
		$cc=new ConstraintChain();
		if (!empty($_permissions_id))
		{
			$cc->add(new Constraint('permissions_id', '=', $_permissions_id));
		}

		$ao=AccessObject::Instance();
		$cc->add(new Constraint('role_id', 'in', '('.implode(',', $ao->roles).')'));

		return $this->getAll($cc, true, true);

	}

	public function getAssignedRoles ($_report_id='', $_permissions_id='') {

		$this->idField='role_id';
		$this->identifierField='role';
		$cc=new ConstraintChain();

		if (!empty($_report_id))
		{
			$cc->add(new Constraint('report_id', '=', $_report_id));
		}

		if (!empty($_permissions_id))
		{
			$cc->add(new Constraint('permissions_id', '=', $_permissions_id));
		}

		return $this->getAll($cc, true, true);

	}


}

// End of HasReport

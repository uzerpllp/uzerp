<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PermissionCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.15 $';

	public $field;

	function __construct($do = 'Permission', $tablename = 'permissions')
	{
		parent::__construct($do, $tablename);		
		$this->orderby = 'position';
	}

	function getPermissions($permissions = null)
	{

		$sh = new SearchHandler($this, false);

		if (!empty($permissions))
		{

			if (is_array($permissions))
			{
				$sh->addConstraint(new Constraint('id', 'in', '(' . implode(',', $permissions) . ')'));
			}
			else
			{
				$sh->addConstraint(new Constraint('id', '=', $permissions));
			}

		}

		return $this->load($sh, null, RETURN_ROWS);

	}

	function getPermissionTree($permissions = array(), $parent = null)
	{

		$nextlevel	= new PermissionCollection();
		$sh			= new SearchHandler($nextlevel, false);

		if (!empty($permissions))
		{
			$sh->addConstraint(new Constraint('id', 'in' , '(' . implode(',', $permissions) . ')'));
		}

		if (empty($parent))
		{
			$sh->addConstraint(new Constraint('parent_id', 'is', 'NULL'));
		}
		else
		{
			$sh->addConstraint(new Constraint('parent_id', '=', $parent));
		}

		$sh->setOrderby('position');
		$rows = $nextlevel->load($sh, null, RETURN_ROWS);

		$tree=array();

		if (!empty($rows))
		{
			foreach ($rows as $permission) {
				$tree[$permission['id']]=$permission;
				$tree[$permission['id']]['children']=$this->getPermissionTree($permissions, $permission['id']);
			}
		}

		return $tree;

	}

	function checkPermission($permissions, $types, $parent_ids = '')
	{

		$cc = new ConstraintChain();

		if (is_array($permissions))
		{
			$permissions = implode("','", $permissions);
		}

		$cc->add(new Constraint('permission', 'in', "('" . $permissions . "')"));

		if (is_array($types))
		{
			$types = implode("','", $types);
		}

		$cc->add(new Constraint('type', 'in', "('" . $types . "')"));

		if (!empty($parent_ids))
		{

			if (is_array($parent_ids))
			{
				$parent_ids = implode(',', $parent_ids);
			}

			$cc->add(new Constraint('parent_id', 'in', '(' . $parent_ids . ')'));

		}

		$sh = new SearchHandler($this, false);
		$sh->addConstraintChain($cc);

		return $this->load($sh, null, RETURN_ROWS);

	}

}

// end of PermissionCollection.php
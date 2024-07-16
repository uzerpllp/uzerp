<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SystemPolicyControlListCollection extends DataObjectCollection
{
	
	protected $version='$Revision: 1.2 $';
	
	public $field;
		
	function __construct($do='SystemPolicyControlList', $tablename = 'sys_policy_control_lists_overview')
	{
		
		parent::__construct($do, $tablename);
			
	}

	static function getPermissions ($module_component = '')
	{

		if (empty($module_component))
		{
			return array();
		}
		
		$system = system::Instance();
		
		$module_component = strtolower($module_component);
		
		$permissions = new SystemPolicyControlListCollection();
		
		$permissions->setViewName('sys_object_access_control_list');
		
		$sh = new SearchHandler($permissions, false);
		
		$db = DB::Instance();
		
		$sh->addConstraint(new Constraint('module_component', '=', $db->qstr($module_component)));
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('module', '=', $db->qstr($system->controller->module)));
		
		$system = system::Instance();
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('access_type', '=', 'Role'));
		
		$roles = $system->access->roles;
		
		if (count($roles) > 0)
		{
			$cc->add(new Constraint('access_object_id', 'IN', '('.implode(',', $roles).')'), 'AND');
		}
		
		$sh->addConstraint($cc);
		
		return $permissions->load($sh, null, RETURN_ROWS);
		
	}
	
	static function getPolicies($module_component = '', $username = '')
	{
		
		if (empty($module_component))
		{
			return array();
		}
		
		$module_component = strtolower($module_component);
		
		$permissions = new SystemPolicyControlListCollection();
		
		$permissions->setViewName('sys_object_access_control_list');
		
		if (!defined('EGS_COMPANY_ID'))
		{
			$sh = new SearchHandler($permissions, false, FALSE);
		}
		else
		{
			$sh = new SearchHandler($permissions, false);
		}
		
		$sh->addConstraint(new Constraint('module_component', '=', $module_component));
		
		$system = system::Instance();
		
		$cc = new ConstraintChain();
		
		$cc1 = new ConstraintChain();
		
		$cc1->add(new Constraint('access_type', '=', 'Role'));
		
		$roles = $system->access->roles ?? null;
		
		if ($roles != null && count($roles) > 0)
		{
			$cc1->add(new Constraint('access_object_id', 'IN', '('.implode(',', $roles).')'), 'AND');
		}
		
		$cc->add($cc1, 'AND');
		
		$context = $system->getContext();
		
		$cc2 = new ConstraintChain();
		
		$cc2->add(new Constraint('access_type', '=', 'Permission'));
		
		if ($context != null && count($context) > 0)
		{
			foreach ($context as $permission_context)
			{
				$context_id[] = $permission_context['id'];
			}
		
			$cc2->add(new Constraint('access_object_id', 'IN', '('.implode(',', $context_id).')'), 'AND');
		}
		
		$cc->add($cc2, 'OR');
		
		$sh->addConstraint($cc);
//		echo 'SystemPolicyPermissionCollection::getPermissions constraint='.$sh->constraints->__toString().'<br>';

		return $permissions->load($sh, null, RETURN_ROWS);
		
	}
	
}

// End of SystemPolicyControlListCollection

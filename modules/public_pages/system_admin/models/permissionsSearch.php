<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class permissionsSearch extends BaseSearch {

	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new permissionsSearch($defaults);
// Search by Type
		$search->addSearchField(
			'type',
			'Type',
			'select',
			'',
			'basic'
		);
// Search by Permission
		$search->addSearchField(
			'permission',
			'Permission',
			'select',
			'',
			'basic'
		);
		$permission = new Permission;
		$types = $permission->getEnumOptions('type');
		$options = array('' => 'All');
		$options += $types;
		$search->setOptions('type', $options);
		
		$cc=new ConstraintChain();
		$cc->add(new Constraint('type', 'in', '('."'g', 'm'".')'));
		$cc->add(new Constraint('parent_id', 'is', 'NULL'));
		$options = array('' => 'All');
		$options += $permission->getAll($cc);
		$search->setOptions('permission', $options);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}
?>
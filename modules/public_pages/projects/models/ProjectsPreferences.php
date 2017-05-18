<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectsPreferences extends ModulePreferences {
	function __construct($getCurrentValues=true) {
		parent::__construct();
		
		$userPreferences = UserPreferences::instance();
		
		$this->setModuleName('projects');
		
		$roleCollection = new RoleCollection(new Role);
		$sh = new SearchHandler($roleCollection, false);
		$sh->AddConstraint(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
		$sh->setOrderby('name');
		$roleCollection->load($sh);

		$roles=array();

		foreach ($roleCollection->getContents() as $role) {
			$roles[$role->id] = array(
				'value' => $role->id,
				'label' => $role->name
			);
			
			if ($getCurrentValues) {
				if(in_array($role->id, $userPreferences->getPreferenceValue('default-read-roles', 'projects'))) {
					$roles[$role->id]['selected'] = true;
				}
			}
		}
		
		$this->registerPreference(
			array(
				'name' => 'default-read-roles',
				'display_name' => 'Default Read Access',
				'type' => 'select_multiple',
				'data' => $roles,
				'default' => array()
			)
		);

		foreach ($roleCollection->getContents() as $role) {
			$roles[$role->id] = array(
				'value' => $role->id,
				'label' => $role->name
			);
			
			if ($getCurrentValues) {
				if(in_array($role->id, $userPreferences->getPreferenceValue('default-write-roles', 'projects'))) {
					$roles[$role->id]['selected'] = true;
				}
			}
		}

		$this->registerPreference(
			array(
				'name' => 'default-write-roles',
				'display_name' => 'Default Write Access',
				'type' => 'select_multiple',
				'data' => $roles,
				'default' => array()
			)
		);
		
	}
}

?>
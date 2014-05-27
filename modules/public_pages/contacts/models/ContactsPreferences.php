<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ContactsPreferences extends ModulePreferences
{

	protected $version='$Revision: 1.6 $';
	
	function __construct($getCurrentValues=true)
	{
		parent::__construct();
		
		$userPreferences = UserPreferences::instance();
		
		$this->setModuleName('contacts');
		
		$roleCollection = new RoleCollection();
		
		$sh = new SearchHandler($roleCollection, false);
		
		$sh->AddConstraint(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
		
		$sh->setOrderby('name');
		
		$roleCollection->load($sh);

		$roles = array();

		foreach ($roleCollection->getContents() as $role)
		{
			$roles[$role->id] = array(
				'value' => $role->id,
				'label' => $role->name
			);
			
			if ($getCurrentValues)
			{
				if(in_array($role->id, $userPreferences->getPreferenceValue('default-read-roles', 'contacts')))
				{
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

		foreach ($roleCollection->getContents() as $role)
		{
			$roles[$role->id] = array(
				'value' => $role->id,
				'label' => $role->name
			);
			
			if ($getCurrentValues)
			{
				if(in_array($role->id, $userPreferences->getPreferenceValue('default-write-roles', 'contacts')))
				{
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

// End of ContactsPreferences

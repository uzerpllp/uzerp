<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class UserPreferencesCollection extends DataObjectCollection {

	protected $version='$Revision: 1.5 $';

	public $field;

	function __construct($do='UserPreferences')
	{

		parent::__construct($do);

	}

	function getPreferences ($username = EGS_USERNAME)
	{

		$sh=new SearchHandler($this, false);

		if (empty($username))
		{
			$username = EGS_USERNAME;
		}

		$sh->addConstraint(new Constraint('username', '=', $username));

		$this->load($sh);

		$prefs=array();

		foreach ($this as $pref)
		{
			$prefs[$pref->module]=$pref->settings;
		}

		return $prefs;

	}

}

// End of UserPreferencesCollection

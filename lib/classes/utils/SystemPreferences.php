<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class SystemPreferences
{

	protected $version = '$Revision: 1.1 $';

	protected $prefs = array();

	private function __construct()
	{

	}

	public static function &instance($_module = '')
	{

		static $instance;

		if ($instance == NULL)
		{

			$instance = new SystemPreferences;

		}

		if (!empty($_module) && empty($prefs[$_module]))
		{
			$instance->loadModule($_module);
		}

		return $instance;

	}

	protected function loadModule($_module)
	{
		$module = ModuleObject::getModule($_module);

		if ($module->isLoaded())
		{
			$this->prefs[$_module] = $module->settings;
		}

	}

	public function getModulePreferences($_module)
	{

		// if nothing in the module, try for a default
		if (!isset($this->prefs[$_module]))
		{
			$this->loadModule($_module);

			if (!isset($this->prefs[$_module]))
			{
				return array();
			}
		}

		// the preferences are encoded in the database, so decode
		$decoded = $this->decode($this->prefs[$_module]);
		return $decoded[EGS_COMPANY_ID];

	}

	public function getPreference($preferenceName)
	{
		if (isset($this->preferences[$preferenceName]))
		{
			return $this->preferences[$preferenceName];
		}
		else
		{
			return array();
		}
	}

	public function getPreferenceValue($_name, $_module = 'home')
	{

		// if nothing in the module, try for a default
		if (!isset($this->prefs[$_module]))
		{
			$this->loadModule($_module);

			if (!isset($this->prefs[$_module]))
			{
				return '';
			}
		}

		// the preferences are encoded in the database, so decode
		$decoded = $this->decode($this->prefs[$_module]);

		// fall back to default if nothing set
		if (!isset($decoded[EGS_COMPANY_ID][$_name]))
		{
			return '';
		}

		return $decoded[EGS_COMPANY_ID][$_name];

	}

	function setPreferenceValue($_name, $_module, $_value)
	{

		if (!isset($this->prefs[EGS_COMPANY_ID][$_module]))
		{
			$this->prefs[$_module] = '';
		}

		// Reload to ensure latest prefs
		$this->loadModule($_module);

		$decoded = $this->decode($this->prefs[$_module]);

		if (empty($_value) || (is_array($_value) && count($_value) == 1 && $_value[0] == 'undefined' ))
		{
			unset($decoded[EGS_COMPANY_ID][$_name]);
		}
		else
		{
			$decoded[EGS_COMPANY_ID][$_name] = $_value;
		}

		$encoded		= $this->encode($decoded);
		$db				= DB::Instance();

		$data			= array(
			'name'		=> $_module,
			'settings'	=> $encoded
		);

		// Update the preferences
		// returns 0 on fail
		//         1 for update OK
		//         2 for insert OK (but this would be an error because row must already exist)
		$status = $db->Replace('modules', $data, array('name'), TRUE);
		return !$status;

	}

	private function decode($_settings)
	{
	    return unserialize(base64_decode((string) $_settings));
	}

	private function encode($_settings)
	{
	    return base64_encode(serialize($_settings));
	}

}

// end of SystemPreferences.php

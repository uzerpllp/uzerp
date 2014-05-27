<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class UserPreferences extends DataObject {

	protected $version = '$Revision: 1.15 $';
	
	protected $username;
	protected $loggedin;
	
	protected $preferences	= array();
	protected $prefs		= array();
	
	public function __construct($tablename = 'userpreferences')
	{	
		parent::__construct($tablename);
	}
	
	public static function &instance($username = EGS_USERNAME)
	{
		
		static $instance;
		
		if ($instance == NULL)
		{
			
			$instance = DataObjectFactory::Factory('UserPreferences');
			
			if (empty($username))
			{
				$instance->loggedin = FALSE;		
			}
			else
			{
				$instance->username	= $username;
				$instance->loggedin	= TRUE;
			}
			
			$instance->initialise();
			
		}
		
		return $instance;
		
	}
	
	protected function initialise()
	{
		$prefs			= new UserPreferencesCollection($this);
		$this->prefs	= $prefs->getPreferences($this->username);
	}
		
	public function userHasPreferences()
	{
		
		if ($this->loggedin)
		{
			
			foreach ($this->prefs as $module => $prefs)
			{
				
				if (substr($module, 0, 1) !== '_' && !empty($prefs))
				{
					return TRUE;
				}
				
			}
			
		}
		
		return FALSE;
		
	}

	public function userCanSetPreferences()
	{
		
		if ($this->loggedin)
		{
			
			$accessObject	= &AccessObject::Instance($this->username);
			$modules		= $accessObject->tree;
			
			if (is_array($modules))
			{
				
				foreach ($modules as $module)
				{

					continue;
			
					// FIXME: Only show module if preferences file exists
					if (!file_exists(FILE_ROOT . '/app/controllers/' . strtolower($module['permission']) . '/Preferences.php')) {
						continue;
					}
		
					if ($accessObject->hasPermission($module['permission'])) {
						return TRUE;
					}
					
				}
				
			}
			
		}
		
		return FALSE;
		
	}

	public function getPreferenceValue($name, $module = 'home')
	{
		
		// if nothing in the module, try for a default
		if (!isset($this->prefs[$module]))
		{
			return $this->getDefault($name, $module);
		}
		
		// the preferences are serialised and base64'd in the database, so decode
		$encoded = $this->prefs[$module];
		$decoded = unserialize(base64_decode($encoded));
		
		// fall back to default if nothing set
		if (!isset($decoded[$name]))
		{
			return $this->getDefault($name, $module);
		}
		
		return $decoded[$name];
		
	}

	function getDefault($name, $module)
	{

		$classname = ucwords($module) . 'Preferences';
		
		if (class_exists($classname))
		{
			$preferences = new $classname(FALSE);
			return $preferences->getPreferenceDefault($name);
		}
		else
		{
			return NULL;
		}
		
	}

	function setPreferenceValue($name, $module, $value)
	{
		
		if (!isset($this->prefs[$module]))
		{
			$this->prefs[$module] = '';
		}
		
		$encoded		= $this->prefs[$module];
		$decoded		= unserialize(base64_decode($encoded));
		
		if (empty($value) || (is_array($value) && count($value) == 1 && $value[0] == 'undefined' ))
		{
			unset($decoded[$name]);
		}
		else
		{
			$decoded[$name]	= $value;
		}
		
		$encoded		= base64_encode(serialize($decoded));
		$db				= DB::Instance();
		
		$data			= array(
			'username'		=> $this->username,
			'module'		=> $module,
			'usercompanyid'	=> EGS_COMPANY_ID,
			'settings'		=> $encoded
		);
		
		$db->Replace('userpreferences', $data, array('username', 'module', 'usercompanyid'), TRUE);
		
		$this->initialise();
		
	}
	
	function getDashboardContents($username = EGS_USERNAME, $dashboard_module = '', $pid = '')
	{
		
		// Get list of modules the user has access to
		$ao	= &AccessObject::Instance($username);
		
		$usermodules = $ao->getUserModules($username);
		
		$modules = array();
		$contents = array();
		
		if (!empty($usermodules))
		{
			$db = DB::Instance();
			
			if ($dashboard_module != 'dashboard' && !empty($pid))
			{
				$parent = $pid;
			}
			
			foreach ($usermodules as $module_permission)
			{
				
				// Get user's selected uzlets for the current module
				$contents[$module_permission['permission']] = $this->getPreferenceValue('dashboard_contents', $module_permission['permission']);
		
				if (empty($parent) || $parent == $module_permission['permissionsid'] || $parent == $module_permission['parent_id'])
				{
					$modules[$module_permission['permissionsid']] = $db->qstr($module_permission['permission']);
				}
			}
		}
		
		// now load the uzlets that are available to the user
		// for this module or modules they have access to 
		$uzlets	= new UzletCollection();
		$sh		= new SearchHandler($uzlets, FALSE);
		
		if (empty($modules))
		{
			$sh->addConstraint(new Constraint('module', '=', $dashboard_module));
			$check_modules = false;
		}
		else
		{
			$sh->addConstraint(new Constraint('module', 'in', '(' . implode(',', $modules) . ')'));
			$check_modules = true;
		}
		
		if ($dashboard_module == 'dashboard')
		{
			$sh->addConstraint(new Constraint('dashboard', 'is', TRUE));
			$check_modules = true;
		}
		
		$sh->addConstraint(new Constraint('enabled', 'is', TRUE));
		$sh->setOrderby(array('module', 'title'));
		$rows = $uzlets->load($sh, null, RETURN_ROWS);
		
		// Now construct uzlet list for display
		$available	= array();
		$selected	= array();
		
		if (count($rows) > 0)
		{
			foreach ($rows as $uzlet)
			{
				
				if (is_array($contents) && !empty($contents[$uzlet['module']]) && in_array($uzlet['name'], $contents[$uzlet['module']]))
				{
					//if the user has picked the EGlet previously, then it belongs in 'selected' (setting the index preserves the ordering)
					$selected[$uzlet['module']][array_search($uzlet['name'], $contents[$uzlet['module']])] = array('title' => prettify($uzlet['title']), 'name' => $uzlet['name']);
				}      
				elseif (empty($contents[$uzlet['module']]) && $uzlet['preset'] == 't')
				{
					//if they haven't picked any EGlets, and the EGlet is marked as default for the current module then it's 'selected'   
					$selected[$uzlet['module']][] = array('title' => prettify($uzlet['title']), 'name' => $uzlet['name']);
				}
				else
				{
					$available[$uzlet['module']][$uzlet['module']][$uzlet['name']] = prettify($uzlet['title']);
				}
				
				if ($uzlet['module'] != 'dashboard') 
				{ 
					if (is_array($contents) && !empty($contents['dashboard']) && in_array($uzlet['name'], $contents['dashboard']))
					{
						//if the user has picked the EGlet previously, then it belongs in 'selected' (setting the index preserves the ordering)
						$selected['dashboard'][array_search($uzlet['name'], $contents['dashboard'])] = array('title' => prettify($uzlet['title']), 'name' => $uzlet['name']);
					}
					elseif ($uzlet['dashboard'] == 't')
					{
						// uzlet can appear on Dashboard so add to dashboard available list
						$available['dashboard'][$uzlet['module']][$uzlet['name']] = prettify($uzlet['title']);
					}
				} 
			}
		}
		
		ksort($available);
		
		foreach ($available as &$module)
		{
			ksort($module);
			
			if (is_array($module))
			{
				foreach ($module as &$detail)
				{
					if (is_array($detail))
					{
						asort($detail);
					}
					else
					{
						asort($module);
						break;
					}
				}
			}
		}
		
		ksort($selected);
		
		return array('available'	=> $available
					,'selected'		=> $selected);
	}
	
	static function getPreferencesClass($username = EGS_USERNAME)
	{
		
		if ($username == EGS_USERNAME)
		{
			// get preferences for logged in user
			$prefs	= &UserPreferences::instance($username);
		}
		else
		{
			// get preferences for named user
			$prefs	= &ManagedUserPreferences::instance($username);
		}
		
		return $prefs;
		
	}
}

// end of UserPreferences.php
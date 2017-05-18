<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModuleFactory {

	protected $version = '$Revision: 1.11 $';
	
	public static function Factory($default_page = null, $requireLogin = true)
	{
		
		$prefs			= UserPreferences::Instance(EGS_USERNAME);
		$default_page	= $prefs->getPreferenceValue('default_page', 'shared');
		
		if ($default_page == null)
		{
			$ao = AccessObject::Instance();
			
			$default_page = 'module,'.$ao->getDefaultModule();
		}
		
		if (get_config('SETUP'))
		{
			
			if (defined('MODULE'))
			{ 
				$default_page = MODULE;
			}
			
		}
		
		$router		= RouteParser::Instance();
		$modules	= array();
		
		if (!$requireLogin||isLoggedIn())
		{
				
			foreach ($router->getDispatch() as $key => $dispatch)
			{
				
				if (($key == 'group' || $key == 'module' || strstr($key, 'submodule')) && !empty($dispatch))
				{
					$modules[$key] = $dispatch;
				}
				
			}
			
			if (empty($modules))
			{
				// Default page contains permission type and permission name
				// i.e. type is group or module
				$array = explode(',', $default_page);
				$modules[$array[0]] = $array[1];
			}
		
		}
		else
		{
			$modules['module'] = 'login';
		}
		
		$al = &AutoLoader::Instance();
		
		return $modules;

	}

}

// end of ModuleFactory

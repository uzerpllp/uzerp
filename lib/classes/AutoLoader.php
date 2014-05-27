<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class AutoLoader {
	
	protected $version = '$Revision: 1.9 $';
	
	public $paths = array();
	
	protected function __construct($paths = array())
	{
		$this->paths = $paths;
	}
	
	function addPath($path)
	{
		$this->paths = array_merge($this->paths, $path);		
	}
	
	function addBefore($apath, $before)
	{
		
		$temppaths = array();
		
		foreach ($this->paths as $path)
		{
			
			if ($path <> $before)
			{
				$temppaths[] = $path;
			}
			else
			{
				$temppaths[] = $apath;
				$temppaths[] = $path;
			}
			
		}
		
		$this->paths = $temppaths;
		
	}
	
	function load($classname)
	{
				
		$classname = preg_replace('[^a-zA-Z0-9_-]', '', $classname);
		
		if (isset($this->paths[$classname]))
		{
			require $this->paths[$classname];
			return;
		}
		
		if (isset($this->paths[strtolower($classname)]))
		{
			require $this->paths[strtolower($classname)];
			return;
		}
		
		$cache_id = array('module_component_path',strtolower($classname));
		
		$cache			= Cache::Instance();
		$module_path	= $cache->get($cache_id);
		
		// go and fetch the data and populate the cache
		if ($module_path === FALSE)
		{
		
			$module = new ModuleComponent();
			$module->loadBy('name', strtolower($classname));
			
			if ($module->isLoaded())
			{
				
				if (substr($module->location,-4) === '.php')
				{
					
					$cache->add($cache_id, $module->location);
										
					require $module->location;
					
				}
				
				return;
				
			}
			
		}
		else
		{
			require $module_path;
		}
		
		
	}

	static function &Instance()
	{
		
		static $autoloader;
		
		if ($autoloader == null)
		{
			$autoloader = new AutoLoader();
		}
		
		return $autoloader;
		
	}
	
}

// end of AutoLoader.php
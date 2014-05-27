<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/**
 *Handles the determination of which Controller to use, based on the current URL and any customised routing
 */

class ControllerFactory {

	protected $version = '$Revision: 1.6 $';
	
	/**
	 * Uses global information ($_GET probably) to determine the Controller to instantiate and return
	 * @return string	A (probably) extended form of the Controller class
	 **/
	public static function Factory($requireLogin = true, $modulecomponents)
	{
		$router = RouteParser::Instance();
		
		if($router->Dispatch('controller') !== null)
		{
			$classname=ucfirst(strtolower($router->Dispatch('controller'))).'Controller';
			
			if (is_array($modulecomponents))
			{
				if (isset($modulecomponents[strtolower($classname)]))
				{
					$controller=$classname;
					
					return $controller;
				}
			}
			elseif(class_exists($classname))
			{
				$controller=$classname;
				
				return $controller;
			}
		}

		if (defined('CONTROLLER'))
		{
			$controller = CONTROLLER;
		}
		else
		{
			$controller = 'IndexController';
		}
		
		return $controller;
	}
}

// End of ControllerFactory

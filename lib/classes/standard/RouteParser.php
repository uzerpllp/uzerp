<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class RouteParser {

	protected $version='$Revision: 1.3 $';
	
	protected $routes = array();
	protected $dispatch = array();
	private static $instance;
	
	private function __construct() {
		$this->dispatch=$_GET;	
	}
	
	public static function Instance() {
		
		if (!isset(self::$instance)) {
			self::$instance = new RouteParser();
		}
		
		return self::$instance;
	}

	/**
	 * Resets the singleton instance.
	 * Used for testing.
	 *
	 * @return void
	 */
	public static function resetInstance() {
		self::$instance = null;
	}
	
	public function AddRoute ($route) {
		$this->routes[] = $route;
			
		return true;
	}
	
	public function ParseRoute ($url) {
		foreach ($this->routes as $route) {
			preg_match('#' . $route->GetRegex() . '#', (string) $url, $matches);
			
			if ( !empty($matches) ) {
				$this->dispatch = array_merge($matches, $route->GetPredefinedArguments());
				return true;
			}
		}
		return false;
	}
	
	public function Dispatch ($key = null) {
		if (empty($key)) {
			return $this->dispatch;
		} else {
			if (array_key_exists($key, $this->dispatch)) {
				return $this->dispatch[$key];
			} else {
				return null;
			}
		}
	}
	
	public function getDispatch() {
		return $this->dispatch;
	}
	
}

?>
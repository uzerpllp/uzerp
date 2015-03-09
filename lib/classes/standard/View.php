<?php
 
/** 
 *	View base class
 *
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */

class View implements Iterator, Countable {

	protected $version = '$Revision: 1.13 $';
	
	public $data				= array();
	public $registered_things	= array();
	private $pointer			= 0;
	private $smarty;
	
	function __construct()
	{
		
		$this->smarty = new Smarty;
		
//		$this->smarty->debugging		= TRUE;
		$this->smarty->caching			= 0;
		$this->smarty->cache_dir		= DATA_ROOT . 'cache';
		$this->smarty->cache_lifetime	= 45;
		$this->smarty->compile_dir		= DATA_ROOT . 'templates_c';
		$this->smarty->template_dir		= STANDARD_TPL_ROOT;

		$this->smarty->addPluginsDir(
			array(
				SMARTY_CUSTOM_PLUGINS,
				'plugins'
			)
		);
		
		$this->smarty->compile_check = !(defined('PRODUCTION') && PRODUCTION);
		
		if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))
		{
			
			list($accept,) = explode(',',$_SERVER['HTTP_ACCEPT']);
			
			// we cannot always rely on the HTTP_ACCEPT value, we need a backup just in case
			// as blank.tpl and json.tpl are the same, just use blank. We handle JSON in PHP anyway
			// this is covered by the default: case
					
			switch($accept)
			{
				
				default:
				case 'text/html':
					$layout = 'blank';
					break;
				
				case 'text/javascript':
					$layout = 'json';
					break;
			
			}
			
		}
		else
		{
			$layout = 'default';
		}
				
		$this->smarty->assign('layout', $layout);
		$this->smarty->assign('DATE_FORMAT', DATE_FORMAT);
		
	}

	/**
	 * Sets given variable in view
	 * @param string $name name of variable
	 * @param string $value content of variable
	 */
	function set($name, $value)
	{
		$this->smarty->assign($name, $value);
	}	


	/**
	 *	indicate that $name should be 'registered' rather than assigned
	 *
	 */
	function register($name, &$value)
	{
		$this->smarty->registerObject($name, $value);
	}

	/**
	 * Get data from given view variable
	 * @param string $name name of variable to get
	 * @return variable given by name
	 */
	function get($name)
	{
		
		if (isset($this->data[$name]))
		{
			return $this->data[$name];
		}
		else
		{
			
			$var = $this->smarty->getTemplateVars($name);
			
			if (!empty($var))
			{
				return $var;
			}
			
			return FALSE;
			
		}
		
	}
	
	public function add_plugin_dir($path)
	{
		$this->smarty->addPluginsDir($path);
	}
	
	/** to implement Iterator**/
	public function current()
	{
		$vals = array_values($this->data);
		return $vals[$this->pointer];
	}
	
	public function next()
	{
		$this->pointer++;
	}
	
	public function key()
	{
		$keys = array_keys($this->data);
		return $keys[$this->pointer];
	}
	
	public function rewind()
	{
		$this->pointer = 0;
	}
	
	public function valid()
	{
		return ($this->pointer < count($this));
	}
	
	/** to implement countable **/
	function count()
	{
		return count($this->data);
	}

	function fetch($template)
	{
		
		$template = $this->getTemplateName($template);
		
		return ($template)?$this->smarty->fetch($template):$template;
		
	}
	
	function getTemplateName($template)
	{
		
		// check (and fix) if the path has a tpl extension
		if (substr($template, -4) != ".tpl")
		{
			$template .= ".tpl";
		}
		
		// check if the template exists
		if (!file_exists($template))
		{
			$template_exists = FALSE;
			
			// fetch the smarty tempalte directory list
			$smarty_dirs = $this->smarty->template_dir;
			
			// we need to make sure we're dealing with an array
			if (!is_array($smarty_dirs))
			{
				$smarty_dirs = array($smarty_dirs);	
			}
			
			// now loop through all the known smarty directories looking for our template
			foreach ($smarty_dirs as $dirs)
			{
				// Add the default template dir to the template file name
				if (file_exists($dirs . $template))
				{
					$template = $dirs . $template;
					
					$template_exists = TRUE;
				}
				
			}
		}
		else
		{
			$template_exists = TRUE;
		}
		
		return ($template_exists)?$template:$template_exists;
		
	}
	
	function __call($func, $args)
	{
		
		if (is_callable(array($this->smarty, $func))) 
		{
			return call_user_func_array(array($this->smarty, $func), $args);
		}
		
		throw new Exception('Unknown function: ' . $func . ' - couldn\'t be passed through to Smarty');
		
	}
	
}

// end of View.php
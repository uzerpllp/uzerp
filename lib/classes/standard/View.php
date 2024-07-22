<?php

use Smarty as Smarty;

/** 
 *	View base class
 *
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
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
		$config = Config::Instance();
		$this->smarty = new Smarty();

		if ($config->get('SMARTY_DEBUG')) {
			$this->smarty->setDebugging(true);
		}
		$this->smarty->setCaching(false);
		$this->smarty->setCacheDir(DATA_ROOT . 'cache');
		$this->smarty->setCacheLifetime(45);
		$this->smarty->setCompileDir(DATA_ROOT . 'templates_c');
		$this->smarty->setTemplateDir(STANDARD_TPL_ROOT);
		$this->smarty->setMergeCompiledIncludes(true);

		$this->smarty->addPluginsDir(
			array(
				SMARTY_CUSTOM_PLUGINS,
				'plugins'
			)
		);

		if (strtolower((string) $config->get('ENVIRONMENT')) == 'production') {
			$this->smarty->setCompileCheck(false);
		}

		if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))
		{

			list($accept,) = explode(',',(string) $_SERVER['HTTP_ACCEPT']);

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
	public function current(): mixed
	{
		$vals = array_values($this->data);
		return $vals[$this->pointer];
	}

	public function next() :void
	{
		$this->pointer++;
	}

	public function key(): mixed
	{
		$keys = array_keys($this->data);
		return $keys[$this->pointer];
	}

	public function rewind(): void
	{
		$this->pointer = 0;
	}

	public function valid(): bool
	{
		return ($this->pointer < count($this));
	}

	/** to implement countable **/
	function count(): int
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
		if (substr((string) $template, -4) != ".tpl")
		{
			$template .= ".tpl";
		}

		// check if the template exists
		if (!file_exists($template))
		{
			$template_exists = FALSE;

			// fetch the smarty template directory list
			$smarty_dirs = $this->smarty->getTemplateDir();

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

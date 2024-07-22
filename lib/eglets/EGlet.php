<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

abstract class EGlet {

	protected $version='$Revision: 1.5 $';

	protected	$template		= 'eglets/eglet.tpl';
	protected	$contents		= '';
	protected	$vars			= array();
	public		$should_render	= true;
	protected	$renderer;
	protected	$smarty;

	function __construct(Renderer $renderer)
	{
		$this->renderer = $renderer;
	}

	abstract function populate();

	abstract function render();

	function getTemplate()
	{
		return $this->template;
	}

	function setSmarty(&$smarty)
	{
		$this->smarty = $smarty;
	}

	function getSmarty()
	{
		return $this->smarty;
	}

	function getContents()
	{
		return $this->contents;
	}

	function get_vars()
	{
		return $this->vars;
	}

	function isCached()
	{
		return (isset($_SESSION['eglet_cache'][$this->getCacheID()]));
	}

	function isPaging()
	{
		return FALSE;
	}

	function getCache()
	{

		if (!$this->isCached())
		{
			throw new Exception('Cache value doesn\'t exist');
		}

		return unserialize($_SESSION['eglet_cache'][$this->getCacheID()]);

	}

	function setCache($val)
	{
		$_SESSION['eglet_cache'][$this->getCacheID()] = serialize($val);
	}

	function getCacheID()
	{
		return 'eglet' . EGS_COMPANY_ID . get_class($this) . date('YmdH');
	}

}

// end of EGlet.php
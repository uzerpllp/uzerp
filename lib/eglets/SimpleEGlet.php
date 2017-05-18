<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

abstract class SimpleEGlet extends EGlet
{

	protected $version = '$Revision: 1.6 $';
	
	protected $params =  array();
	
	function render()
	{
		$this->renderer->render($this,$this->smarty);
	}

	function getClassName()
	{
		return 'eglet';
	}
	
	function getTitle()
	{
		return FALSE;
	}
	
	static function getRenderer()
	{
		return new SimpleRenderer();
	}

	function setParameters($params)
	{
		$this->params = $params;
	}
}

// end of SimpleEGlet.php

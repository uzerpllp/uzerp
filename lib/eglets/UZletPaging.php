<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class UZletPaging
{

	protected $version = '$Revision: 1.1 $';
	
	private $_template = 'uzletpaging.tpl';
	
	private $_page = 1;
	
	private $_uzlet;
	private $_smarty;
	
	function __construct($_uzlet, $smarty)
	{
		$this->_uzlet = $_uzlet;
		$this->_smarty = $smarty;
	}
	
	function getTemplate()
	{
		return $this->_template;
	}
	
	function setTemplate($_template)
	{
		$this->_template = $_template;
	}
	
	function setPage($_page)
	{
		$this->_page = $_page;
	}
	
	function render()
	{
		$this->_uzlet->setOffset(($this->_page-1)*$this->_uzlet->getLimit());
		
		$this->_uzlet->populate();
		
		$this->_smarty->set('content', $this->_uzlet->getContents());
		$this->_smarty->set('egletname', get_class($this->_uzlet));
		$this->_smarty->set('uzlet', $this->_uzlet);
		
		return $this->_smarty->fetch($this->getTemplate());
	}

}

// End of UZletPaging

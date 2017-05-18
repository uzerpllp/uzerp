<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SimpleListUZlet extends SimpleEGlet {

	protected $version = '$Revision: 1.1 $';
	
	protected $template = 'list_uzlet.tpl';
	
	protected $limit = 9;
	
	protected $offset = 0;
	
	protected $is_paging = TRUE;
	
	function isPaging()
	{
		return $this->is_paging;
	}
	
	function setPaging($paging)
	{
		$this->is_paging = $paging;
	}
	
	function populate()
	{
		if(!empty($this->contents))
		{
			$this->contents = array_slice($this->contents,0,$this->limit);
		}
	}
	
	function setData($the_data)
	{
		$this->contents = $the_data;
	}
	
	function getLimit()
	{
		return $this->limit;
	}
	
	function getOffset()
	{
		return $this->offset;
	}
	
	function setLimit($limit)
	{
		$this->limit = $limit;
	}
	
	function setOffset($offset)
	{
		$this->offset = $offset;
	}
	
	function setSearchLimit($sh)
	{
		$sh->setLimit($this->limit, $this->offset);
		
		$sh->perpage = $this->limit;
	}
	
}

// End of SimpleListUZlet

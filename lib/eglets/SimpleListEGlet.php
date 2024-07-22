<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SimpleListEGlet extends SimpleEGlet
{

	protected $version = '$Revision: 1.4 $';

	protected $template = 'list_eglet.tpl';

	protected $limit = 10;

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

	function setLimit($limit)
	{
		$this->limit = $limit;
	}
}

// End of SimpleListEGlet

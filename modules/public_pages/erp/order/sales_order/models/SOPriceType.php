<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SOPriceType extends DataObject
{

	protected $version = '$Revision: 1.4 $';
	
	protected $defaultDisplayFields = array('name'
											,'description'
											);
	
	function __construct($tablename = 'so_price_types')
	{
		parent::__construct($tablename);
	}

}

// End of SOPriceType

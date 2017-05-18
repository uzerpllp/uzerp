<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PersonaddressCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.5 $';
	
	function __construct($do = 'Personaddress', $tablename = 'personaddress_overview')
	{
		parent::__construct($do, $tablename);

	}
}


// End of PersonaddressCollection

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DatasetCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.6 $';

	function __construct($do = 'Dataset')
	{

		parent::__construct($do);

	}

}

// End of DatasetCollection

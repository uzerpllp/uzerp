<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintVolumeCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.5 $';
	
	function __construct($do = 'ComplaintVolume', $tablename = 'qc_complaint_volume')
	{
		parent::__construct($do, $tablename);

	}
}

// End of ComplaintVolumeCollection

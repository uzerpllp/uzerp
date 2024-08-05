<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SupplementaryComplaintCodeCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.7 $';

	function __construct($do = 'SupplementaryComplaintCode', $tablename = 'qc_supplementary_complaint_code_overview')
	{
		parent::__construct($do, $tablename);

	}

}

// End of SupplementaryComplaintCodeCollection

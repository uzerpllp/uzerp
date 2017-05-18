<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class FilesizeFormatter implements FieldFormatter {
	
	protected $version = '$Revision: 1.3 $';
	
	public function format($value)
	{
		return sizify($value);
	}
	
}

// end of FilesizeFormatter.php
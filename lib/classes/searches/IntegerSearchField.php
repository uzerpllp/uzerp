<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/**
 * Used for representing SearchFields that accept text from the user
 * The different types that can be set determine the placement of wildcards (%s) within the comparison
 * uses the same toHTML as TextSearchField
 */

class IntegerSearchField extends NumericSearchField
{

	protected $version	='$Revision: 1.1 $';
	
	public function isValid($value, &$errors)
	{
		
		if (!empty($value)
			&& strcmp((int) $value, $value))
		{
			$errors[] = $this->fieldname . ' needs to be numeric';
			return FALSE;
		}
		
		return TRUE;
		
	}
	
}

// end of IntegerSearchField
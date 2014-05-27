<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.3 $ */

function smarty_modifier_break($string) {
	
	if ($string !== '') 
	{
		return $string . '<br />';
	}
	
	return '';
	
}

// end of modifier.break.php
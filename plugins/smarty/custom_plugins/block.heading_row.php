<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.3 $ */

function smarty_block_heading_row($params, $content, &$smarty, $repeat)
{

	if (!empty($content))
	{
		return '<thead><tr>' . $content . '</tr></thead>' . "\n";	
	}

}

// end of block.heading_row.php
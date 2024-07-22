<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

function smarty_modifier_interval_add($base = 0, $add = 0)
{

	$base	= new Interval($base);
	$add	= new Interval($add);

	return $base->add($add)->getValue();

}

// end of modifier.interval_add.php
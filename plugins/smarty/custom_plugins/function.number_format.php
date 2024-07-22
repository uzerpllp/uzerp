<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.4 $ */

function smarty_function_number_format($params, &$smarty)
{

	$decimals				= 2;
	$thousands_sep			= '';
	$dec_point				= '.';
	$red_negative_numbers	= FALSE;

	if (isset($params['options']['decimal_places']))
	{
		$decimals = $params['options']['decimal_places'];
	}

	if (isset($params['options']['thousands_seperator']) && $params['options']['thousands_seperator'] == "true")
	{
		$thousands_sep = ",";
	}

	if (isset($params['options']['red_negative_numbers']) && $params['options']['red_negative_numbers'] == "true")
	{
		$red_negative_numbers=true;
	}

	if ($params['number'] < 0 && $red_negative_numbers == "true")
	{
		return '<span class="red">' . number_format($params['number'], $decimals, $dec_point, $thousands_sep) . "</span>";
	}
	else 
	{
		return number_format($params['number'], $decimals, $dec_point, $thousands_sep);
	}

}

// end of function.number_format.php
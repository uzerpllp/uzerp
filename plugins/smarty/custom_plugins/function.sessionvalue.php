<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.2 $ */

function smarty_function_sessionvalue($params, $model_name, $attribute)
{
	$value='';

	if (isset($_POST[$model_name][$attribute]))
	{
		$value = $_POST[$model_name][$attribute];
	}
	elseif (!empty($params['group']) && isset($_POST[$params['group']][$model_name][$attribute]))
	{
		$value = $_POST[$params['group']][$model_name][$attribute];
	}
	elseif (!empty($params['number']) && isset($_POST[$model_name][$params['number']][$attribute]))
	{
		$value = $_POST[$model_name][$params['number']][$attribute];
	}
	elseif (isset($_SESSION['_controller_data'][$model_name][$attribute]))
	{
		$value = $_SESSION['_controller_data'][$model_name][$attribute];
	}
	
	return $value;
}

// end of function.sessionvalue.php
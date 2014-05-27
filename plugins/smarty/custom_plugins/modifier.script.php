/*
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
* Smarty plugin
* -------------------------------------------------------------
* Type: function
* Name: script
* Purpose: concatinates two strings
* -------------------------------------------------------------
*/
function smarty_modifier_script($params, &$smarty)
{

$code = '';

$code .= '<script LANGUAGE="JavaScript"> function confirmSubmit(){var agree=confirm("Are you sure you wish to continue?"); if (agree)return true ;else return false ;} </script>';


return $code;
}

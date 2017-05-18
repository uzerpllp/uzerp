<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

if (!defined('MODULE'))
{

	if (isset($_GET['module']))
	{
		define('MODULE', $_GET['module']);
	}
	else
	{
		define('MODULE', 'dashboard');
	}
	
}
	
class FileReadingTranslator extends Prettifier implements Translation {
	
	protected $version = '$Revision: 1.4 $';
	
	protected static $strings = array();

	function translate($string)
	{

		if (isset(self::$strings[MODULE][strtolower($string)]))
		{
			return self::$strings[MODULE][strtolower($string)];
		}
		
		if (isset(self::$strings['global'][strtolower($string)]))
		{
			return self::$strings['global'][strtolower($string)];
		}
		
		return parent::translate($string);

	}

}

// end of FileReadingTranslator.php
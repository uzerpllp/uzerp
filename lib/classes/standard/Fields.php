<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/*
 * Fields.php
 * 
 * $Revision: 1.5 $
 * 
 */

class Fields {

	static function getFields_static($tablename, $refresh = FALSE)
	{

		static $cache;
		if(!isset($cache[$tablename]) || $refresh) {
			$fields=self::getFields_none($tablename);	
			$cache[$tablename]=$fields;
		}
		return $cache[$tablename];

	}

	static function getFields_none($tablename)
	{

		$db=&DB::Instance();
		$fields = $db->MetaColumns($tablename,FALSE);
		return $fields;

	}

}

// end of Fields.php
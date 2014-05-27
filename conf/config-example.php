<?php
if (!empty($_SERVER['HTTP_HOST']))
{
	$path='conf/'.$_SERVER['HTTP_HOST'].'.config.php';
}
else
{
	$path = 'none';
}
if(file_exists($path)) {
	include $path;
}
else if(file_exists("../".$path))
{
	include "../".$path;
}
else {
        $conf['DB_TYPE'] = 'pgsql';
        $conf['DB_NAME'] = 'uzerp-base';
        $conf['DB_USER'] = 'www-data';
        $conf['DB_HOST'] = '';
        $conf['DB_PASSWORD'] = '';
        $conf['SETUP'] = false;
	$conf['SYSTEM_MESSAGE'] = '';
	$conf['SYSTEM_STATUS'] = 'uzERP Base Install';
	$conf['SYSTEM_VERSION'] = '2014.3';
	$conf['BASE_TITLE'] = 'uzERP Base System';
	$conf['ADMIN_EMAIL'] = '';

// Defines whether to write login attempts to audit log
//		$conf['AUDIT_LOGIN'] = true;

// Uncomment the following two lines to switch off caching
//		$conf['CACHE_RESOURCES'] = FALSE;
//		$conf['MINIFY_RESOURCES'] = FALSE;
		
// Defines the number of rows that can be returned before Auto Complete is turned on
// i.e. drop down lists will automatically be converted to type ahead/autocomplete
// if the number of rows returned exceeds this value
	$conf['AUTOCOMPLETE_SELECT_LIMIT'] =  500000;

// The following parameter needs to be uncommented if automated ticket loading is used
// This defines the user to use when loading tickets
//		$conf['TICKET_USER'] = '';

// print debug config - uncomment the next $conf line and change the directory name as required
// to enable fop output debug, then go that directory and run
// fop -xsl <name of generated xsl file> -xml <name of generated xml file> -pdf <name of pdf file to create>
// and this should then give additional messages to help identify any problems
//		$conf['OUTPUT_DEBUG_PATH'] = 'var/www/uzerp-base/data/print_debug/'; 

// IPP Logging - uncomment the following three $conf lines
// and change the values as required to define where logging is written
// and the level of logging
//		$conf['IPP_LOG_PATH'] = 'var/www/uzerp-base/data/logs/print.log'; // file name or email address
//		$conf['IPP_LOG_TYPE'] =  'file';  // file, e-mail or logger
//		$conf['IPP_LOG_LEVEL'] = 0;  // 0 - no logging, 3 - most verbose

// This defines the enviroment; development or production (default)
//		$conf['ENVIRONMENT'] = 'development';

}

// end of config.php


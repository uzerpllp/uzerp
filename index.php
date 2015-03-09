<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.12 $ */

session_start();

// define time at start of request
define('START_TIME', microtime(TRUE));

require 'system.php';
require 'vendor/autoload.php';
$system = system::Instance();


 //*******
// CONFIG

	// we have to load parts of system to load the config
	$system->check_system();
	$system->load_essential();
	

 //**************************
// ERROR REPORTING & LOGGING

	
	// set the error reporting based on the environment
	switch (strtolower(get_config('ENVIRONMENT')))
	{
		
		case 'development':
			error_reporting(E_ALL ^ E_USER_DEPRECATED ^ E_DEPRECATED ^ E_NOTICE);
			//error_reporting(E_ALL);
			break;
			
		case 'production':
		default:
			error_reporting(E_ERROR);
			break;
			
	}
	
	// define where the log should go, syslog or a file of your liking with
	$log = $_SERVER["DOCUMENT_ROOT"] . 'data/logs/' . session_id() . '.log';
	
	// set the php_ini error log value with the log path
	ini_set("error_log", $log);


 //*******************
// LOAD THE FRAMEWORK

	$system->display();
	
	if (AUDIT || get_config('AUDIT_LOGIN'))
	{
		
		if (is_array($system->controller->_data) && isset($system->controller->_data['password']))
		{
			$system->controller->_data['password'] = '********************';
		}
		
		$audit = Audit::Instance();
		$audit->write(print_r($system->controller->_data, TRUE) . print_r($system->flash, TRUE), TRUE, (microtime(TRUE) - START_TIME));
		$audit->update();
	}
	

// end of index.php

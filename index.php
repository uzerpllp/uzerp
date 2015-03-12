<?php
 
/** 
 *	uzERP Start-up Entry Point
 *
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved. 
 *
 *	This file is part of uzERP.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 *
 *	uzERP is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with uzERP.  If not, see <http://www.gnu.org/licenses/>.
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
	set_exception_handler('exception_handler');
	function exception_handler($exception) {
		if (defined('SENTRY_DSN'))
		{
			try {
				$client = new Raven_Client(SENTRY_DSN, array(
						'curl_method' => 'async',
						'verify_ssl' => FALSE,
				));
				$event_id = $client->getIdent($client->captureException($exception));
				echo "<h1>Sorry, Something went wrong:</h1>";
				echo "Please call support and include the following reference ID in your problem report: <strong>" . $event_id . "</strong>";
				
			}
			catch (Exception $e) {
				//If something went wrong, just continue.
			}
		}
		else
		{
			echo "<h1>Sorry, Something went wrong:</h1>";
			echo "<p>" . $exception->getMessage() . "</p>";
			echo "<strong>Please call support and include the message above.</strong>";
		}
	}
	
	// if a we have a DSN defined, log exceptions and fatals to Sentry
	if (defined('SENTRY_DSN'))
	{
		try {
			$client = new Raven_Client(SENTRY_DSN, array(
					'curl_method' => 'async',
					'verify_ssl' => FALSE,
			));
			$error_handler = new Raven_ErrorHandler($client);
			$error_handler->registerShutdownFunction();
		}
		catch (Exception $e) {
			//If something went wrong, just continue.
		}
	}
	
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

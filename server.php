<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.3 $ */

session_start();

// define time at start of request
define('START_TIME', microtime(TRUE));

require 'system.php';
$system = system::Instance();

//Define where do you want the log to go, syslog or a file of your liking with
$log=$_SERVER["DOCUMENT_ROOT"].'data/logs/'.session_id().'.log';
ini_set("error_log", $log);

//include setup and configuration
$system->xmlrpcServer();

// End of Server
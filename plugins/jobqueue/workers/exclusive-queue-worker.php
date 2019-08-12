<?php
/**
 *  A PHP worker for the exclusive-job queue
 * 
 *  Exclusive jobs only allow one job instance to be in the queue at any time.
 *  They may also be exclusive with another job. For example, item cost rollover
 *  cannot be queued while a full item recost is running, @see uzJob::uzExclusiveJob.
 *
 *  @author Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 **/

require 'system.php';
error_reporting(E_ERROR);

// system.php uses this to set paths, setting it here because this is not an http request.
// Typically, workers should be run from the uzERP root directory, which 'getcwd' will return
$_SERVER['DOCUMENT_ROOT'] = getcwd();

// uzERP initialisation, autoload, etc.
$system = system::Instance();
$system->load_essential();
$config = Config::Instance();

// Jobs should not be prevented by system policies
define('SYSTEM_POLICIES_ENABLED', false);

// All jobs run as the 'admin' user of company 1
define('EGS_USERNAME', 'admin');
define('EGS_COMPANY_ID', 1);

// Use the uzERP config for DJJob set-up
DJJob::configure([
    'driver' => $config->get('DB_TYPE'),
    'host' => $config->get('DB_HOST'),
    'dbname' => $config->get('DB_NAME'),
    'user' => $config->get('DB_USER'),
    'password' => $config->get('DB_PASSWORD'),
]);

// Start a worker for the exclusive-job queue
$worker = new DJWorker(['queue' => 'exclusive']);
$worker->start();
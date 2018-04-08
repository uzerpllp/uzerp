<?php
require 'system.php';
//require 'test-job.php';

$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/';


//define('HTTP_HOST', 'example.com');



$system = system::Instance();
$system->load_essential();
$config = Config::Instance();

define('SYSTEM_POLICIES_ENABLED', false);
define('EGS_USERNAME', 'admin');
define('EGS_COMPANY_ID', 1);
DJJob::configure([
    'driver' => $config->get('DB_TYPE'),
    'host' => $config->get('DB_HOST'),
    'dbname' => $config->get('DB_NAME'),
    'user' => $config->get('DB_USER'),
    'password' => $config->get('DB_PASSWORD'),
]);

$worker = new DJWorker(['queue' => 'exclusive']);
$worker->start();

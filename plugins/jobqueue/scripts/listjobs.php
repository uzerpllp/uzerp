<?php
/**
 *  Output a list of queued jobs
 *
 *  @author Steve Blamey <blameys@blueloop.net>
 *  @license GPLv3 or later
 *  @copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

require 'system.php';
error_reporting(E_ERROR);

// system.php uses this to set paths, setting it here because this is not an http request.
$_SERVER['DOCUMENT_ROOT'] = getcwd();

// uzERP initialisation, autoload, etc.
$system = system::Instance();
$system->load_essential();
$config = Config::Instance();

// Use the uzERP config for DJJob set-up
DJJob::configure([
    'driver' => $config->get('DB_TYPE'),
    'host' => $config->get('DB_HOST'),
    'dbname' => $config->get('DB_NAME'),
    'user' => $config->get('DB_USER'),
    'password' => $config->get('DB_PASSWORD'),
]);

$jobs = DJJob::runQuery(
    'select * from jobs;'
);

$output = new ConsoleOutput();
$table = new Table($output);
        $table->setHeaders(['Job#', 'Job Class', 'Queue', 'Queued at', 'Attempts', 'Locked at', 'Failed at', 'Error']);

$rows = [];
foreach ($jobs as $job) {
    $handler = get_class(unserialize(base64_decode($job['handler'])));
    $rows[] = [$job['id'], $handler, $job['queue'], $job['created_at'], $job['attempts'], $job['locked_at'], $job['failed_at'], $job['error']];
}

$table->setRows($rows);
$table->render();
?>
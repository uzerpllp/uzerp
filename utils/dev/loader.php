<?php

// Bootstrap uzERP
require 'system.php';

$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
global $system;
$system = system::Instance();
$system->load_essential(true);

define('EGS_COMPANY_ID', 1);
define('EGS_USERNAME', 'admin');

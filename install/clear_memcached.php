<?php

/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/* $Revision: 1.11 $ */

// clear the memcached cache

// we don't need to include Cache.php, System::checkSystem() does that for us
//require_once 'conf/config.php';
require_once 'system.php';

$arguments = getopt("f:h:");
//echo 'Arguments='.print_r($arguments, true)."\n";
if (empty($arguments['f']))
{
    echo "No file path\n";
    exit;
}

if (empty($arguments['h']))
{
    echo "No host name supplied\n";
    exit;
}

$_SERVER['DOCUMENT_ROOT']	= $arguments['f'];
$_SERVER['HTTP_HOST']		= $arguments['h'];

echo 'Clearing Caches in '.$_SERVER['DOCUMENT_ROOT'];
// instantiate system and checks
$system = System::Instance();

$system->check_system();

// Disable any caching to ensure any new lib files are loaded
$system->load_essential(TRUE);

// flush the cache
$cache = Cache::Instance();
$cache->flush();

echo " - Done\n";

// end of clear_memcached.php

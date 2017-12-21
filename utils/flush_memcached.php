<?php

/**
 * uzERP memcached flush utility
 *
 * Flush memcached key for a specified uzERP instance.
 * 
 * options:
 *    -i instance name, e.g. uzerp. This option is required.
 *    -h memcached host name or IP address, default = localhost
 *    -p memcached port, default = 11211
 *    -v list deleted keys
 *
 * @package uzerp
 * @author Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 **/

$arguments = getopt("i:h:p:v");

if (empty($arguments['i'])) {
    echo "No instance specified\n";
    exit(1);
}

if (empty($arguments['h']) || !isset($arguments['h'])) {
    $host = 'localhost';
} else {
    $host = $arguments['h'];
}

if (empty($arguments['p']) || !isset($arguments['p'])) {
    $port = '11211';
} else {
    $port = $arguments['p'];
}

$regex = $arguments['i'];

// initiate the memcached instance
$cache = new \Memcached();
$cache->addServer($host, $port);

// get all stored memcached items
$keys = $cache->getAllKeys();
$cache->getDelayed($keys);
$store = $cache->fetchAll();

// delete keys by regex
$keys = $cache->getAllKeys();
if (count($keys) > 0) {
    foreach($keys as $item) {
        if(preg_match('/'.$regex.'/', $item)) {
            $cache->delete($item);
	    if (isset($arguments['v']) && $arguments['v'] === false) {
	        echo "deleted: " . $item . "\n";
            }
        }
    }
    echo "Cleared all keys with prefix '{$regex}'\n";
    exit(0);
} else {
    echo "No keys found with prefix '{$regex}'\n";
    exit(0);
}

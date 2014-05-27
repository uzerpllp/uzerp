#!/bin/bash
#
#	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
#
#	Released under GPLv3 license; see LICENSE.
#
#
# $Revision: 1.7 $
#
# Clear cache folders and memcached
#

cd $1
rm -f data/resource_cache/*
rm -f data/templates_c/*
rm -f data/tmp/*

php install/clear_memcached.php -f $1 -h `hostname -f`

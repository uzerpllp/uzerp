<?php
/**
 * Database connection settings
 */

$conf['DB_NAME'] = 'uzerp';
$conf['DB_USER'] = 'www-data';
$conf['DB_HOST'] = 'localhost';
$conf['DB_PASSWORD'] = '';


/**
 * Memcached connection settings
 */

// Memcached host or IP address
// Default: 'localhost'
$conf['MEMCACHED_HOST'] = 'localhost';

// Memcached port
// Default: '11211'
$conf['MEMCACHED_PORT'] = '11211';

// Prefix for memcache keys
// Default: database name (from DB_NAME)
$conf['MEMCACHED_PREFIX'] = 'uzerp';

// Turn on database query result caching (memcached required).
// Default: false
// $conf['USE_ADODB_CACHE'] = true;


/**
 * uzERP settings
 */

// Appears at the top-left of every uzERP page, highlighted with a yellow background.
// Default: ''
$conf['SYSTEM_MESSAGE'] = '';

// Appears on the login page
// Default: ''
$conf['SYSTEM_STATUS'] = 'uzERP Base Install';

// Appears in the footer on every page
// Default: ''
$conf['SYSTEM_VERSION'] = '1.26.8';

// Appears in the footer on every page
// Default: ''
$conf['BASE_TITLE'] = 'uzERP Base System';

// Email to send system email to, e.g. backflush errors
// Default: ''
$conf['ADMIN_EMAIL'] = '';

// The 'from' email address for system emails
// Default: ''
$conf['ADMIN_FROM_EMAIL'] = '';

// Defines the number of rows that can be returned before auto-complete is turned on.
// Drop down lists will automatically be converted to type-ahead/autocomplete
// if the number of rows returned exceeds this value.
// Default: 500000
$conf['AUTOCOMPLETE_SELECT_LIMIT'] =  500000;

// The following must be set if automated ticket loading is used.
// It defines the user to use when loading tickets.
// Default: ''
$conf['TICKET_USER'] = '';


/**
 * Logging, debug and development settings
 */

// Sentry configuration
// If the following constants are defined, uzERP will send
// flash errors and warnings, and uncaught PHP exceptions to Sentry

//if (!defined('SENTRY_DSN') && !defined('SENTRY_CONFIG')) {
//    define('SENTRY_DSN', 'https://sdkjfhsjkdhf:2398623986429834@sentry.io/1234567');
//    define('SENTRY_CONFIG', serialize(array('curl_method' => 'async', 'release' => '1.6.2')));
//}

// Log/audit login attempts
// Default: true
$conf['AUDIT_LOGIN'] = false;

// Path to store app logger files
// Default: ''
$conf['UZERP_LOG_PATH'] = '/var/log/uzerp';

// Prevent print and/or email output
// Default: false
$conf['DEV_PREVENT_EMAIL'] = false;
$conf['DEV_PREVENT_PRINT'] = false;

// Print debug output directory (Apache FOP)
// Enables output of the FOP xml and xsl files for testing on the command line.
// In that directory, fop can be run to get information about a failing output:
//   $ fop -xsl <name of generated xsl file> -xml <name of generated xml file> -pdf <name of pdf file to create>
// This should provide additional messages to help identify any problems.
$conf['OUTPUT_DEBUG_PATH'] = 'var/www/uzerp-base/data/print_debug/'; 

// IPP Logging
//$conf['IPP_LOG_PATH'] = 'var/www/uzerp-base/data/logs/print.log'; // file name or email address
//$conf['IPP_LOG_TYPE'] =  'file';  // file, e-mail or logger
//$conf['IPP_LOG_LEVEL'] = 0;  // 0 - no logging, 3 - most verbose

// This defines the environment; development or production
// When set to 'development':
//   - PHP errors will be logged the file defined in UZERP_LOG_PATH
//   - ADOdb debugging will be turned on and logged to a file in UZERP_LOG_PATH
//   - Smarty templates will be re-compiled if changed
// Default: production
//$conf['ENVIRONMENT'] = 'development';

// Debug smarty templates
//   - The smarty debug console will be shown for all pages
// Default: false
//$conf['SMARTY_DEBUG'] = true;

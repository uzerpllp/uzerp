<?php

/**
 * resource.php Handle request to css and js resources
 *
 * @version $Revision: 1.6 $
 * @package uzerp
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2015 uzERP LLP (support#uzerp.com). All rights reserved.
 **/

// include relevant files
require_once ('lib/classes/utils/ResourceHandler.php');
require_once ('conf/config.php');
require_once ('system.php');

// get resource type
$type = key($_GET);

// set the theme if specified in the request
if (isset($_GET['theme']) and $_GET['theme']) {
    $theme = $_GET['theme'];
} else {
    $theme = 'default';
}

// make sure type is not empty, and that it's an allowed type, throw a 403 if it is
$allowed_types = array(
    'js',
    'css'
);

if (empty($type) or ! in_array($type, $allowed_types)) {
    header("HTTP/1.0 403 Forbidden");
    exit();
}

// instanciate system
$system = System::Instance();

$system->check_system();
$system->load_essential();

// if we're dealing with global resources...
if (! isset($_GET['file'])) {
    
    // we want scope to serve up other groups of resources
    // default to all, if none specified
    
    if (! isset($_GET['group'])) {
        $_GET['group'] = 'all';
    }
    
    switch ($_GET['group']) {
        
        case 'all':
        default:
            
            // get resources from system
            $resources = $system->getResources($theme);
            $resources = $resources[$type];
            break;
        
        case 'uzlet':
            
            // get resources from system
            $resources = $system->get_uzlet_resources($type);
            break;
    }
} else {
    
    // otherwise set the resources var as our file
    $resources = $_GET['file'];
}

// pass data to Handle_resources::build function
ResourceHandler::build($type, $resources, $theme);
?>

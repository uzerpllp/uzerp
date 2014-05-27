<?php 
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.5 $ */

// include relevent files
require_once('lib/classes/utils/ResourceHandler.php');
require_once('conf/config.php');
require_once('system.php');

// get resource type
$type = key($_GET);

// make sure type is not empty, and that it's an allowed type, throw a 403 if it is
$allowed_types = array('js', 'css');

if (empty($type) OR !in_array($type, $allowed_types)) {
	header ("HTTP/1.0 403 Forbidden");
	exit;
}

// instanciate system
$system = System::Instance();

$system->check_system();
$system->load_essential();

// if we're dealing with global resources...
if (!isset($_GET['file']))
{
	
	// we want scope to serve up other groups of resources
	// default to all, if none specified
	
	if (!isset($_GET['group']))
	{
		$_GET['group'] = 'all';
	}
	
	switch ($_GET['group'])
	{
		
		case 'all':
		default:
			
			// get resources from system
			$resources = $system->getResources();
			$resources = $resources[$type];
			break;
			
		case 'uzlet':
			
			// get resources from system
			$resources = $system->get_uzlet_resources($type);
			break;
			
	}
	
}
else
{

	// otherwise set the resources var as our file
	$resources = $_GET['file'];
	
}

// pass data to Handle_resources::build function
ResourceHandler::build($type, $resources);

// end of resource.php
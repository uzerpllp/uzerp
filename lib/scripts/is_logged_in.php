<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/* $Revision: 1.2 $ */

// do a quick check (if asked) to see if the user is logged in
// this function is placed here purely for performance reasons

session_start();

// set the header so jQuery can 'guess' the response
header('Content-type: application/json');

// if the username is set in the session...
if (isset($_SESSION['username']) && !empty($_SESSION['username']))
{
	
	// return the username
	echo json_encode(
		array(
			'logged_in'	=> TRUE,
			'username'	=> $_SESSION['username']
		)
	);
	
}
else
{
	
	// otherwise report that the user is not logged in
	echo json_encode(array('logged_in' => FALSE));
	
}

exit;

// end of check_logged_in.php

<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ManagedUserPreferences extends UserPreferences {

	protected $version = '$Revision: 1.1 $';
	
	public static function &instance($username = EGS_USERNAME)
	{
		
		static $instance;
		
		if ($instance == NULL)
		{
			
			$instance = new ManagedUserPreferences();
			
			if (empty($username))
			{
				$instance->loggedin = FALSE;		
			}
			else
			{
				$instance->username	= $username;
				$instance->loggedin	= TRUE;
			}
			
			$instance->initialise();
			
		}
		
		return $instance;
		
	}
	
	
}

// end of ManagedUserPreferences.php
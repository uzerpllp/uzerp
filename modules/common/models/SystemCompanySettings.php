<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SystemCompanySettings {
	
	protected $version = '$Revision: 1.5 $';
	
	const DAY_START_HOURS	= '9';
	const DAY_START_MINUTES	= '0';
	const DAY_LENGTH		= '8';
	const _THEME			= 'default';
	
	public static function get($var)
	{
		
		$return	= FALSE;
		
		if (MEMCACHED_ENABLED)
		{
			$cache	= Cache::Instance();
			$return	= $cache->get(array('system_company_settings', $var));
		}
		
		if (FALSE === $return)
		{
			
			//if it's a constant, use that
			$c_var = 'self::' . $var;
			
			if (defined($c_var))
			{
				$return = constant($c_var);
			}
			else
			{
				
				//check for a db-field corresponding to the value. and use that
				$sc = new Systemcompany();
				
				if (EGS_COMPANY_ID !== 'null' && $sc->isField($var))
				{
					$res	= $sc->load(EGS_COMPANY_ID);
					$return	= $sc->$var;
				}
				else
				{
					
					//_ indicates a default for a DB-value
					$c_var = 'self::_' . $var;
					
					if (defined($c_var)) 
					{
						$return = constant($c_var);
					}
					
				}
				
			}
			
			if (MEMCACHED_ENABLED)
			{
				
				$cache->add(
					array('system_company_settings', $var),
					$return,
					28800
				);
			
			}
			
		}
		
		return $return;
		
	}
	
}

// end of SystemCompanySettings.php
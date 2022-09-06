<?php
 
/** 
 *	(c) 2021 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Config {

	protected $version = '$Revision: 1.3 $';
	
	
	protected $options	= NULL;
	protected $defaults	= array();
	
	public static function &Instance()
	{
		
		static $config;
		
		if ($config === NULL)
		{
			$config = new Config();
		}

		return $config;
		
	}
	
	public function __construct()
	{
		
		if ($this->options === NULL)
		{
			// get the file
			include FILE_ROOT . 'conf/config.php';

			// Load .env file and merge
			$dotenv = Dotenv\Dotenv::createImmutable(FILE_ROOT . 'conf/');
			$dotenv->safeLoad();
			try {
				if ($dotenv->ifPresent('UZERP_MANAGE_USER_SESSIONS')->isBoolean()) {
					// Absolute session age
					$dotenv->required(['USER_SESSION_MAX_AGE_SECS'])->notEmpty()->isInteger();
					// Require login after a period of inactivity
					$dotenv->required(['USER_ACTIVITY_TIMEOUT_SECS'])->notEmpty()->isInteger();
				}
			} catch (Exception $e) {
				echo($e->getMessage());
				exit();
			}
			$conf = $conf + $_ENV;
			
			// we have to populate the defaults here as we have values
			// that require evaluation, which must be done at run time
			// http://stackoverflow.com/questions/9616822
			
			$this->defaults = array(
	
				// DATABASE
				'DB_TYPE'					=> 'pgsql',
				'DB_NAME'					=> '',
				'DB_HOST'					=> '',
				'DB_USER'					=> '',
				'DB_PASSWORD'				=> '',
				'USE_ADODB_CACHE'			=> false,
				
				// SYSTEM
				'SETUP'					=> TRUE,
				'ENVIRONMENT'				=> 'production',
				'SMARTY_DEBUG'				=> false,
				'SYSTEM_MESSAGE'			=> '',
				'SYSTEM_STATUS'				=> '',
				'SYSTEM_VERSION'			=> '',
				'BASE_TITLE'				=> 'uzERP',
				'ADMIN_EMAIL'				=> '',
				'ADMIN_FROM_EMAIL'			=> '',
				'AUDIT_LOGIN'				=> TRUE,
				'UZERP_LOG_PATH'			=> '',
		
				// MEMCACHED
				'MEMCACHED_HOST'			=> 'localhost',
				'MEMCACHED_PORT'			=> '11211',
				'MEMCACHED_PREFIX'			=> '',
		
				// IPP LOGGING
				'IPP_LOG_LEVEL'				=> 0,
				'IPP_LOG_PATH'				=> '',
				'IPP_LOG_TYPE'				=> 'logger',
				
				// OUTPUT
				'OUTPUT_DEBUG_PATH'			=> '',
				'DEV_PREVENT_EMAIL' 		=> FALSE,
				'DEV_PREVENT_PRINT' 		=> FALSE,
				'PHPMAILER_CONF'			=> FALSE,
		
				// AUTOCOMPLETE
				'AUTOCOMPLETE_SELECT_LIMIT'	=> 500000
				
			);
			
			// this will come in a future release
			// $this->defaults['SYSTEM_VERSION'] = file_get_contents(FILE_ROOT . 'current_version');
			
			// merge the given config with defaults
			// and apply back to our settings var
			
			$this->options = $conf + $this->defaults;
			
			
			 //************************
			// POST CONFIG: SET VALUES
			
			// if we've got no memcached prefix set it to the db_name
			if (empty($this->options['MEMCACHED_PREFIX']))
			{
				$this->options['MEMCACHED_PREFIX'] = $this->options['DB_NAME'];
			}
			
		}
		
	}
	
	public function get($key)
	{
		
		// force the given key to uppercase
		$key = strtoupper($key);
		
		// if the key doesn't exist, return false
		if (!isset($this->options[$key]))
		{
			return FALSE;
		}
		
		// return the value for the given setting key
		return $this->options[$key];
		
	}
	
	public function set($key, $value)
	{
		$this->options[$key] = $value;	
	}
	
	public function get_all()
	{
		return $this->options;
	}
		
}

// end of Config.php

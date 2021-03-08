<?php
 
/** 
 *	(c) 2021 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DB {
	
	protected $version = '$Revision: 1.10 $';
	
	public $connected;
	private $db;

	private function __construct()
	{
		
		// double check if the psql extension exists
		if (!extension_loaded('pgsql'))
		{
			die('Missing extension: pgsql');
		}

		// get a few settings
		$db_type = get_config('DB_TYPE');
		$db_name = get_config('DB_NAME');
	
		$this->db = NewADOConnection($db_type);
		$this->db->SetFetchMode(ADODB_FETCH_ASSOC);

		if (class_exists('Memcached')) {
			$this->db->memCache = true;
			$this->db->memCacheHost	= [get_config('MEMCACHED_HOST')];
			$this->db->memCachePort = get_config('MEMCACHED_PORT');
			// From ADOdb 5.22, this should set our prefix on cached query results in memcached,
			// https://adodb.org/dokuwiki/doku.php?id=v5:userguide:memcached#adding_options_to_the_memcached_server
			$this->db->memCacheOptions = [Memcached::OPT_PREFIX_KEY => get_config('MEMCACHED_PREFIX')];
		}
		
		if (defined('TESTING') && TESTING == TRUE)
		{
			
			if (defined('TEST_DB_NAME'))
			{
				$dbname = TEST_DB_NAME;
			}
			else
			{
				die("<h1>No test database defined</h1>");
			}
			
		}
		
		if (!defined('DB_CREATE'))
		{
			define('DB_CREATE', FALSE);
		}
		
		if (!DB_CREATE && (!isset($db_name) || empty($db_name)))
		{
			die("<h1>No database defined</h1>");
		}
		
		// $this->db_connect returns true / false, check if db has connected or not
		if (DB_CREATE)
		{
			$connection = $this->db->Connect(
				get_config('DB_HOST'),
				get_config('DB_USER'),
				get_config('DB_PASSWORD')
			);
		}
		else
		{
			$connection = $this->db->Connect(
				get_config('DB_HOST'),
				get_config('DB_USER'),
				get_config('DB_PASSWORD'),
				$db_name
			);
		}
		
		// output an error message if the db connection failed
		if (!$connection)
		{
			die("<h1>Error connecting to database</h1>");
		}
		
		$this->connected = $connection;

		if (!defined('ADODB_OUTP'))
		{
			define('ADODB_OUTP', 'adodb_outp');
		}
		
	}

	public static function &Instance()
	{
		
		static $db;
		
		if ($db === null)
		{
			$db = new DB();
		}
		
		return $db;
		
	}
	
	public static function debug($debug = TRUE)
	{
		$db = self::Instance();
		$db->debug=$debug;
	}
	
	function __call($func, $args)
	{
		
		if (is_callable(array($this->db, $func)))
		{
			return call_user_func_array(array($this->db, $func), $args);
		}
		
	}
	
	function __set($key, $var)
	{
		$this->db->$key = $var;
	}
	
	function __get($key)
	{
		return $this->db->$key;
	}

	function extractSchema()
	{
		$schema = new adoSchema($this->db); 
		return $schema->extractSchema();
	}
	
}

// end of DB.php
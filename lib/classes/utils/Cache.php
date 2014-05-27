<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Cache {

	protected $version = '$Revision: 1.14 $';
		
	// memcache server handle
	protected $memcached;
	
	// an array to keep track of the keys in use
	protected $keys_table = array();
	
	// whether to use the session if memcached is unavailable
	// the session will have NO timeout, other than that 
	// maintained by php internally.
	
	public $use_file_system = TRUE;
	
	// default time for expiration in seconds
	public $default_expiration = 28800;
	
	public static function &Instance()
	{
		
		static $cache;
		
		if ($cache == null)
		{

			$cache = new Cache();
			
			// load the keys table from the cache
			$cache->loadKeys();

		}

		return $cache;
		
	}
	
	public function __construct()
	{
		
		$memcached_prefix = get_config('MEMCACHED_PREFIX');
		
		// first check we're okay to use memcached
		if ((defined('HAS_MEMCACHED') && !HAS_MEMCACHED) || empty($memcached_prefix))
		{
			define('MEMCACHED_ENABLED', FALSE);
			$this->memcached = FALSE;
			return;
		}
		
		// set the keys table... key
		$this->set_table_key();
		
		// connect to the memcached server
		$this->memcache = new Memcached();
		$this->memcache->addServer(get_config('MEMCACHED_HOST'), get_config('MEMCACHED_PORT'));

		// memcached will allow us to connect to a server even if it is disabled
		// instead test the connection by attempting to set a value to it
		
		if ($this->memcache->set($memcached_prefix . '_MEMCACHED_CONNECTION_TEST', 'CONNECTION_TEST', 60) === FALSE)
		{
			define('MEMCACHED_ENABLED', FALSE);			
		}
		else
		{
			define('MEMCACHED_ENABLED', TRUE);
		}
		
	}
	
	public function add($key, $value, $expiration = NULL)
	{
		
		// set the default expiration
		if (is_null($expiration))
		{
			$expiration = $this->default_expiration;
		}
		
		// if memcached is not available attempt to set the cache to the file system instead
		if (!MEMCACHED_ENABLED)
		{
			return $this->add_file($key, $value);
		}
		
		// build the cache key, with prefix, in array format
		$key = $this->build_key($key);
		
		// push the value to the cache, add to the keys table is successful
		if ($this->memcache->set($key, $value, $expiration) === TRUE)
		{
			$this->addKey($key);
			return TRUE;
		}
		
		return FALSE;
		
	}
	
	/**
	 * get a value from the cache
	 * 
	 * @param $key
	 * @param $expiration - used only for file based cache
	 */
	public function get($key, $expiration = NULL)
	{
		
		// set the default expiration
		if (is_null($expiration))
		{
			$expiration = $this->default_expiration;
		}
		
		// if memcached is not available attempt to get the cache from the file system instead
		if (!MEMCACHED_ENABLED)
		{
			return $this->get_file($key, $expiration);
		}
		
		// build the cache key, with prefix, in array format
		$key = $this->build_key($key);
		
		// return the cache value
		return $this->memcache->get($key);
	
	}
	
	public function flush()
	{
				
		// no point in checking if keys table is empty
		if (!empty($this->keys_table))
		{
			
			// loop through all keys...
			foreach($this->keys_table as $key => $value)
			{
				
				// build the cache key, with prefix, in array format
				$value = $this->build_key($value);
				
				// ... and delete it the key is actually the array key, the value is the memcached key
				$this->delete($value);
				
			}
			
			// clear the keys table
			$this->delete(MEMCACHED_KEYS_TABLE);
			
		}
		
		// flush any files in cache
		$this->flush_files();
		
		if (MEMCACHED_ENABLED)
		{
		
			// flush any unknown keys
			$this->memcache->flush();
		
		}
		
		// destroy the local keys table
		$this->keys_table = array();
	
	}
	
	public function delete($key)
	{
		
		// make sure we've got a valid connection
		if (!MEMCACHED_ENABLED) { return FALSE; }
		
		// build the cache key, with prefix, in array format
		$key = $this->build_key($key);
		
		// delete the cache item and hold onto the response
		$result = $this->memcache->delete($key);
		
		if ($result === TRUE)
		{
			
			// remove the key(s) from the keys table
			$this->removeKey($key);

			return TRUE;
			
		}
		
		return FALSE;
		
	}
	
	private function addKey($key)
	{
		
		// make sure we've got a valid connection
		if (!MEMCACHED_ENABLED) { return FALSE; }

		// build the cache key, with prefix, in array format
		$key = $this->build_key($key);
		
		// remove any existing instances of the key
		$this->removeKey($key);
		
		// add the ket to the array
		$this->keys_table[] = $key;
		
		// push the keys table back into the cache
		$this->memcache->set(MEMCACHED_KEYS_TABLE, $this->keys_table);
				
	}
	
	private function removeKey($key)
	{
		
		// make sure we've got a valid connection
		if (!MEMCACHED_ENABLED) { return FALSE; }
		
		// build the cache key, with prefix, in array format
		$key = $this->build_key($key);
		
		// find keys to remove
		$removed_keys = array_keys($this->keys_table, $key);
		
		// loop through and unset them from keys table
		foreach ($removed_keys as $key)
		{
			unset($this->keys_table[$key]);
		}
		
		// set the table back to the cache
		$this->memcache->set(MEMCACHED_KEYS_TABLE, $this->keys_table);
		
	}
	
	public function loadKeys()
	{
		
		// make sure we've got a valid connection
		if (!MEMCACHED_ENABLED) { return FALSE; }
			
		// get the keys, only setting if it's valid
		$keys = $this->get(MEMCACHED_KEYS_TABLE);
		
		if ($keys !== FALSE && is_array($keys))
		{
			$this->keys_table = $keys;
		}
		
		// cleanup any invalid keys
		$this->cleanKeys();
		
	}
	
	public function cleanKeys()
	{
		
		// make sure we've got a valid connection
		if (!MEMCACHED_ENABLED) { return FALSE; }
		
		// no point in cleaning if the keys table is empty
		if (!empty($this->keys_table))
		{
			
			// loop through keys, finding any that no longer exist
			foreach ($this->keys_table as $key => $value)
			{
				
				// ATTN: what if the cached value is actually false?
				if ($this->get($value) === FALSE)
				{
					
					// the key must not exist
					$this->memcache->delete($value);
					unset($this->keys_table[$key]);	
					
				}
				
			}
			
		}
		
	}
	
	public function checkConnection()
	{
		
		if ($this->memcached === FALSE)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
		
	}
	
	private function build_key($keys, $before = '[', $after = ']')
	{
		
		$memcached_prefix = get_config('MEMCACHED_PREFIX');
		
		// if the start of the string is the prefix and we can find a [ character
		// we must have already built the key 
		
		// ensure the keys variable is an array
		
		// if...
		// 1) keys string starts with the prefix
		// 2) keys string includes a [ characters
		
		if (!is_array($keys) && substr($keys, 0, strlen($memcached_prefix)) === $memcached_prefix && strpos($keys, '[') !== FALSE)
		{
			return $keys;
		}
	
		$key_string = $memcached_prefix;
		
		if (!is_array($keys))
		{
			$keys = array($keys);
		}
		
		foreach ($keys as $key)
		{
			$key_string .= $before . $key . $after;
		}
		
		return $key_string;

	}
	
	private function set_table_key()
	{
		
		$table_key = $this->build_key('keys_table');
		
		if(!defined('MEMCACHED_KEYS_TABLE')) {
			define('MEMCACHED_KEYS_TABLE', $table_key);
		}
		
	}
	
	
	 //*********************
	// FILE CACHE FUNCTIONS
	
	public function add_file($key, $value)
	{

		if ($this->use_file_system === FALSE)
		{
			return FALSE;
		}
		
		// don't attempt to continue if the cache root doesn't exist
		if (!file_exists(CACHE_ROOT))
		{
			return FALSE;	
		}
		
		$key	= $this->build_key($key, '_', '_');
		$file	= FALSE;
		
		if (is_writable(CACHE_ROOT))
		{
			$file = @file_put_contents(CACHE_ROOT . $key, serialize($value));
		}
		
		return ($file === TRUE);
		
	}
	
	public function get_file($key, $expiration)
	{
		
		if ($this->use_file_system === FALSE)
		{
			return FALSE;
		}
		
		// don't attempt to continue if the cache root doesn't exist
		if (!file_exists(CACHE_ROOT))
		{
			return FALSE;	
		}
		
		if (empty($expiration))
		{
			$expiration = $this->default_expiration;
		}
		
		$key = $this->build_key($key, '_', '_');

		$cache_file = CACHE_ROOT . $key;
		
		if (file_exists($cache_file))
		{
			
			// if the file has expired...
			if ((filemtime($cache_file) + $expiration) < time())
			{
				
				// delete it
				@unlink($cache_file);
				
				// and return false
				return FALSE;
				
			}
			
			return unserialize(file_get_contents($cache_file));
			
		}
		
		return FALSE;
	
	}
	
	public function flush_files()
	{
		
		// don't attempt to continue if the cache root doesn't exist
		if (!file_exists(CACHE_ROOT))
		{
			return FALSE;	
		}
			
		$handle = opendir(CACHE_ROOT);
		
		while (($file = readdir($handle)) !== FALSE)
		{
			@unlink(CACHE_ROOT . $file);
		}
		
		closedir($handle);
		
	}
	
}

// end of Cache.php
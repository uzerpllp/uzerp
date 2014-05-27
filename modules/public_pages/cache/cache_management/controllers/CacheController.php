<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CacheController extends Controller {

	protected $version = '$Revision: 1.5 $';

	public function __construct($module = null, $action = null) 
	{
		parent::__construct($module, $action);
	}

	public function index()
	{
		
		$flash	= Flash::Instance();
		$keys	= array();
		
		if (MEMCACHED_ENABLED)
		{

			// fetch the keys table from the cache
			$cache	= Cache::Instance();
			$keys	= $cache->get('keys_table');
		
			// failsafe, just incase keys isn't an array
			if (!is_array($keys))
			{
				$keys = array();
			}
			
			// sort the for easy viewing
			sort($keys);

		}
		else
		{
			$flash->addWarning("Memcached is disabled");
		}
		
		// output a message to warn of the cache root not existing
		if (!file_exists(CACHE_ROOT))
		{
			$flash->addWarning("Backup cache directory doesn't exist");	
		}
		
		// set sidebar
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'view_all_caches' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'index'
					),
					'tag' => 'view_all_caches'
				),
				'flush_cache' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'flush'
					),
					'tag' => 'flush_cache'
				)
			)
		);
					
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
		$this->view->set('keys', $keys);
		
	}
	
	public function view()
	{
		
		$flash = Flash::Instance();
		
		if (MEMCACHED_ENABLED)
		{

			// fetch the item from the cache
			$cache	= Cache::Instance();
			$item	= $cache->get($this->_data['id']);
		
		}
		else
		{
			$flash->addError("Cache is disabled");
			sendBack();
		}
		
		
		// make sure we've got something
		if ($item === FALSE)
		{
			$flash->addError("Cache item doesn't exist");
			sendBack();
		}
		
		// ammend output depending on the data type
		if (is_object($item) || is_array($item))
		{
			$item = print_r($item, TRUE);
		}
		else
		{
			$item = var_export($item, TRUE);
		}
		
		// set smarty variables
		$this->view->set('item', $item);
		$this->view->set('title', $this->_data['id']);
		
		// set sidebar
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'view_all_caches' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'index'
					),
					'tag'=>'view_all_caches'
				),
				'flush_cache' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'delete',
						'id'			=> $this->_data['id']
					),
					'tag' => 'delete_key'
				)
			)
		);
					
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	public function delete()
	{
		
		$flash = Flash::Instance();
		
		if (MEMCACHED_ENABLED)
		{
			
			// delete the key from the cache
			$cache = Cache::Instance();
		
			if($cache->delete($this->_data['id'])) {
				$flash->addMessage("Key successfully deleted");
			} else {
				$flash->addError("Deleting key failed");
			}
			
			sendTo($this->name,'index',$this->_modules);
			
		}
		else
		{
			$flash->addError('Cache is disabled');
			sendBack();			
		}
		
		
	}
	
	public function flush()
	{
		
		$flash = Flash::Instance();
		$cache = Cache::Instance();
		
		$cache->flush();
		
		if (MEMCACHED_ENABLED)
		{
			$flash->addMessage("Cache successfully flushed");
		}
		else
		{
			$flash->addWarning('Memcached is disabled');
			$flash->addMessage('Attempted to clear file cache');
		}
		
		sendBack();
		
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName('Cache');
	}

}

// end of CacheController.php
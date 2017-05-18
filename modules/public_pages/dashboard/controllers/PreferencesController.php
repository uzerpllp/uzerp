<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/**
 * Handles the displaying of the page called with no arguments
 */

class PreferencesController extends Controller
{
	
	protected $version = '$Revision: 1.10 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
	}
	
	private function getPreferenceClass($module)
	{
		// Does the user have permission for this module?
//		$accessObject = &AccessObject::Instance(EGS_USERNAME);
		# TODO: Redirect?
		$classname = ucfirst($module).'Preferences';
		
		return new $classname();
	}
	
	public function index()
	{
		// Cater for no module to edit.
		if (empty($this->_data['for_module']))
		{
			$this->_data['for_module'] = 'shared';
		}
		
		$sidebarList['shared'] = array(
					'tag' => 'Shared',
					'link' => array(
						'module' => 'dashboard',
						'controller' => 'preferences',
						'action' => 'index',
						'for_module' => 'shared'
					)
				);
		
		$accessObject =&AccessObject::Instance(EGS_USERNAME);

		$module = DataObjectFactory::Factory('ModuleObject');
		$modules = $module->getAll();

		$sidebar = new SidebarController($this->view);
		foreach ($modules as $module)
		{
			
			if(!class_exists(ucfirst($module).'Preferences'))
			{
				continue;
			}
			
			if ($accessObject->hasPermission($module,'preferences'))
			{
				$sidebarList[$module] = array(
					'tag' => ucfirst($module).' Preferences',
					'link' => array(
						'module' => 'dashboard',
						'controller' => 'preferences',
						'action' => 'index',
						'for_module' => $module
					)
				);
			}
		}
		
		if (empty($sidebarList))
		{
			$flash = Flash::Instance();
			$flash->addError('There are no preferences you can edit');
			sendTo('index','index',array('dashboard'));
		}
		else
		{
			$sidebar->addList(
				'Modules',
				$sidebarList
			);
		}
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$module = $this->getPreferenceClass($this->_data['for_module']);
		
		$this->view->set('templateCode', $module->generateTemplate());
		
		$this->view->set('page_title', $this->getPageName($this->_data['for_module'], 'Preferences for'));
		
	}
	
	public function save()
	{
		$module = $this->getPreferenceClass($this->_data['__moduleName']);
		
		$preferenceNames = $module->getPreferenceNames();
		
		$flash=Flash::Instance();
		
		$userPreferences = UserPreferences::instance();
		
		// FIXME: Validate incomming data against supplied values
		foreach($preferenceNames as $preferenceName)
		{
			if (isset($this->_data[$preferenceName]))
			{
				$pref = $module->getPreference($preferenceName);
				
				if(isset($pref['type'])&&$pref['type']=='numeric')
				{
				
					if(!is_numeric($this->_data[$preferenceName]))
					{
						$flash->addError($pref['display_name'].' must be numeric');
						continue;
					}
				}
				
				$userPreferences->setPreferenceValue(
					$preferenceName,
					$this->_data['__moduleName'],
					$this->_data[$preferenceName]
				);
			}
			else
			{
				$preference = $module->getPreference($preferenceName);
				switch ($preference['type'])
				{
					case 'checkbox':
						$userPreferences->setPreferenceValue(
							$preferenceName,
							$this->_data['__moduleName'],
							'off'
						);
						break;
					case 'select_multiple':
						$userPreferences->setPreferenceValue(
							$preferenceName,
							$this->_data['__moduleName'],
							array()
						);
						break;
				}
			}
		}
		
		$handled = $module->getHandledPreferences();
		
		foreach($handled as $name=>$preference)
		{
			if(!empty($this->_data[$name])&&isset($preference['callback']))
			{
				$callback = array($module,$preference['callback']);
				call_user_func($callback,$this->_data);
			}
		}

		
		// Do stuff.
		
		$flash->addMessage( prettify($this->_data['__moduleName']) .' preferences saved successfully');
		sendTo('', '', $this->_modules);
	}

	/* protected functions */
	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName(($base)?$base:$this->module,$action);
	}

}

// End of PreferencesController

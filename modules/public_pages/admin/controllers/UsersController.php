<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class UsersController extends printController {

	protected $version = '$Revision: 1.27 $';
	
	public function __construct($module = NULL, $action = NULL)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('User');
		$this->uses($this->_templateobject);
		
	}
	
	public function reset_passwords()
	{
		
		if (!isset($this->_data['users']))
		{
			$user	= DataObjectFactory::Factory('User');
			$cc		= new ConstraintChain();
			
			$cc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
			
			$results = $user->getAll($cc);
			$this->view->set('users', $results);
			
		}
		else
		{
			
			foreach ($this->_data['users'] as $username)
			{
				
				$user = DataObjectFactory::Factory('User');
				$user->load($username);
				
				$password = '';
				
				if (isset($this->_data['password']))
				{
					$password = $this->_data['password'];
				}
				
				$password = $user->setPassword($password);
				
				$message = "Your password for ".SERVER_ROOT." has been reset\n" .
						"Your username is {$user->username}\n" .
						"Your password is {$password}\n" .
						"Thank you";
				
				$subject = 'Password reset';
				
				$person = DataObjectFactory::Factory('Person');
				$person->load($user->person_id);
				
				$to			= $person->email->contactmethod;
				$headers	= 'From: '.get_config('ADMIN_EMAIL')."\r\n".'X-Mailer: PHP/' . phpversion();
				
				if ($to <> '')
				{
					mail($to, $subject, $message, $headers);
				}
				
			}

			sendBack();	
			
		}
		
	}

	public function index()
	{
		$flash = Flash::Instance();
		
		$errors = array();
		
		$s_data = array();
		
// Set context from calling module
		$this->setSearch('AdminSearch', 'Users', $s_data);
		
		$this->view->set('clickaction', 'view');
		
		parent::index(new UserCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new' => array(
					'link' => array(
						'module'		=> 'admin',
						'controller'	=> 'users',
						'action'		=> 'new'
					),
					'tag' => 'New User'
				),
				'view' => array(
					'link' => array(
						'module'		=> 'admin',
						'controller'	=> 'hasroles',
						'action'		=> 'index'
					),
					'tag' => 'Users by Role'
				),
				'reset_passwords' => array(
					'link' => array(
						'module'		=> 'admin',
						'controller'	=> 'users',
						'action'		=> 'reset_passwords'
					),
					'tag' => 'Reset Passwords'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}

	public function save()
	{
		
		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}
		
		$flash		= Flash::Instance();
		$errors 	= array();
		$user		= $this->_uses[$this->modeltype];
		$user_data	= $this->_data[$this->modeltype];
		$username	= $user_data['username'];
		$user		= $user->load($username);
		$new_user	= TRUE;
		
		if ($user !== FALSE)
		{
			
			$new_user = FALSE;
			
			if ($user_data['password'] == $user->password)
			{
				unset($this->_data[$this->modeltype]['password']);
			}
			
		}
		
		if (empty($user_data['lastcompanylogin']))
		{
			$this->_data[$this->modeltype]['lastcompanylogin'] = EGS_COMPANY_ID;
		}
		
		if (isset($user_data['debug_options']))
		{
			
			$debug = DebugOption::getUserOption($username);
			
			if (isset($user_data['debug_enabled']))
			{
				$this->_data['DebugOption']['username']		= $username;
				$this->_data['DebugOption']['company_id']	= EGS_COMPANY_ID;
				$this->_data['DebugOption']['options']		= $debug->setOptions($user_data['debug_options']);
			}
			else
			{
				
				if ($debug->isLoaded()) 
				{
					$debug->delete();
					unset($this->_data['DebugOption']);
				}
				
			}
			
		}
		else
		{
			unset($this->_data['DebugOption']);
		}
		
		if (!parent::save($this->modeltype))
		{
			
			if ($new_user)
			{
				$this->refresh();
			}
			else
			{
				$this->_data['username'] = $this->_data[$this->modeltype]['username'];
				$this->edit();
			}
			
			return FALSE;
			
		}
		
		if ($new_user)
		{
			
			$uca = Usercompanyaccess::Factory(
				array(
					'username'		=> $username,
					'company_id'	=> EGS_COMPANY_ID,
					'enabled'		=> TRUE
				), 
				$errors, 
				'Usercompanyaccess'
			);
			
			if ($uca)
			{
				
				if (!$uca->save())
				{
					$errors[] = 'Failed to save user company access';
				}
				
			}
			
			if (!$uca||count($errors))
			{
				$flash->addErrors($errors);
				sendTo($this->name, 'index', $this->_modules);
				
			}
			
			$user		= $this->saved_model;
			$password	= $user->setPassword($user_data['password']);
			
			if (!is_null($user->person_id))
			{
				$person = DataObjectFactory::Factory('Person');
				$person->load($user->person_id);
				$to = $person->email->contactmethod;
			}
			else
			{
				$to = '';
			}
			
			if (empty($to))
			{
				$to = $user->email;
			}
			
			if ($to <> '')
			{
				
				$message = "You have been created an account for ".SERVER_ROOT."\n" .
						"Your username is {$user->username}\n" .
						"Your password is {$password}\n" .
						"Thank you";
				$subject = 'New Account';
				$headers = 'From: '.get_config('ADMIN_EMAIL')."\r\n".'X-Mailer: PHP/' . phpversion();
				
				mail($to, $subject, $message, $headers);
				
			}
			
		}
		
		if (isset( $this->_data[$this->modeltype]['roles']) && is_array( $this->_data[$this->modeltype]['roles']))
		{
			$roles = $this->_data[$this->modeltype]['roles'];
			User::setRoles($user, $roles, $errors);
			$flash->addErrors($errors);
		}
		
		sendTo($this->name, 'index', $this->_modules);

	}

	public function saveroles()
	{
		
		$username	= $this->_data['username'];
		$role		= $this->_data['roles'];
		
		User::setRoles($username, $roles);
	
		sendTo($this->name, 'index', $this->_modules);
		
	}

	public function _new()
	{
		
		parent::_new();
		
		$roles = DataObjectFactory::Factory('Role');
		$this->view->set('roles', $roles->getAll());
		
		$sc = DataObjectFactory::Factory('Systemcompany');
		
		$companies = new SystemcompanyCollection($sc);
		$this->view->set('companies', $companies->getCompanies());

		$sc->load(EGS_COMPANY_ID);
		
		$current_people = $sc->getCurrentPeople();
		$assigned_users = $this->_uses[$this->modeltype]->getAssignedPeople();
		
		$this->view->set('people', array_diff_key($current_people, $assigned_users));
		
		$debug = DataObjectFactory::Factory('DebugOption');
		$this->view->set('debug_options', $debug->getEnumOptions('options'));
		
	}

	public function edit()
	{
		
		$id = $this->_data['username'];
		
		$this->_uses[$this->modeltype]->load($id);
		$user = $this->_uses[$this->modeltype];

		$this->view->set('current', $user->roles->getAssoc());
		
		$companies = new UsercompanyaccessCollection(DataObjectFactory::Factory('Usercompanyaccess'));
		
		$sh = new SearchHandler($companies, FALSE, FALSE);
		$sh->addConstraint(new Constraint('username','=', $id));
		$sh->setFields(array('id', 'usercompanyid'));
		$companies->load($sh);
		
		$this->view->set('selected_companies', $companies->getAssoc());
		$this->view->set('username', $id);
		$this->view->set('edit', TRUE);
		
		$this->_new();
		$this->setTemplateName('new');
		
		$debug = DebugOption::getUserOption($id);
		$this->view->set('debug_id', $debug->id);
		$this->view->set('selected_options', $debug->getOptions());
		
	}

	public function edit_preferences()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		$user = $this->_uses[$this->modeltype];
		
		// Cater for no module to edit.
		if (empty($this->_data['for_module'])) 
		{
			$this->_data['for_module'] = 'shared';
		}

		$classname	= ucfirst($this->_data['for_module']).'Preferences';
		$module		= new $classname(true, 'ManagedUserPreferences', $user->username);
		
		$this->view->set('templateCode', $module->generateTemplate());
		
		$this->view->set('username', $this->_data['username']);
		
		if (!is_null($user->person))
		{
			$name = $user->person;
		}
		else
		{
			$name = $user->username;
		}
		
		$this->view->set('page_title', $this->getPageName($name, 'Edit '.$this->_data['for_module'].' Preferences for '));
		
	}
	
	public function save_preferences()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$user = $this->_uses[$this->modeltype];
		
		$classname	= ucfirst($this->_data['__moduleName']).'Preferences';
		$module		= new $classname(true, 'ManagedUserPreferences', $user->username);
		
		$preferenceNames = $module->getPreferenceNames();
		
		$flash=Flash::Instance();
		
		$userPreferences = ManagedUserPreferences::instance($username);
		
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
		
		if (!is_null($user->person))
		{
			$name = $user->person;
		}
		else
		{
			$name = $user->username;
		}
		
		$flash->addMessage( prettify($this->_data['__moduleName']) .' preferences for '.$name.' saved successfully');
		sendTo($this->name, 'index', $this->_modules);

	}
	
	private function addSidebar($username)
	{
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'all'=>array(
					'link'=>array('module'=>'admin','controller'=>'users','action'=>'index'),
					'tag'=>'View all users'
				),				
				'spacer',		
				'edit'=>array(
					'link'=>array('module'=>'admin','controller'=>'users','action'=>'edit','username'=>$username),
					'tag'=>'Edit details for '.$username
				),
				'edit_preferences'=>array(
					'link'=>array('module'=>'admin','controller'=>'users','action'=>'edit_preferences','username'=>$username),
					'tag'=>'Edit preferences for '.$username
				),
				'spacer',		
				'delete'=>array(
					'link'=>array('module'=>'admin','controller'=>'users','action'=>'delete','username'=>$username),
					'tag'=>'delete '.$username
				)				
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}

	public function view()
	{
		
		if (isset($this->_data['username']))
		{
			
			$username	= $this->_data['username'];
			$res		= $this->_uses[$this->modeltype]->load($username);
			
			if ($res === FALSE)
			{
				sendBack();
			}
			
		}
		else
		{
			$this->dataError();
			sendBack();
		}
		
		$this->view->set('companies', $res->getCompanies());
		$this->view->set('roles', $res->getCompanyRoles());
		
		// Get preferences based on username
		$prefs = UserPreferences::getPreferencesClass($username);
		
		$uzlets = $prefs->getDashboardContents($username);
		
		// Manipulate and combine the available and selected uzlets
		// for display
		foreach ($uzlets['available'] as $modules=>$module)
		{
			foreach ($module as $detail)
			{
				if (isset($dashboard['available'][$modules]))
				{
					$dashboard['available'][$modules] += $detail;
				}
				else
				{
					$dashboard['available'][$modules] = $detail;
				}
			}
		}
		
		foreach ($uzlets['selected'] as $module=>$details)
		{
			foreach ($details as $selected)
			{
				$dashboard['available'][$module][$selected['name']] = $dashboard['current'][$module][$selected['name']] = $selected['title'];
			}
			
			asort($dashboard['available'][$module]);
		}
		
		ksort($dashboard['available']);
		
		$this->view->set('dashboard', $dashboard);
		
		$shared_prefs['shared']['items_per_page']		= $prefs->getPreferenceValue('items-per-page', 'shared');
		$shared_prefs['shared']['pdf_preview']			= $prefs->getPreferenceValue('pdf-preview', 'shared');
		$shared_prefs['shared']['pdf_browser-printing']	= $prefs->getPreferenceValue('pdf-browser-printing', 'shared');
		$shared_prefs['shared']['default_page']			= $prefs->getPreferenceValue('default_page', 'shared');
		
		$default_printer = $prefs->getPreferenceValue('default_printer', 'shared');
		$printers		 = $this->selectPrinters();
		
		if (isset($printers[$default_printer]))
		{
			$shared_prefs['shared']['default_printer'] = $printers[$default_printer];
		}
		else
		{
			$shared_prefs['shared']['default_printer'] = '';
		}
		
		$this->view->set('preferences', $shared_prefs);
		
		$this->addSidebar($username);
		
	}
	
}

// end of UsersController.php
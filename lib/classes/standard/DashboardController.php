<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DashboardController extends Controller
{

	protected $version = '$Revision: 1.31 $';
	
	protected $dashboard_module;
	
	function __construct($module=null,$view)
	{
		
		parent::__construct($module, $view);
		
		$dashboard_modules		= $this->_modules;
		$this->dashboard_module	= '';
		
		while (empty($this->dashboard_module) && count($dashboard_modules) > 0)
		{
			$this->dashboard_module = array_pop($dashboard_modules);
		}
		
	}

	public function index()
	{
		
		// dynamically generate the quick links from the permissions for the selected module 
		$eglet = new SimpleMenuEGlet(new SimpleRenderer);
		$eglet->setMenuData($this->dashboard_module, $system->pid);
		$eglet->setSmarty($this->view);
		
		if (count($eglet->getContents()) > 0)
		{
			$this->view->set('eglets', array('Quick Links'=>$eglet));
		}
		
		#$cache			= Cache::Instance();
		#$eglet_store	= $cache->get(array('eglet_store', $this->module));
		
		// ATTN: we're avoiding the cache because eglets are appearing (and not appearing)
		// where they should be. it seems the module grouping isn't actually working 
		 
		$eglet_store = FALSE;
		
		$ao	= &AccessObject::Instance(EGS_USERNAME);
		
		if (FALSE === $eglet_store)
		{
			
			// get user's uzlet preferences
			$prefs			= UserPreferences::Instance(EGS_USERNAME);
			$user_uzlets	= $prefs->getPreferenceValue('dashboard_contents', $this->dashboard_module);
			$user_uzlets	= (is_array($user_uzlets)?array_flip($user_uzlets):array());
			
			// discover what uzlet_id's belong to the module we're working with
			$uzlets = new UzletCollection();
				
			$sh = new SearchHandler($uzlets, FALSE);
			
			if (count($user_uzlets) > 0)
			{
				// get the uzlet details for the user's uzlet preferences
				$db = DB::Instance();
				$uzlet_names = array();
				foreach ($user_uzlets as $name=>$value)
				{
					$uzlet_names[] = $db->qstr($name);
				}
				$sh->addConstraint(new Constraint('name', 'in', '('.implode(',', $uzlet_names).')'));
			}
			else
			{
				// user preferences are not set, get the default(preset) uzlets for the module
				$sh->addConstraint(new Constraint('preset', 'is', TRUE));
				
				if ($this->dashboard_module == 'dashboard')
				{
					$sh->addConstraint(new Constraint('dashboard', 'is', TRUE));
					// only include uzlets for modules the user has access to
					foreach ($ao->permissions as $permission)
					{
						if ($permission['type'] == 'm' && !empty($permission['module_id']))
						{
							$modules[$permission['module_id']] = $permission['module_id'];
						}
					}
					
					if (count($modules) > 0)
					{
						$sh->addConstraint(new Constraint('module_id', 'in', '('.implode(',', $modules).')'));
					}
					else
					{
						$sh->addConstraint(new Constraint('module_id', '=', -1));
					}
				}
				else
				{
					$sh->addConstraint(new Constraint('module', '=', $this->dashboard_module));
				}
			}
			
			$sh->addConstraint(new Constraint('enabled', 'is', TRUE));
			
			$rows = $uzlets->load($sh, null, RETURN_ROWS);
			$uzlets=array();

			if (!empty($rows))
			{
				if (empty($user_uzlets))
				{
					// no user uzlet preferences so set uzlets to the default(preset) for the module
					foreach ($rows as $uzlet)
					{
						$uzlets[$uzlet['name']] = $uzlet;
					}
				}
				else
				{
					// Preserve order of user's uzlet preferences
					foreach ($rows as $uzlet)
					{
						if (isset($user_uzlets[$uzlet['name']]))
						{
							$user_uzlets[$uzlet['name']] = $uzlet;
						}
					}
					// Remove any user's uzlet preferences that are no longer valid
					foreach ($user_uzlets as $name=>$uzlet)
					{
						if (!is_array($uzlet))
						{
							unset($user_uzlets[$name]);
						}
					}
					$uzlets = $user_uzlets;
				}
			}
			
			$this->view->set('uzlets', $uzlets);
		}
		
		showtime('pre-pop');
		
		$this->view->set('can_edit', $ao->can_manage_uzlets());

	}
	
	public function edit()
	{
		
		debug('DashboardController::edit');
		
		// Get user name
		$username = $this->getUser();
		
		// Get preferences based on username
		$prefs = UserPreferences::getPreferencesClass($username);
		
		$uzlets = $prefs->getDashboardContents($username, $this->dashboard_module, $this->_data['pid']);
		
		$this->view->set('module_count', count($uzlets['available'][$this->dashboard_module]));
		$this->view->set('selected', $uzlets['selected'][$this->dashboard_module]);
		$this->view->set('available', $uzlets['available'][$this->dashboard_module]);
		$this->view->set('username', $username);
		
		//same template for all modules
		$this->setTemplateName('edit_dashboard');
		
	}
	
	function save()
	{
		
		if (isset($this->_data['eglets'])&&count($this->_data['eglets'])>0)
		{
			
			$prefs = UserPreferences::getPreferencesClass($this->getUser());
			
			$prefs->setPreferenceValue('dashboard_contents',$this->dashboard_module,$this->_data['eglets']);
			
			$flash = Flash::Instance();
			$flash->addMessage('Dashboard preferences set');

			// ATTN: see above for the reason for this commenting
			
#			$cache = Cache::Instance();
#			$cache->delete(array('eglet_store', $this->module));
			
		}
		
		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		
	}

	function refreshEglet()
	{
		
		// used with scripts.js ajaxEglet to refresh an eglet
		// probably a better way of doing this
		$uzlet_model = DataObjectFactory::Factory('uzlet');
		
		if (!empty($this->_data['uzletid']))
		{
			$uzlet_model->load($this->_data['uzletid']);
		}
		elseif (isset($this->_data['uzlet']))
		{
			$uzlet_model->loadBy(array('name', 'usercompanyid'), array($this->_data['uzlet'], EGS_COMPANY_ID));
			$className		= $this->_data['uzlet'];
		}
		
		$calls = array();
		
		if ($uzlet_model->isLoaded())
		{
			$this->view->set('uzlet_title', $uzlet_model->title);
			$this->view->set('uzletid', $uzlet_model->id);
			
			$className		= ((is_null($uzlet_model->uses)) ? $uzlet_model->name : $uzlet_model->uses);
			
			// check uzlet for a list of method=>argument(s) pairs that will be called
			foreach ($uzlet_model->calls->getContents() as $key => $value) 
			{
				$arg = json_decode($value->arg, TRUE);
				
				if (is_null($arg))
				{
					// json decode didn't work, either malformed or string
					$arg = $value->arg;
				}
				
				$calls[$value->func] = $arg;
			}	
		}
		
		$uzlet			= new $className($className::getRenderer());
		
		$uzlet->setSmarty($this->view);
		
		if (!empty($calls))
		{
			// execute each defined uzlet->method call
			foreach($calls as $func => $arg)
			{
				if(is_array($arg))
				{
					call_user_func_array(array($uzlet, $func), $arg);
				}
				else
				{
					call_user_func(array($uzlet, $func), $arg);
				}
			}
				
		}
		
		$this->setTemplateName('inline_dashboard');
		
		if ($uzlet->isPaging())
		{
			$paging = new UZletPaging($uzlet, $this->view);
			
			$paging->setPage(empty($this->_data['page'])?1:$this->_data['page']);
			
			$this->view->set('uzlet', $paging);
		}
		else
		{
			$this->view->set('uzlet', $uzlet);
			
			$uzlet->populate();
		}
		
		$this->view->set('uzlet_class', $uzlet->getClassName());
		
	}
	
	/*
	 * Protected Functions
	 */
	protected function getPageName($base = null, $type = null)
	{
		
		if (empty($this->dashboard_module))
		{
			return parent::getPageName($base, $type);
		}
		else
		{
			return $this->dashboard_module;
		}
		
	}
	
	/*
	 * Private Functions
	 */
	private function getUser()
	{
		return (empty($this->_data['username']))?EGS_USERNAME:$this->_data['username'];
	}
	
}

// end of DashboardController.php
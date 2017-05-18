<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PoauthlimitsController extends Controller {

	protected $version = '$Revision: 1.15 $';

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('POAuthLimit');
		
		$this->uses($this->_templateobject);
	}

	public function index()
	{
		$this->view->set('clickaction', 'view');

		$errors = array();
	
		$s_data = array();

// Set context from calling module
		if (isset($this->_data['username']))
		{
			$s_data['username'] = $this->_data['username'];
		}
		
		if (isset($this->_data['glcentre_id']))
		{
			$s_data['glcentre_id'] = $this->_data['glcentre_id'];
		}
		
		$this->setSearch('poauthlimitsSearch', 'useDefault', $s_data);
		
		$username	 = $this->search->getValue('username');
		$glcentre_id = $this->search->getValue('glcentre_id');
		
		parent::index(new POAuthLimitCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$actions = array();

		$actions['new']=array(
					'tag'=>'New Authorisation Limit',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 )
					);
					
		if (isset($s_data['username']) && $s_data['username']!='')
		{
			$actions['forperson']=array(
						'tag'=>'New Authorisation Limit for this person',			
						'link'=>array('modules'=>$this->_modules
									 ,'controller'=>$this->name
									 ,'action'=>'new'
									 ,'username'=>$username
									 )
					);
		}
		
		if (isset($s_data['glcentre_id']) && $s_data['glcentre_id']!='0')
		{
			$actions['foraccount']=array(
						'tag'=>'New Authorisation Limit for this GL Centre',
						'link'=>array('modules'=>$this->_modules
									 ,'controller'=>$this->name
									 ,'action'=>'new'
									 ,'glcentre_id'=>$s_data['glcentre_id']
									 )
					);
		}
		
		$sidebar->addList(
			'Actions',
			$actions
		);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
	}

	public function delete()
	{
		$flash = Flash::Instance();
		
		parent::delete($this->modeltype);
		
		sendTo($this->name, 'index', $this->_modules);
	}
	
	public function view()
	{
		
		parent::view();
		
		$auth_accounts	= array();
		$poauthlimit	= $this->_uses[$this->modeltype];
		
		// build an array of the auth accounts, with the account number as the key
		foreach ($poauthlimit->authaccounts as $auth_account)
		{
			
			// build the key
			$parts	= explode('-', $auth_account->glaccount);
			$key	= trim($parts[0]);
			
			// output as array
			$auth_accounts[$key] = $auth_account->glaccount;
			
		}
		
		// sort the array by it's key
		// this will allow the output to be in numerical order
		
		ksort($auth_accounts);
		
		$this->view->set('authaccounts', $auth_accounts);
		$this->view->set('no_ordering', true);
		
	}
	
	public function _new()
	{
		
		parent::_new();
		
		$poauthlimit=$this->_uses[$this->modeltype];
		
		$default_glcentre_id	= '';
		$default_username		= '';
		
		if (isset($_POST[$this->modeltype]))
		{
			
			// must be re-entering following form error so preserve form data
			$default_glcentre_id	= $_POST[$this->modeltype]['glcentre_id'];
			$default_username		= $_POST[$this->modeltype]['username'];
				
		}

// Set the username
		$people = DataObjectFactory::Factory('Usercompanyaccess');
		$people->identifierField = 'username';
		$people->idField		 = 'username';
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
		
		$peoplelist = $people->getAll($cc);
		
		if (empty($default_username))
		{
			
			// No error - set defaults depending on whether an edit or new
			if (isset($this->_data['username']))
			{
				
				$peoplelist			= array($this->_data['username'] => $this->_data['username']);
				$default_username	= $this->_data['username'];
				
			}
			elseif ($poauthlimit->isLoaded())
			{
				
				$peoplelist			= array($poauthlimit->username => $poauthlimit->username);
				$default_username	= $poauthlimit->username;
				
			}
			else
			{
				
				ksort($peoplelist);
				$default_username = current($peoplelist);
				
			}
		}
		
		$this->view->set('people',$peoplelist);

		// Set the centre
		if (empty($default_glcentre_id))
		{
			
			if (isset($this->_data['glcentre_id']))
			{
				$default_glcentre_id=$this->_data['glcentre_id'];
			}
			elseif ($poauthlimit->isLoaded())
			{
				$default_glcentre_id=$poauthlimit->glcentre_id;
			}
			
		}
		
		$gl_centres = $this->getCentres($default_username, $default_glcentre_id);
		
		if (empty($default_glcentre_id))
		{
			$default_glcentre_id = key($gl_centres);
		}
		
		// Set the accounts list based on the centre
		$this->view->set('glaccounts', $this->getAccounts($default_glcentre_id));

		foreach ($this->_modules as $key=>$value)
		{
			$modules[]=$key.'='.$value;
		}
		
		$link = implode('&', $modules) . '&controller=' . $this->name . '&action=show_auth_accounts';
		$this->view->set('link', $link);
		
		$selected_account_ids = array();
		
		if ($poauthlimit->isLoaded())
		{
			
			$authaccounts = new POAuthAccountCollection();
			$sh=new SearchHandler($authaccounts, false);
			$sh->addConstraint(new Constraint('po_auth_limit_id', '=', $poauthlimit->id));
			$authaccounts->load($sh);
			
			foreach ($authaccounts as $authaccount) {
				$selected_account_ids[$authaccount->glaccount_id] = true;
			}
			
		}
		
		$accounts = new GLAccountCollection();
		
		if (!empty($selected_account_ids))
		{
			$sh = new SearchHandler($accounts, false);
			$sh->addConstraint(new Constraint('id', 'in', '('.implode(',', array_keys($selected_account_ids)).')'));
			$accounts->load($sh);
		}
		
		foreach ($accounts as $account)
		{
			$selected_accounts[$account->id] = $account->account . ' - ' . $account->description;
		}
		
		$this->view->set('selected_accounts', $selected_accounts);
		
	}
	
	public function show_auth_accounts ()
	{
		
		if (isset($this->_data['id']))
		{
			$params=explode('=', $this->_data['id']);
		}
		else
		{
			$params = array();
		}
		
		$linkdata			= SESSION::Instance();
		$selected_account	= array();
		
		if (!empty($params))
		{
			if (isset($selected_account[$params[0]]))
			{
				if (strtolower($params[1]) == 'false')
				{
					unset($selected_account[$params[0]]);
				}
			}
			elseif (strtolower($params[1]) == 'true')
			{
				$selected_account[$params[0]] = true;
			}
		}
		
		$selected_accounts = new GLAccountCollection();
		
		if (!empty($selected_account))
		{
			$sh = new SearchHandler($selected_accounts, false);
			
			$sh->addConstraint(new Constraint('id', 'in', '('.implode(',',array_keys($selected_account)).')'));
			
			$selected_accounts->load($sh);
		}
		
		$this->view->set('selected_accounts', $selected_accounts);
		
	}
	
	public function save()
	{
		
		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}
		
		$flash	= Flash::Instance();
		
		$db		= DB::Instance();
		
		$errors	= array();
		
		$db->StartTrans();
		
		$data = $this->_data[$this->modeltype];
		
		if ($data['order_limit'] <= 0)
		{
			$errors['order_limit'] = 'Order Limit must be greater than zero';
		}
		elseif (!empty($data['id']))
		{
			
			// delete all the current auth accounts, start with a clean slate
			$authLimit = $this->_templateobject;
			$authLimit->load($data['id']);
			
			if ($authLimit->isLoaded())
			{
				foreach ($authLimit->authAccounts as $authAccount)
				{
					$authAccount->delete();
				}
			}
			
		}
		
		if (count($errors) === 0 && parent::save('POAuthLimit', null, $errors))
		{
			
			// generate the selected accounts array, data from form is pipe delimited
			$selected_accounts	= explode('|', $this->_data['POAuthLimit']['selected_accounts']);
			
			$auth_id	= $this->saved_model->id;
			$data		= array();
			
			if (!empty($selected_accounts))
			{
				
				foreach ($selected_accounts as $key=>$account)
				{
					
					$data['po_auth_limit_id']	= $auth_id;
					$data['glaccount_id']		= $account;
					
					$po_auth_account = DataObject::Factory($data, $errors, 'POAuthAccount');
					
					if (!$po_auth_account || !$po_auth_account->save())
					{
						$flash->addError('Failed to save Account Authorisation');
						break;
					}
					
				}
				
			}
			else
			{
				$errors[] = 'No accounts selected';
			}
			
			if (count($errors)==0 && $db->CompleteTrans())
			{
				$flash->addMessage('Authorisation Limit saved');
				sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
			}
			
		}
		$db->FailTrans();
		$db->CompleteTrans();
		$flash->addErrors($errors);
		$this->refresh();

	}

	public function getAccounts($_glcentre_id = '')
	{
		
		// Used by Ajax to return Account list after selecting the Centre
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['glcentre_id'])) { $_glcentre_id = $this->_data['glcentre_id']; }
		}
		
		$centre = DataObjectFactory::Factory('GLCentre');
		$centre->load($_glcentre_id);
		$accounts = $centre->accounts;
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('glaccounts',$accounts);
			unset($_SESSION['selectedauthaccounts']);
		}
		else
		{
			return $accounts;
		}

	}

	public function getCentres($_username = '', $_glcentre_id = '')
	{
// Used by Ajax to return Account list after selecting the Centre
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['username'])) { $_username = $this->_data['username']; }
			if(!empty($this->_data['glcentre_id'])) { $_glcentre_id=$this->_data['glcentre_id']; }
		}
		
		if (!empty($_glcentre_id))
		{
			$gl_centre = DataObjectFactory::Factory('GLCentre');
			
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint('id', '=', $_glcentre_id));
			
			$gl_centres=$gl_centre->getAll($cc);
		}
		else
		{
			$gl_centres = POAuthLimit::getUnassignedCentres($_username);
		}
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('options', $gl_centres);
			$this->setTemplateName('select_options');
		}
		else
		{
			$this->view->set('gl_centres', $gl_centres);
			return $gl_centres;
		}
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName('PO Authorisation Limits');
	}

}

// End of PoauthlimitsController

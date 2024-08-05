<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SystemcompanysController extends Controller
{

	protected $version = '$Revision: 1.18 $';

	protected $_templateobject;

	public function __construct($module = NULL, $action = NULL)
	{

		parent::__construct($module, $action);

		$this->_templateobject = new Systemcompany();
		$this->uses($this->_templateobject);

	}

	public function _new()
	{

		parent::_new();

		$permissions = new PermissionCollection(new Permission);

		$sh=new SearchHandler($permissions, FALSE);
		$sh->addConstraint(new Constraint('parent_id', 'is', 'NULL'));
		$sh->setOrderby('title');

		$permissions->load($sh);

		$systemcompany = $this->_uses[$this->modeltype];

		if ($systemcompany->isLoaded())
		{

			$companypermissions = new CompanypermissionCollection(new Companypermission);
			$checked = $companypermissions->getPermissionIDs($systemcompany->id);

			$this->view->set('checked',$checked);

			$debug=DebugOption::getCompanyOption($systemcompany->id);

			$this->view->set('debug_id', $debug->id);
			$this->view->set('selected_options', $debug->getOptions());

			foreach ($permissions as $permission)
			{

				$permission->setAdditional('permissions');

				if (isset($checked[$permission->id]))
				{
					$permission->permissions = TRUE;
				}
				else
				{
					$permission->permissions = FALSE;
				}

			}

		}

		$this->view->set('permissions', $permissions);

		$debug = new DebugOption();
		$this->view->set('debug_options', $debug->getEnumOptions('options'));

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$this->view->set('clickaction', 'view');

		parent::index(new SystemcompanyCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new' => array(
					'link' => array(
						'module'		=>'system_admin',
						'controller'	=>'systemcompanys',
						'action'		=>'new'
					),
					'tag' => 'new_system_company'
				)
			)
		);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);

	}

	public function delete($modelName = null)
	{
		$flash = Flash::Instance();
		parent::delete($this->modeltype);
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? NULL);
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) :void
	{ 

		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}

		$this->loadData();
		$systemcompany = $this->_uses[$this->modeltype];

		$db = DB::Instance();
		$db->StartTrans();

		$errors	= array();
		$flash	= Flash::Instance();

		$data = $this->_data[$this->modeltype];

		if (!isset($data['id']) || empty($data['id']))
		{

			$company = DataObject::Factory($data, $errors, 'Company');

			if (count($errors) > 0 || !$company->save())
			{
				$errors[] = 'Failed to create Company';
				$db->FailTrans();
			}
			else
			{
				$data['company_id'] = $company->id;
			}
		}

		if (isset($data['debug_options']) && isset($data['id']))
		{

			$debug = DebugOption::getCompanyOption($data['id']);

			if (isset($data['debug_enabled']))
			{
				$data['DebugOption']['company_id']	= $data['id'];
				$data['DebugOption']['options']		= $debug->setOptions($data['debug_options']);
			}
			else
			{

				if ($debug->isLoaded())
				{
					$debug->delete();
				}

				unset($data['DebugOption']);

			}

		}
		else
		{
			unset($data['DebugOption']);
		}

		if (isset($data['delete_logo']))
		{
			if ($this->delete_logo($systemcompany->logo_file_id, $errors))
			{
				$data['logo_file_id'] = NULL;
			}
			else
			{
				$errors[] = 'Error deleting Logo image';
			}
		}

		if (!empty($_FILES['file']['name']))
		{
			// Need to upload file before checking
			$file = File::Factory($_FILES['file'], $errors, DataObjectFactory::Factory('File'));

			if ($file->save())
			{
				if (!is_null($systemcompany->logo_file_id))
				{
					$old_file = DataObjectFactory::Factory('File');

					if (!$this->delete_logo($systemcompany->logo_file_id, $errors))
					{
						$errors[] = 'Error replacing Logo image';
					}
				}

				$data['logo_file_id'] = $file->id;
			}
			else
			{
				$errors[] = 'Error loading Logo image';
			}
		}
		if (count($errors) == 0)
		{

			if (!parent::save($this->modeltype, $data, $errors))
			{
				$errors[] = 'Failed to save System Company';
				$db->FailTrans();
			}

		}

		$db->CompleteTrans();

		if (count($errors) > 0)
		{

			$flash->addErrors($errors);

			if (isset($data['id']))
			{
				$this->_data['id'] = $data['id'];
			}

			$this->refresh();

		}
		else
		{
			sendTo($this->name, 'index', $this->_modules);
		}

	}

	public function view()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$systemcompany = $this->_uses[$this->modeltype];
		$this->addSidebar($systemcompany);

		$company = new Company();
		$company->load($systemcompany->company_id, TRUE);

		$this->view->set('company', $company);

		$permissions = new CompanypermissionCollection(new Companypermission);
		$permissions->getPermissions($systemcompany->id);

		$this->view->set('permissions', $permissions);
		$this->view->set('page_title', $this->getPageName('system_company'));

	}

	public function view_current ()
	{
		$this->_data['id'] = EGS_COMPANY_ID;

		$this->_templateName = $this->getTemplateName('view');
		$this->view();	
	}

	/*
	 * Private Functions
	 */
	private function addSidebar($systemcompany)
	{

		$sidebar = new SidebarController($this->view);


		$sidebarlist['all'] = array(
			'link' => array(
				'module'		=> 'system_admin',
				'controller'	=> 'Systemcompanys',
				'action'		=> 'index'
			),
			'tag' => 'View All System Companies'
		);

		$sidebarlist['new'] = array(
			'link' => array(
				'module'		=>'system_admin',
				'controller'	=>'Systemcompanys',
				'action'		=>'new'
			),
			'tag' => 'new_system_company'
		);

		$sidebar->addList('System Companies', $sidebarlist);

		$sidebarlist = array(
			'edit' => array(
				'link' => array(
					'module'		=> 'system_admin',
					'controller'	=> 'Systemcompanys',
					'action'		=> 'edit',
					'id'			=> $systemcompany->id
				),
				'tag' => 'Edit '.$systemcompany->company
			)
		);

		$sidebar->addList('Actions', $sidebarlist);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);

	}

	private function delete_logo($_logo_file_id, &$errors = array())
	{
		$file = DataObjectFactory::Factory('File');

		return $file->delete($_logo_file_id, $errors);
	}

	/*
	 * Protected Functions
	 */
	protected function getPageName($base = NULL, $type = NULL)
	{
		return parent::getPageName((empty($base) ? 'system_companies' : $base), $type);
	}

}

// end of SystemcompanysController.php

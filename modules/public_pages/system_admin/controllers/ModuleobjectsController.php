<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class ModuleobjectsController extends Controller {

	protected $version = '$Revision: 1.27 $';

	protected $_templateobject;

	private $components = array(
		'controllers'	=>'C',
		'eglets'		=>'E',
		'implementations'		=>'M',
		'models'		=>'M',
		'reports'		=>'R',
		'templates'		=>'T',
	);

	public function __construct($module = null, $action = null)
	{

		parent::__construct($module, $action);

		$this->_templateobject = new ModuleObject();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$errors = array();

		$s_data = array();

		// Set context from calling module
		$this->setSearch('modulesSearch', 'useDefault', $s_data);

		$moduleobjects	= new ModuleObjectCollection($this->_templateobject);
		$sh				= $this->setSearchHandler($moduleobjects);

		parent::index(new ModuleObjectCollection($this->_templateobject), $sh);

		$this->view->set('clickaction', 'view');

		$sidebar = new SidebarController($this->view);

		$sidebarlist['new'] = array(
			'link' => array(
				'modules'		=> $this->_modules,
				'controller'	=> $this->name,
				'action'		=> 'new'
			),
			'tag'=>'New'
		);

		$sidebar->addList('Actions', $sidebarlist);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar' ,$sidebar);

	}

	public function delete($modelName = null)
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$moduleobject = $this->_uses[$this->modeltype];

		$db = DB::Instance();
		$db->StartTrans();

		if ($moduleobject->isLoaded())
		{

			$modulename		= $moduleobject->name;
			$modulelocation	= FILE_ROOT . $moduleobject->location;

			if ($moduleobject->enabled && !$moduleobject->disable())
			{
				$errors[] = 'Failed to disable module';
			}

			if ($moduleobject->registered && !$moduleobject->unregister())
			{
				$errors[] = 'Failed to unregister module';
			}

			if (!$moduleobject->delete())
			{
				$errors[] = 'Failed to delete module';
			}
			else
			{

				$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulelocation), RecursiveIteratorIterator::CHILD_FIRST);

				for ($dir->rewind(); $dir->valid(); $dir->next())
				{

				    if ($dir->isDir())
				    {
				        rmdir($dir->getPathname());
				    }
				    else
				    {
			    	    unlink($dir->getPathname());
				    }

				}

				rmdir($modulelocation);

			}

		}
		else
		{
			$errors[] = 'Cannot find module';
		}

		$flash = Flash::Instance();

		if (count($errors) > 0)
		{

			$db->FailTrans();
			$flash->addErrors($errors);

			if (isset($this->_data['id']))
			{
				$db->CompleteTrans();
				sendTo($this->name, 'view', $this->_modules, array('id' => $this->_data['id']));
			}

		}
		else
		{
			$flash->addMessage('Module ' . $modulename . ' deleted OK');
		}

		$db->CompleteTrans();
		sendTo($this->name, 'index', $this->_modules);

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}

		$errors	= array();
		$flash	= Flash::Instance();

		$data = $this->_data[$this->modeltype];

		if (empty($data['location']) || (!file_exists($data['location']) && !is_dir($data['location'])))
		{
			$errors[] = 'Cannot find module';
		}


		if (count($errors) === 0 && parent::save($this->modeltype, '', $errors))
		{
			$id = $this->saved_model->idField;
			sendTo($this->name, 'view', $this->_modules, array($id=>$this->saved_model->{$id}));
		}

		$flash->addErrors($errors);
		$flash->addError('Error saving module');
		$this->refresh();

	}

	public function view()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$flash		= Flash::Instance();
		$module		= $this->_uses[$this->modeltype];
		$sidebar	= new SidebarController($this->view);

		$sidebarlist['all'] = array(
			'link' => array(
				'modules'		=> $this->_modules,
				'controller'	=> $this->name,
				'action'		=> 'index'
			),
			'tag' => 'View All Modules'
		);

		$sidebarlist['edit'] = array(
			'link' => array(
				'modules'		=> $this->_modules,
				'controller'	=> $this->name,
				'action'		=> 'edit',
				'id'			=> $module->id
			),
			'tag' => 'Edit'
		);

		if ($module->registered == 't')
		{

			if ($module->enabled == 't')
			{

				$sidebarlist['disable'] = array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'disable',
						'id'			=> $module->id
					),
					'tag'=>'Disable'
				);

			}
			else
			{

				$sidebarlist['enable'] = array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'enable',
						'id'			=> $module->id
					),
					'tag' => 'Enable'
				);

			}

			$sidebarlist['unregister'] = array(
				'link' => array(
					'modules'		=> $this->_modules,
					'controller'	=> $this->name,
					'action'		=> 'unregister',
					'id'			=> $module->id
				),
				'tag' => 'Unregister'
			);

		}

		$sidebarlist['delete'] = array(
			'link' => array(
				'modules'		=> $this->_modules,
				'controller'	=> $this->name,
				'action'		=> 'delete',
				'id'			=> $module->id
			),
			'tag' => 'Delete'
		);


		$sidebar->addList('Actions', $sidebarlist);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);

		if (!is_null($module->location))
		{

			$components = $this->buildDirTree($module->location.DIRECTORY_SEPARATOR, $this->_data['id']);

			// compontents will be false if the module location doesn't exist
			if ($components !== FALSE)
			{
				$this->view->set('components', $components);
			}
			else
			{
				$flash->addError("The module location does not exist");
			}

		}

		$this->view->set('module_id', $this->_data['id']);
		$this->view->set('permissions_tree', $this->getTemplateName('permissions_tree'));

	}

	public function save_permissions()
	{

		if (!$this->checkParams(array('ModuleComponent','ModuleObject')))
		{
			sendBack();
		}

		if (!$this->loadData())
		{
			sendBack();
		}

		$moduleobject	= $this->_uses[$this->modeltype];
		$idField		= $moduleobject->idField;
		$idValue		= $moduleobject->{$moduleobject->idField};

		$errors	= array();
		$flash	= Flash::Instance();

		$db = DB::Instance();
		$db->Debug();
		$db->StartTrans();

		$components			= $this->_data['ModuleComponent'];
		$current_components	= array();

		foreach ($components as $key => $component)
		{

			// delete any registered components that are de-registered
			if (!isset($component['register']))
			{

				if (!empty($component['id']))
				{
					$modulecomponent = new ModuleComponent();
					$modulecomponent->delete($component['id']);
				}

				unset($components[$key]);

			}
			else
			{

				// create list of current registered components
				if (!empty($component['id']))
				{
					$current_components[$component['id']]=$key;
				}

			}

		}

		// delete any entries that no longer exist in the file system
		foreach ($moduleobject->module_components as $module_component)
		{

			if (!isset($current_components[$module_component->id]))
			{
				$module_component->delete();
			}

		}

		foreach ($components as $data)
		{

			$component = DataObject::Factory($data, $errors, 'ModuleComponent');

			if (!$component || !$component->save())
			{
				$errors[] = 'Failed to save components';
				break;
			}

		}

		if (count($errors) > 0)
		{

			$flash->addErrors($errors);
			$db->FailTrans();
			$db->CompleteTrans();

			$this->_data['id'] = $idValue;
			$this->refresh();

			return;

		}
		else
		{
			$moduleobject->update($idValue, 'registered', true);
			$flash->addMessage('components added');
		}

		$db->CompleteTrans();
		sendTo($this->name, 'index', $this->_modules);

	}

	public function disable()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$moduleobject = $this->_uses[$this->modeltype];

		$errors	= array();
		$flash	= Flash::Instance();

		$db = DB::Instance();
		$db->StartTrans();

		if ($moduleobject->isLoaded() && !$moduleobject->disable())
		{
			$errors[] = 'Failed to disable module';
		}

		if (count($errors) > 0)
		{

			$flash->addErrors($errors);
			$db->FailTrans();
			$db->CompleteTrans();

			$this->_data['id'] = $moduleobject->id;
			$this->refresh();
			$this->setTemplateName('view');

		}
		else
		{
			$db->CompleteTrans();
			sendTo($this->name, 'index', $this->_modules);
		}

	}

	public function enable()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$moduleobject = $this->_uses[$this->modeltype];

		$errors	= array();
		$flash	= Flash::Instance();

		$db = DB::Instance();
		$db->StartTrans();

		/*
		 * DISABLED, YAML NO LONGER EXISTS
		 *
			$menu_options = $this->getMenuActions($moduleobject->location);

			if (!$moduleobject->enable($errors, $menu_options))
			{
				$errors[] = 'Failed to enable module';
			}
		*/

		if (count($errors) > 0)
		{

			$flash->addErrors($errors);
			$db->FailTrans();
			$db->CompleteTrans();

			sendTo(
				$this->name,
				'view',
				$this->_modules,
				array('id' => $moduleobject->id)
			);

		}
		else
		{
			$db->CompleteTrans();
			sendTo($this->name, 'index', $this->_modules);
		}

	}

	public function unregister()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$moduleobject = $this->_uses[$this->modeltype];

		$errors	= array();
		$flash	= Flash::Instance();

		$db = DB::Instance();
		$db->StartTrans();

		if ($moduleobject->isLoaded() && !$moduleobject->disable())
		{
			$errors[] = 'Failed to disable module';
		}

		if ($moduleobject->isLoaded() && !$moduleobject->unregister())
		{
			$errors[] = 'Failed to unregister module';
		}

		if (count($errors) > 0)
		{

			$flash->addErrors($errors);
			$db->FailTrans();
			$db->CompleteTrans();

			$this->_data['id'] = $moduleobject->id;
			$this->refresh();
			$this->setTemplateName('view');

		}
		else
		{
			$db->CompleteTrans();
			sendTo($this->name, 'index', $this->_modules);
		}

	}

	private function buildDirTree($mydatapath, $module_id, $type = '', $parent_name = '')
	{

		if (file_exists(FILE_ROOT . $mydatapath))
		{

			$files	= array();
			$mydata	= dir(FILE_ROOT.$mydatapath);

			while (($current = $mydata->read()) !== false)
			{

				if (substr($current, 0, 1) != '.' && $current != 'CVS')
				{

					if (is_dir($mydatapath . $current))
					{

						$module = new ModuleObject();
						$module->loadBy('name', $current);

						if (!$module->isLoaded())
						{

							if ($type != 'T')
							{
								$current_type = (isset($this->components[$current]))?$this->components[$current]:$type;
							}
							else
							{
								$current_type = $type;
							}

							$files[$current] = $this->buildDirTree($mydatapath . $current . DIRECTORY_SEPARATOR, $module_id, $current_type, $current);

						}

					}
					elseif (is_file($mydatapath.$current))
					{

						if ($type == 'C')
						{
							$name = strtolower(str_replace('.php','',$current));
						}
						else
						{

							// ATTN: switch (get_file_extension($current)) {

							if (substr($current, -4) == '.css')
							{
								$type = 'S';
							}
							if (substr($current, -3) == '.js')
							{
								$type = 'J';
							}
							if (substr($current, -4)=='.yml')
							{
								$type = 'Y';
							}
							if (empty($type) && substr($current, -4)=='.php')
							{
								$type='M';
							}

							$name = strtolower(substr_replace($current, '', strrpos($current, '.')));

						}

						$component = new ModuleComponent();
						$component->loadBy(array('name', 'type', 'module_id'), array($name, $type, $module_id));
						$component->addField('register', new DataField('register', false));

						if (!$component->isLoaded())
						{
							$component->name		= $name;
							$component->type		= $type;
							$component->module_id	= $module_id;
						}
						else
						{
							$component->register = true;
						}

						if ($type == 'T')
						{
							$component->controller = $parent_name;
						}
						else
						{
							$component->controller=null;
						}
						$component->location		= str_replace(FILE_ROOT, '', $mydatapath.$current);
						$files[$component->name]	= $component;

					}

				}

			}

			ksort($files);

			$mydata->close();
			return $files;

		}
		else
		{
			return FALSE;
		}

	}

	public function getPathName($_id = '')
	{

		// Used by Ajax to return Currency after selecting the Supplier

		if (isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}

		if (!empty($_id))
		{
			$system		= System::Instance();
			$pathname	= str_replace(FILE_ROOT, '', $system->findModulePath(FILE_ROOT.'modules', $_id));
		}
		else
		{
			$pathname = '';
		}

		if (isset($this->_data['ajax']))
		{
			$this->view->set('value', $pathname);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $pathname;
		}

	}

	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'Modules':$base), $action);
	}

}

// end of ModuleobjectsController.php
<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModulecomponentsController extends Controller
{

	protected $version = '$Revision: 1.10 $';

	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('ModuleComponent');

		$this->uses('ModuleDefault', false);

		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$moduleobjects = new ModuleObjectCollection($this->_templateobject);

		$sh = new SearchHandler($moduleobjects, false);

		$sh->extract();

		parent::index(new ModuleObjectCollection($this->_templateobject), $sh);

		$this->view->set('clickaction', 'view');

		$sidebar = new SidebarController($this->view);

		$sidebarlist['new']=array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'new'),
					'tag'=>'New'
					);

		$sidebar->addList('Actions',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null){

		if (!$this->CheckParams($this->_templateobject->idField)) {
			sendBack();
		}

		$flash = Flash::Instance();
		parent::delete($this->modeltype);
		sendTo($this->name,'index',$this->_modules);
	}

	public function edit()
	{
		$flash = Flash::Instance();

		parent::edit();

		$modulecomponent = $this->_uses[$this->modeltype];

		$this->addSidebar($modulecomponent);

		switch ($modulecomponent->type)
		{
			case 'C':

				break;

			case 'M':

				$model=new $modulecomponent->name;
				$fields=$model->getFields();
				$current_defaults=array();

				foreach ($modulecomponent->module_defaults as $default)
				{
					$current_defaults[$default->field_name]=$default->id;
				}

				foreach ($fields as $field)
				{
					if (isset($current_defaults[$field->name]))
					{
						$field->id=$current_defaults[$field->name];
					}
				}

				$this->view->set('model_class',$model);
				$this->view->set('models',array($modulecomponent->name=>$model));
				$this->view->set('fields', $fields);
				$this->view->set('type', 'display');

				if (is_null($modulecomponent->title))
				{
					$modulecomponent->title = $model->getTitle();
				}

				if ($model instanceOf DataObject)
				{
					$this->view->set('internal_type', 'DataObject');
				}
				elseif ($model instanceOf DataObjectCollection)
				{
					$this->view->set('internal_type', 'DataObjectCollection');
				}

				$this->view->set('version', $model->version());

				break;

			default:

				$flash->addWarning('Edit of '.$modulecomponent->getEnum('type', $modulecomponent->type).' not allowed');
				sendback();

		}

		$this->view->set('ModuleComponent', $modulecomponent);

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {

		if(!$this->checkParams($this->modeltype)) {
			sendBack();
		}

		$flash=Flash::Instance();
		if(parent::save($this->modeltype)) {
			$id=$this->saved_model->idField;
			sendTo($this->name, 'view', $this->_modules, array($id=>$this->saved_model->{$id}));
		} else {
			$flash->addError('Error saving module component');
			$this->refresh();
		}

	}

	public function view()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$modulecomponent=$this->_uses[$this->modeltype];

		$this->addSidebar($modulecomponent);

		switch ($modulecomponent->type)
		{
			case 'C':

				// set actions as an array with a null first value
				$actions = array('' => '');

				// fetch the methods for the controller
				$actions['Local Methods'] = get_final_class_methods($modulecomponent->name);

				// fetch the inherited methods
				$inherited_methods = get_class_methods($modulecomponent->name);
				$inherited_methods = array_combine($inherited_methods, $inherited_methods);
				$actions['Inherited Methods'] = array_diff($inherited_methods, $actions['Local Methods']);

				 natcasesort($actions['Local Methods']);
				 natcasesort($actions['Inherited Methods']);

				$this->view->set('local_methods', $actions['Local Methods']);
				$this->view->set('inherited_methods', $actions['Inherited Methods']);

				if (is_null($modulecomponent->title))
				{
					$modulecomponent->title = $modulecomponent->name;
				}

				$this->view->set('internal_type', 'Controller');

//				$this->view->set('version', $model->version());

				break;

			case 'M':

				$model=new $modulecomponent->name;
				$fields=$model->getFields();
				$current_defaults=array();

				foreach ($modulecomponent->module_defaults as $default)
				{
					$current_defaults[$default->field_name]=$default->id;
				}

				foreach ($fields as $field)
				{
					if (isset($current_defaults[$field->name]))
					{
						$field->id=$current_defaults[$field->name];
					}
				}

				$this->view->set('model_class',$model);
				$this->view->set('models',array($modulecomponent->name=>$model));
				$this->view->set('fields', $fields);
				$this->view->set('type', 'display');

				if (is_null($modulecomponent->title))
				{
					$modulecomponent->title = $model->getTitle();
				}

				if ($model instanceOf DataObject)
				{
					$this->view->set('internal_type', 'DataObject');

					$this->view->set('system_policies', $modulecomponent->system_policies);

				}
				elseif ($model instanceOf DataObjectCollection)
				{
					$this->view->set('internal_type', 'DataObjectCollection');
				}

				$this->view->set('version', $model->version());

				break;

			case 'T':

				$controllername	= $modulecomponent->controller.'Controller';
				$controller		= new $controllername('', $this->view);

				$this->view->set('models', $controller->usesModels());
				$this->view->set('type', 'input');

			default:

				$this->view->set('models',array());

		}

		$this->view->set('moduledefault', DataObjectFactory::Factory('ModuleDefault'));

	}

	public function save_defaults()
	{

		if(!$this->checkParams('ModuleComponent'))
		{
			sendBack();
		}

		if (!$this->loadData())
		{
			sendBack();
		}

		$modulecomponent = $this->_uses[$this->modeltype];

		$idField	= $modulecomponent->idField;
		$idValue	= $modulecomponent->{$modulecomponent->idField};

		$errors = array();

		$flash = Flash::Instance();

		$db = DB::Instance();

		$db->Debug();

		$db->StartTrans();		

		switch ($modulecomponent->type)
		{
			case 'M':

				if (strpos($modulecomponent->name, 'collection'))
				{
					$do = new $modulecomponent->name;
				}
				else
				{
					$do = DataObjectFactory::Factory($modulecomponent->name);
				}

				$enabled = $do->getDisplayFields();

				break;

			case 'T':

				$controllername = $modulecomponent->controller.'Controller';

				$do=new $controllername('', $this->view);

				$enabled=$do->getInputFields();

				break;

			default:

				$do = null;
		}

		if (is_object($do))
		{
			$do_name = get_class($do);
		}
		else
		{
			$do_name = 'none';
		}

		if (isset($this->_data[$do_name]))
		{
			$defaults = array();

			foreach ($this->_data[$do_name] as $key=>$field)
			{
				foreach ($field as $name=>$value)
				{
					if (substr((string) $name, 0, 17)=='_checkbox_exists_')
					{
						$checkbox_name=substr((string) $name, 17);

						if (isset($this->_data[$do_name][$key][$checkbox_name]))
						{
							continue;
						}
						else
						{
							$defaults[$checkbox_name][$key]='false';
						}
					}
					else
					{
						$defaults[$name][$key]=$value;
					}
				}
			}

			foreach ($defaults as $field=>$default)
			{
				$current = $do->getField($field);

				if (!is_object($current))
				{
					// Non Database Field
					continue;
				}

				if (((!isset($enabled[$field]) && !$default['enabled'])
					|| (isset($enabled[$field]) && $enabled[$field]==$default['enabled']))
					&& ($current->has_default && $current->default_value==$default['default_value']))
				{
					// don't update if value not changed!
					continue;
				}

				$moduledefault = DataObjectFactory::Factory('ModuleDefault');
				$moduledefault->loadBy(array('module_components_id', 'field_name'), array($idValue, $field));

				$data = array();

				if ($moduledefault->isLoaded())
				{
					$data['id'] = $moduledefault->id;
				}
				$data['field_name'] = $field;

				if (!isset($default['default_value']))
				{
					$default['default_value']='';
				}

				$data['default_value']			= $default['default_value'];
				$data['enabled']				= $default['enabled'];
				$data['module_components_id']	= $idValue;

				$moduledefault=DataObject::Factory($data, $errors, 'ModuleDefault');

				if (!$moduledefault || !$moduledefault->save(true))
				{
					$errors[] = 'Failed to update default value for '.$field;
					break;
				}
			}

		}

		if (count($errors)>0)
		{
			$flash->addErrors($errors);

			$db->FailTrans();
			$db->CompleteTrans();

			$this->_data['id'] = $idValue;

			$this->refresh();
		}
		else
		{
			$flash->addMessage('defaults updated');

			$db->CompleteTrans();

			sendTo($this->name,'view',$this->_modules, array($idField=>$idValue));
		}
	}

	private function addSidebar($modulecomponent, $edit = false)
	{
		$sidebar = new SidebarController($this->view);

		if ($edit)
		{
			$sidebarlist['edit']=array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'edit','id'=>$modulecomponent->id),
					'tag'=>'Edit'
					);
		}

		$sidebarlist['all']=array(
					'link'=>array('modules'=>$this->_modules,'controller'=>'ModuleObjects','action'=>'index'),
					'tag'=>'View All Modules'
					);

		$sidebarlist['allcomponents']=array(
					'link'=>array('modules'=>$this->_modules,'controller'=>'ModuleObjects','action'=>'view','id'=>$modulecomponent->module_id),
					'tag'=>'View All Components for Module'
					);

		$sidebar->addList('Actions',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	/*
	 * Protected Functions
	 */
	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName((empty($base)?'Components':$base), $action);
	}

	/*
	 * Private Functions
	 */
	private function getDefaults($modulecomponent)
	{
		$object=new $modulecomponent->name;

		if (is_object($object) && $object instanceof DataObject)
		{
			return $object->getFields();
		}

		return false;
	}

}

// End of ModulecomponentsController

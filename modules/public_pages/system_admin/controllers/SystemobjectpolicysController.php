<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SystemobjectpolicysController extends Controller
{

	protected $version = '$Revision: 1.6 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('SystemObjectPolicy');
		
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		
		// Search
		$errors = array();
	
		$s_data = array();

// Set context from calling module
		$s_data['name']					= '';
		$s_data['module_components_id']	= '';
				
//		$this->setSearch('SystemPolicySearch', 'useDefault', $s_data);
		
		parent::index(new SystemObjectPolicyCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'New System Policy'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('page_title', $this->getPageName('', 'List'));
		
		$this->view->set('clickaction', 'view');
		
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null)
	{

		$result = parent::delete($this->modeltype);
		
		if (isset($this->_data['ajax']))
		{
		
			header('Content-type: application/json');
			
			if ($result)
			{
				echo json_encode(array('success' => TRUE));
			}
			else
			{
				echo json_encode(array('success' => FALSE));
			}
			
			exit;
		
		}
				
		sendTo($this->name, 'index', $this->_modules);
		
	}

	public function _new()
	{
		
		parent::_new();
		
		$systempolicy = $this->_uses[$this->modeltype];
		
		if ($systempolicy->isLoaded())
		{
			$this->_data['module_components_id'] = $systempolicy->module_components_id;
			
			if (substr($systempolicy->value, -1) == ')')
			{
				$systempolicy->operator = ($systempolicy->operator == 'IN')?'=':'!=';
			}
			
		}
		
		$module_component = DataObjectFactory::Factory('ModuleComponent');
		
		$module_component->identifierField = 'title';
		$module_component->orderby = 'title';
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('type', '=', 'M'));
		
// Really need to identify the DataObject models
		$cc->add(new Constraint('name', 'not like', '%collection'));
		
		$cc->add(new Constraint('name', 'not like', '%search'));
		
		$components = $module_component->getAll($cc);
		
		if (isset($this->_data['module_components_id']))
		{
			$module_component->load($this->_data['module_components_id']);
		}
		elseif (count($components) > 0)
		{
			$module_component->load(key($components));
		}
		
		$this->view->set('components', $components);
		
		$fields = $this->get_fields($module_component);
		
		$key_field = array_search('key_field', $fields);
		
		if ($key_field !== FALSE)
		{
			$this->view->set('key_field', $key_field);
		}
		
		$this->view->set('fields', $fields);
		
		$current_field = is_null($systempolicy->fieldname)?key($fields):$systempolicy->fieldname;
		
		$output = $this->get_values($current_field, $module_component->id, $systempolicy->value);
		
		$this->view->set('operators', $output['operators']['data']);
		$this->view->set('input_value', $output['input_value']['data']);
		
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		if(!$this->checkParams($this->modeltype))
		{
			sendBack();
		}
		
		$errors = array();
		
		if (is_array($this->_data[$this->modeltype]['value']))
		{
			if (empty($this->_data[$this->modeltype]['value']))
			{
				$errors[] = 'No value selected';
			}
			elseif (count($this->_data[$this->modeltype]['value']) > 1)
			{
				$this->_data[$this->modeltype]['value'] = '('.implode(',', $this->_data[$this->modeltype]['value']).')';
				$this->_data[$this->modeltype]['operator'] = ($this->_data[$this->modeltype]['operator']=='=')?'IN':'NOT IN';
			}
			else
			{
				$this->_data[$this->modeltype]['value'] = current($this->_data[$this->modeltype]['value']);
				if ($this->_data[$this->modeltype]['value'] == "'NULL'" || $this->_data[$this->modeltype]['value'] == '')
				{
					$this->_data[$this->modeltype]['operator'] = ($this->_data[$this->modeltype]['operator']=='=')?'IS':'IS NOT';
				}
			}
		}
		
		if ($this->_data[$this->modeltype]['fieldname'] == $this->_data[$this->modeltype]['key_field'])
		{
			$this->_data[$this->modeltype]['is_id_field'] = TRUE;
		}
		else
		{
			$this->_data[$this->modeltype]['is_id_field'] = FALSE;
		}
		
		if (count($errors) > 0 || !parent::save($this->modeltype))
		{
			$this->refresh();
		}
		
		sendTo($this->name, 'index', $this->_modules);
	
	}	

	public function view()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$systempolicy = $this->_uses[$this->modeltype];
				
		$this->addSidebar($systempolicy);

		$policy_permissions = new SystemPolicyControlListCollection();
		
		$sh = $this->setSearchHandler($policy_permissions);
		$sh->addConstraint(new Constraint('object_policies_id', '=', $systempolicy->{$systempolicy->idField}));
		
		parent::index($policy_permissions, $sh);
		
	}
	
/*
 * Protected functions
 */
	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName((empty($base)?'System Policy':$base), $action);
	}

/*
 * Private Functions
 */
	private function addSidebar($systempolicy)
	{
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['allpolicies']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 ),
					'tag'=>'View All System Policies'
					);
					
		$sidebar->addList('Actions',$sidebarlist);
		
		$sidebarlist = array();
		
		$sidebarlist['edit']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'edit'
								 ,'id'=>$systempolicy->{$systempolicy->idField}),
					'tag'=>'edit_policy_details'
					);

		$sidebarlist['delete']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'delete'
								 ,'id'=>$systempolicy->{$systempolicy->idField}),
					'tag'=>'delete_policy_details'
					);

		$sidebarlist['addpermission']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'systempolicycontrollists'
								 ,'action'=>'_new'
								 ,'object_policies_id'=>$systempolicy->{$systempolicy->idField}),
					'tag'=>'add_policy_permission'
					);
		
		$sidebar->addList('This Policy',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

/*
 * Output functions - called by ajax
 */
	public function get_fields($module_components = '', $_module_components_id = '')
	{
		if (isset($this->_data['module_components_id'])) { $_module_components_id = $this->_data['module_components_id']; }
		
		if (empty($module_components) || !$module_components instanceof ModuleComponent)
		{
			$module_components = DataObjectFactory::Factory('ModuleComponent');
		}
		
		if (!$module_components->isLoaded() && !empty($_module_components_id))
		{
			$module_components->load($_module_components_id);
		}
		
		$fields = array();
		
		if ($module_components->isLoaded())
		{
			$model = DataObjectFactory::Factory($module_components->name);
			
			foreach ($model->getFields() as $fieldname=>$field)
			{
				
				if (isset($model->belongsTo[$fieldname]))
				{
					continue;
				}
				elseif (!$model->isHidden($fieldname) || $field->system_override)
				{
					$fields[$fieldname] = $field->tag;
				}
				elseif ($model->idField == $fieldname)
				{
					$fields[$fieldname] = 'Key Field';
				}
			}
			
		}

		if (isset($this->_data['ajax']))
		{
			$this->view->set('options', $fields);
			echo $this->view->fetch('select_options');
			exit;
		}
		else
		{
			return $fields;
		}
		
	}
	
	public function get_values($_field_name = '', $_module_components_id = '', $_current_value = '')
	{
		
		if (isset($this->_data['field_name'])) { $_field_name = $this->_data['field_name']; }
		if (isset($this->_data['module_components_id'])) { $_module_components_id = $this->_data['module_components_id']; }
		if (isset($this->_data['value'])) { $_current_value = $this->_data['value']; }
		
		$value = '';
		
		if (!empty($_module_components_id) && !empty($_field_name))
		{
			$module_component = DataObjectFactory::Factory('ModuleComponent');
			$module_component->load($_module_components_id);
			
			$model = DataObjectFactory::Factory($module_component->name);
			
			if ($_field_name == $model->idField)
			{
				$value = $model->getAll();
			}
			elseif (isset($model->belongsToField[$_field_name]))
			{
				$belongsto			= $model->belongsTo[$model->belongsToField[$_field_name]];
				$belongsto_model	= DataObjectFactory::Factory($belongsto['model']);
				$value				= $belongsto_model->getAll();
				if ($model->getField($_field_name)->not_null != 1)
				{
					$value = array("'NULL'"=>'Null')+$value;
				}
			}
			elseif ($model->isEnum($_field_name))
			{
				$value	= $model->getEnumOptions($_field_name);
			}
		}
		
		$this->view->set('model', $this->_templateobject);
		$this->view->set('attribute', 'value');
		$this->view->set('tags', 'true');
		
		if (is_array($value))
		{
			if (substr($_current_value, -1) == ')')
			{
				$this->view->set('value', explode(',', str_replace(array('(', ')'), '', $_current_value)));
			}
			$this->view->set('multiple', true);
			$this->view->set('options', $value);
			$html = $this->view->fetch('select');
			$operators = $this->_templateobject->getEnumOptions('multiple');
		}
		else
		{
			$html = $this->view->fetch('input');
			$operators = $this->_templateobject->getEnumOptions('operator');
		}
		
		$output['operators']	= array('data'=>$operators,'is_array'=>is_array($operators));
		$output['input_value']	= array('data'=>$html,'is_array'=>is_array($html));
		
		if (isset($this->_data['ajax']))
		{
			$this->view->set('data',$output);
			echo $this->view->fetch('ajax_multiple');
			exit;
		}
		else
		{
			return $output;
		}
		
	}

}

// End of SystemobjectpolicysController

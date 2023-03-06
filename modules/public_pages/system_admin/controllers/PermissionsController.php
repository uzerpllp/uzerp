<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PermissionsController extends Controller {

	protected $version = '$Revision: 1.21 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = new Permission();
		$this->uses($this->_templateobject);
		
	}

	public function index($collection = null, $sh = null, &$c_query = null)
	{
		
		$permissions	= new PermissionCollection($this->_templateobject);
		
		$this->view->set('modules_list', $this->get_modules_list());

		$this->view->set('tree', $permissions->getPermissionTree());		
		
	}

	public function _new()
	{
		
		$options = array(
			'modules'		=> $this->get_modules_list(),
			'controllers'	=> NULL,
			'actions'		=> NULL
		);
		
		$selected = array(
			'module'		=> NULL,
			'controller'	=> NULL,
			'action'		=> NULL
		);
		
		$parameter_string = '';
		
		$permission = new Permission();
		
		if ($this->_data['action'] === 'edit')
		{
			
			$permission->load($this->_data['id']);
			
			// don't build selected / option arrays if we don't have a module_id
			// any standard link would have such at least a module_id
						
			if (!empty($permission->_data['module_id']))
			{
				
				// build module [, controller [, action]] lists
				switch ($permission->type)
				{
					
					case 'a':
						$selected['action']		= $permission->permission;
						$selected['module']		= $permission->module_id;
						$selected['controller']	= $permission->component_id;
						
						$options['controllers']	= $this->get_controller_list($selected['module']);
						$options['actions']		= $this->get_action_list($selected['controller']);
						break;
						
					case 'c':
						$selected['module']		= $permission->module_id;
						$selected['controller']	= $permission->component_id;
						
						$options['controllers']	= $this->get_controller_list($selected['module']);
						break;
						
					case 'm':
						$selected['module'] = $permission->module_id;
						break;
							
				}
								
			}
			
			if (in_array($permission->type, array('a', 'c', 'm')))
			{
				$tab = 'standard';
			}
			
			if (in_array($permission->type, array('g', 's')))
			{
				$tab = 'group';
			}
			
			if (in_array($permission->type, array('x')))
			{
				$tab = 'custom';
			}
						
			$this->view->set('tab', $tab);
		
			// built parameters field
			$parameters = new PermissionParametersCollection(new PermissionParameters());
			$sh			= new SearchHandler($parameters, FALSE);
				
			$sh->addConstraint(new Constraint('permissionsid', '=', $permission->id));
				
			$data = $parameters->load($sh, null, RETURN_ROWS);
			
			foreach ($data as $parameter)
			{
				$parameter_string .= $parameter['name'] . '=' . $parameter['value'] . "\n";
			}
		
		}
		
		$this->view->set('parameter_string', trim($parameter_string));
		$this->view->set('permission', $permission);
		$this->view->set('options', $options);
		$this->view->set('selected', $selected);
		
		if (isset($this->_data['parent_id']) && !empty($this->_data['parent_id']))
		{
			
			$permission	= new Permission();
			$parent		= $permission->load($this->_data['parent_id']);
		
			$this->view->set('parent_name', $parent->title);
			$this->view->set('parent_id', $this->_data['parent_id']);
			
		}
		
	}
	
	public function delete($modelName = null)
	{
		
		// we're echoing JSON, so lets tell the browser
		// this is required for $.ajax to be able to intelligently guess what the dataType is
		
		header('Content-type: application/json');
		
		// get the id from data
		if (is_ajax())
		{
			$id = (!empty($this->_data['id']) ? $this->_data['id'] : null);
		}
		
		$permission = new Permission();
		
		// NOTE: the db will take care od cascading the delete to child rows
		if ($permission->delete($id))
		{
			echo json_encode(array('success' => TRUE));
		}
		else
		{
			echo json_encode(array('success' => FALSE));
		}
		
		exit;
		
	}
	
	public function save($modelName = null, $dataIn = array(), &$errors = array())
	{
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$flash		= Flash::Instance();
		$permission	= new Permission();
		
		$modules			= new ModuleObject();
		$module_components	= new ModuleComponent();
		
		$errors		= '';
		$messages	= '';
		
		
		// we're echoing JSON, so lets tell the browser
		// this is required for $.ajax to be able to intelligently guess what the dataType is
		
		header('Content-type: application/json');
		
		// for standard permissions we need to work out the permission type
		switch ($this->_data['PermissionData']['type'])
		{
			
			case  'standard':
				
				foreach (array('m' => 'module', 'c' => 'controller', 'a' => 'action') as $key => $type)
				{
					
					if (!empty($this->_data['PermissionData'][$type]))
					{
						$this->_data['Permission']['type'] = $key;
					}
					
				}
				break;
				
			case 'group':
				
				$this->_data['Permission']['type'] = 'g';
				break;
				
			case 'custom':
				
				$this->_data['Permission']['type'] = 'x';
				break;
			
		}
		
		// make sure we have a valid type set
		if (!in_array($this->_data['Permission']['type'], array_keys($permission->getEnumOptions('type'))))
		{
			echo json_encode(array('success' => FALSE, 'errors' => 'Invalid permission type'));
			exit;
		}
		
		// we may also need to genrate the permission field based on the permission type
		if (!isset($this->_data['Permission']['permission']) || empty($this->_data['Permission']['permission']))
		{
			
			switch ($this->_data['Permission']['type'])
			{
				
				case 'm':
					$this->_data['Permission']['permission'] = $modules->load_identifier_value($this->_data['PermissionData']['module']);
					break;
				
				case 'c':
					$this->_data['Permission']['permission'] = str_replace('controller', '', $module_components->load_identifier_value($this->_data['PermissionData']['controller']));
					break;
				
				case 'a':
					$this->_data['Permission']['permission'] = $this->_data['PermissionData']['action'];
					break;
				
			}
			
		}
		
		// ensure we're dealing with a lowercase permission value
		$this->_data['Permission']['permission'] = strtolower($this->_data['Permission']['permission']);
	
		if ($this->_data['PermissionData']['type'] === 'standard')
		{
			
			switch ($this->_data['Permission']['type'])
			{
				
				case 'm':
					$this->_data['Permission']['module_id'] = $this->_data['PermissionData']['module'];
					break;
					
				case 'c':
				case 'a':
					$this->_data['Permission']['module_id']		= $this->_data['PermissionData']['module'];
					$this->_data['Permission']['component_id']	= $this->_data['PermissionData']['controller'];
					break;
					
			}
			
		}
			
		// make sure we've got a title
		if (!isset($this->_data['Permission']['title']) && empty($this->_data['Permission']['title']))
		{
			$errors .= '<li>A title is required</li>';
		}
		
		// before we proceed to save, do a quick error check
		if (!empty($errors))
		{
			echo json_encode(array('success' => FALSE, 'errors' => $errors));
			exit;
		}
		
		// save the model, saving the returned success variable
		$result = parent::save('Permission');
		
		// get the values from flash
		$flash->save();

		foreach ($flash->__get('errors') as $error)
		{
			$errors .= '<li>' . $error . '</li>';
		}

		foreach ($flash->__get('messages') as $message)
		{
			$messages .= '<li>' . $message . '</li>';
		}

		$flash->clear();
		
		if ($result)
		{
			
			// save permission parameters
			$saved_permission = $this->saved_model;
			
			$parameter	= new PermissionParameters();
			$parameters = new PermissionParametersCollection($parameter);
			$sh			= new SearchHandler($parameters, FALSE);
			
			$sh->addConstraint(new Constraint('permissionsid', '=', $saved_permission->id));
			
			$parameters->load($sh);
			
			foreach ($parameters as $value)
			{
				$value->delete();
			}
			
			$parameters_to_save = FALSE;
			
			if (!empty($this->_data['PermissionData']['extra']))
			{
	
				// trim off excess whitespace off the whole
				$text = trim($this->_data['PermissionData']['extra']);
				
				// explode all separate lines into an array
				$textAr = explode("\n", $text);
				
				// trim all lines contained in the array.
				$textAr = array_filter($textAr, 'trim');
				
				// loop through the lines
				foreach($textAr as $line)
				{
					
					$parts = explode("=", $line);
					
					if (count($parts) === 2)
					{

						$parameters_to_save = TRUE;
						
						$parameter = new PermissionParameters();
						
						$parameter->permissionsid	= $saved_permission->id;
						$parameter->name			= $parts[0];
						$parameter->value			= $parts[1];

						//Set the id to 'NULL', otherwise save will fail
						$parameter->id = 'NULL';
						
						$save_result = $parameter->save();
						
						if ($save_result === FALSE)
						{
							$errors .= '<li>Failed to save parameters</li>';
							//continue;
						}
						
					}
					
				}
				
			}
			
			if (!$permission->update($saved_permission->id, 'has_parameters', ($parameters_to_save === TRUE)))
			{
				$errors .= '<li>Error setting parameter flag on permission</li>';
			}
			
		}
				
		if (empty($errors))
		{		
			
			$db->CompleteTrans();
			
			echo json_encode(array('success' => TRUE, 'messages' => $messages));
			
		}
		else
		{
			
			$db->FailTrans();
			$db->CompleteTrans();
			
			echo json_encode(array('success' => FALSE, 'errors' => $errors));
			
		}

		// Don't let this function return as the above is all handled via ajax/XHR via the UI.
		exit();
	}
	
	public function update()
	{
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$new_position = $this->_data['position'];
				
		// load sibling permissions
		$permissions = new PermissionCollection(new Permission());
		$sh = new SearchHandler($permissions, FALSE);
		
		// build the query based on whether a parent_id is available
		if (isset($this->_data['parent_id']) && !empty($this->_data['parent_id']))
		{
			$sh->addConstraint(new Constraint('parent_id', '=', $this->_data['parent_id']));
		}
		else
		{
			$this->_data['parent_id'] = 'null';
			$sh->addConstraint(new Constraint('parent_id', 'IS', 'NULL'));
		}
		
		// finally, always ignore the current permission
		$sh->addConstraint(new Constraint('id', '!=', $this->_data['permission_id']));
		
		$permissions->load($sh);
		
		
		 //***********************************
		// CHECK + UPDATE SIBLING PERMISSIONS
		
		$count				= 0;
		$position_errors	= FALSE;
		
		foreach ($permissions as $model)
		{
			
			// normal increment
			$count++;
			
			if ($new_position == $count)
			{
				
				// we've come across our new item
				// increment again, so we make a gap for the new permission
				
				$count++;
				
			}
			
			// there's no point in updating the record if it's already in the correct position
			if ($model->position != $count)
			{
				
				if (!$model->update($model->id, array('position'), array($count)))
				{
					$position_errors = TRUE;
					continue;
				}
				
			}
			
		}
		
		// no point in proceeding is we've got errors
		if ($position_errors === TRUE)
		{
			
			$db->FailTrans();
			$db->CompleteTrans();
			
			echo json_encode(array('success' => FALSE, 'errors' => '<li>Error updating sibling positions</li>'));
			exit;
			
		}
		
		$permission = new Permission();
		
		// update the current permission position
		if (!$permission->update($this->_data['permission_id'], array('position', 'parent_id'), array($new_position, $this->_data['parent_id'])))
		{
			
			$db->FailTrans();
			$db->CompleteTrans();
			
			echo json_encode(array('success' => FALSE, 'errors' => '<li>Error updating desired position</li>'));
			exit;
			
		}
		
		// must have been successful
		$db->CompleteTrans();
		
		echo json_encode(array('success' => TRUE));
		exit;
		
	}
	
	public function get_modules_list()
	{
		
		$module_object = new ModuleObject();
		
		return $module_object->getAll();
		
	}

	public function get_controller_list($module_id = '')
	{
		
		if (is_ajax() && is_direct_request())
		{
			$module_id = $this->_data['module_id'];
		}
		
		$module_components = new ModuleComponent();
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('module_id', '=', $module_id));
		$cc->add(new Constraint('type', '=', 'C'));
		
		$controllers = ['' => ''];
		$controllers += $module_components->getAll($cc);
		
		if (is_ajax() && is_direct_request())
		{
			
			$output['controller'] = array('data' => $controllers, 'is_array' => is_array($controllers));
		
			$this->view->set('data', $output);
			$this->setTemplateName('ajax_multiple');
		}
		else
		{
			return $controllers;
		}

	}
	
	public function get_action_list($controller_id = '')
	{

		if (is_ajax() && is_direct_request())
		{
			$controller_id = $this->_data['controller_id'];
		}
		
		$html = '';
		
		// attempt to load the controller based on the controller id
		$module_components = new ModuleComponent();
		$module_components->load($controller_id);
		
		// if the component hs loaded it must mean we were dealing with an id
		// set the controller_id to the controller name
		
		if ($module_components->loaded)
		{
			$controller_id = $module_components->name;
		}
		
		// set actions as an array with a null first value
		$actions = array('' => '');
		
		// fetch the methods for the controller
		$local_methods = get_final_class_methods($controller_id);
		ksort($local_methods);

		// fetch the inherited methods
		$inherited_methods = get_class_methods($controller_id);
		$inherited_methods = array_combine($inherited_methods, $inherited_methods);
		ksort($inherited_methods);
		
		// To have the selected item in a dropdown match an item in the options array,
		// the array key must be lowercase to match values stored in DB.
		$actions['Local Methods'] = array_change_key_case($local_methods, CASE_LOWER);
		$actions['Inherited Methods'] = array_change_key_case($inherited_methods, CASE_LOWER);
		
		if (is_ajax() && is_direct_request())
		{
			
			$output['action'] = array('data' => $actions, 'is_array' => is_array($actions));
						
			$this->view->set('data', $output);
			$this->setTemplateName('ajax_multiple');
			
		}
		else
		{
			return $actions;
		}

	}
	
	public function tree()
	{
		
		$permissions	= new PermissionCollection($this->_templateobject);

		$this->view->set('tree', $permissions->getPermissionTree());	
		
		$this->setTemplateName('tree');
		
	}
	
	function requires_id($type, $id_type)
	{
		
		// empty arrays have no requirement for module / component ids
		
		switch ($type)
		{
			
			case 'm':
				return in_array($id_type, array('module'));
				
			case 'c':
			case 'a':
				return in_array($id_type, array('module', 'component'));
				
				
		}
		
	}
	
}

// end of PermissionController.php
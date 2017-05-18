<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MasterSetupController extends Controller
{

	protected $version = '$Revision: 1.16 $';
	
	protected $module_preferences = array();
	
	protected $setup_preferences = array();
		
	protected $setup_options = array();
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->setup_module = $module;
	}
	
	public function index()
	{
		// Get any System Preferences for the module
		
		if (!empty($this->setup_preferences))
		{
			$prefs = SystemPreferences::instance($this->setup_module);
			
			foreach ($this->setup_preferences as $pref=>$title)
			{
				$this->module_preferences[$pref]['preference']	= $prefs->getPreferenceValue($pref, $this->setup_module);
				$this->module_preferences[$pref]['title']		= $title;
			}
			
//			$this->preferences = new ModulePreferences();
			
//			$this->preferences->setModuleName($this->setup_module);
			
			$this->registerPreference();
			
			$this->view->set('templateCode', $this->preferences->generateTemplate());
		
		}
		
		// Get any Data Options for the module
		$current_options	= array();
		
		if (!empty($this->setup_options))
		{
			foreach ($this->setup_options as $name => $modelname)
			{
				
				if ($modelname != 'spacer')
				{
					
					if (is_array($modelname))
					{
						
						$options = DataObjectFactory::Factory($modelname['model']);
						
					}
					else
					{
						
						$options = DataObjectFactory::Factory($modelname);
						
					}
					
					$current_options[$name]			 = $this->option_link($name, $modelname);
					$current_options[$name]['count'] = $options->getCount();
					
				}
				
			}
		}
		
		$this->view->set('setup_options', $current_options);
		
		$this->view->set('no_ordering', TRUE);
		
		$this->view->set('page_title', $this->getPageName($this->setup_module, 'Setup'));
		
		$this->view->set('link', array(
									'module'		=> $this->setup_module,
									'controller'	=> 'setup',
									'action'		=> 'save_preferences'
									)
				);
		
		$this->setTemplateName('module_setup_index');
	}
	
	public function delete_items()
	{
		
		if (isset($this->_data['delete_items']))
		{
			
			$items					= $this->_data['delete_items'];
			
			$this->_data['option']	= key($items);
			
			$option					= $this->checkValidOption() or sendBack();
			
			$flash					= Flash::Instance();
			
			$modelname				= $this->setup_options[$option];
			
			$model					= DataObjectFactory::Factory($modelname);
			
			$db						= &DB::Instance();
			
			$count					= 0;
			
			$db->StartTrans();
			
			$errors = array();
			
			foreach ($items[$option] as $id => $on)
			{
				$model->delete($id, $errors);
				$count++;
			}
			
			if ($db->CompleteTrans())
			{
				$flash->addMessage($count . ' items deleted');
			}
			else
			{
				$flash->addErrors($errors);
			}
			
		}
		
		sendBack();
		
	}
	
	public function edit()
	{
		
		$this->view();
		
		$option = $this->checkValidOption() or sendBack();
		$model = DataObjectFactory::Factory($this->setup_options[$option]);
		$model->load($this->_data['id']) or sendBack();
		
		$this->view->set('model', $model);
		
		$this->view->set('edit_extrafields', $this->editFields($option, $model));
		
	}

	public function save_item()
	{
		
		$option		= $this->checkValidOption() or sendBack();
		
		$modelname	= $this->setup_options[$option];
		$db			= DB::Instance();
		
		if (isset($this->_data[$modelname]))
		{
			
			if (empty($this->_data[$modelname]['id']))
			{
				$action	= 'added';
				$update	= FALSE;
			}
			else
			{
				$action	= 'updated';
				$update	= TRUE;
			}
			
			if (!empty($this->_data[$modelname]['position']))
			{
				if ($update)
				{
					$model = DataObjectFactory::Factory($modelname);
					
					$model->load($this->_data[$modelname]['id']);
					
					$current_position = $model->position;
				}
				else
				{
					$model = DataObjectFactory::Factory($modelname);
						
					$model->loadBy('position', $this->_data[$modelname]['position']);
						
					if (!$model->isLoaded())
					{
						// No need to update sequences because new sequence is not used
						$current_position = $this->_data[$modelname]['position'];
					}
				}
				
				$this->updatePositions($modelname, 'position', $this->_data[$modelname]['position'], $current_position);
			}
			
			$flash		= Flash::Instance();
			$errors		= array();
			$model		= DataObject::Factory($this->_data[$modelname], $errors, $modelname);
			
			if ($model && $model->save())
			{
				$flash->addMessage('Item '.$action.' successfully');
				
				sendTo($this->name, 'view', $this->_modules, array('option'=>$option));
			}
			else
			{
				$errors[] = 'Error saving item : '.$db->ErrorMsg();
				$flash->addErrors($errors);
				sendBack();
			}
			
		}
		
	}
	
	public function save_preferences()
	{
		$flash = Flash::Instance();
		
		$module = SystemPreferences::instance($this->setup_module);
		
		$this->registerPreference();
		
		$preferenceNames = $this->preferences->getPreferenceNames();
		
		$result = TRUE;
		
		// FIXME: Validate incoming data against supplied values
		foreach($preferenceNames as $preferenceName)
		{
			$preference = $this->preferences->getPreference($preferenceName);
			
			if (isset($this->_data[$preferenceName]))
			{
				
				if(isset($preference['type'])&&$preference['type']=='numeric')
				{
				
					if(!is_numeric($this->_data[$preferenceName]))
					{
						$flash->addError($preference['display_name'].' must be numeric');
						$result = FALSE;
						continue;
					}
				}
				
				$module->setPreferenceValue(
					$preferenceName,
					$this->_data['__moduleName'],
					$this->_data[$preferenceName]
				);
			}
			else
			{
				switch ($preference['type'])
				{
					case 'checkbox':
						$module->setPreferenceValue(
							$preferenceName,
							$this->_data['__moduleName'],
							'off'
						);
						break;
						
					case 'select_multiple':
						$module->setPreferenceValue(
							$preferenceName,
							$this->_data['__moduleName'],
							array()
						);
						break;
				}
			}
		}
		
		$handled = $this->preferences->getHandledPreferences();
		
		foreach($handled as $name=>$preference)
		{
			if(!empty($this->_data[$name])&&isset($preference['callback']))
			{
				$callback = array($module,$preference['callback']);
				call_user_func($callback,$this->_data);
			}
		}

		if ($result)
		{
			$flash->addMessage('Preferences saved OK');
		}
		else
		{
			$errors[] = 'Error saving preferences';
			$flash->addErrors($errors);
		}
		
		sendBack();
		
	}
	
	public function view()
	{
		$option		= $this->checkValidOption() or sendBack();
		
		$modelname	= $this->setup_options[$option];
		
		$col_name	= $modelname . 'Collection';
		
		$model		= DataObjectFactory::Factory($modelname);
		
		$collection	= new $col_name();
		
		$sh = new SearchHandler($collection, FALSE);
		$sh->extract();
		$sh->setLimit(0);
		$collection->load($sh);
		
		$this->view->set('collection', $collection);
		$this->view->set('model', $model);
		
		$this->view->set('extrafields', $this->viewFields($option, $model));
		$this->view->set('edit_extrafields', $this->newFields($option, $model));
		
		if ($model->isField('position') || $model->isField('index'))
		{
			$this->view->set('orderable', TRUE);
		}
		
		// Set the Sidebar options
		$this->sidebar_options();
		
		$this->setTemplateName('module_setup_view');
		
	}

	/*
	 * Protected Functions
	 */
	protected function checkValidOption($value = 'option')
	{
		
		$option	= (isset($this->_data[$value]))?$this->_data[$value]:'';
		$valid	= isset($this->setup_options[$option]);
		
		if ($valid)
		{
			return $option;
		}
		
		$flash = Flash::Instance();
		$flash->addError('Invalid setup option');
		return FALSE;
		
	}
	
	protected function newFields($option, $model)
	{
		// Want to make sure the Identifier Fiels occur first in list
		$fields = array_flip($model->getIdentifierFields());
		
		// Now loop through the model's fields; these will
		// be in alphabetic order
		foreach ($model->getFields() as $fieldname=>$field)
		{
			if ($model->isHidden($fieldname))
			{
				$fields[$fieldname] = array('type'=>'hidden');
			}
			elseif ($model->isEnum($fieldname))
			{
				$fields[$fieldname] = array('type'=>'select'
										   ,'options'=>$model->getEnumOptions($fieldname));
			}
			elseif (isset($model->belongsToField[$fieldname]))
			{
				$fields[$fieldname] = $this->makeLookupField($model->belongsTo[$model->belongsToField[$fieldname]]['model'], $fieldname, TRUE);
			}
			elseif ($field->type != '')
			{
				
				switch ($field->type)
				{
					case ('int'):
					case ('int2'):
					case ('int4'):
					case ('int8'):
						$field_type = 'numeric';
						break;
					case ('varchar'):
						$field_type = 'text';
						break;
					default:
						$field_type = $field->type;
				}
				$fields[$fieldname] = array('type'=>$field_type);
			}
			elseif (isset($fields[$fieldname]))
			{
				// Some identifier fields are not to be displayed directly
				// probably because they are FK fields from the collection 
				unset($fields[$fieldname]);
			}
		}
		
		return $fields;
	}
	
	protected function viewFields($option, $model)
	{
		return $this->newFields($option, $model);
	}
	
	protected function editFields($option, $model)
	{
		return $this->newFields($option, $model);
	}
	
    protected function makeLookupField($modelname, $fieldname, $compulsory = FALSE)
    {
	 	
		$model		= DataObjectFactory::Factory($modelname);
		$options	= $model->getAll();
		
		if (!$compulsory)
		{
			$options = array('' => 'None') + $options;
		}
		
		return array(
			'type'		=> 'select',
			'options'	=> $options
			);
		
	}
	
	protected function registerPreference()
	{
		$this->preferences = new ModulePreferences();
			
		$this->preferences->setModuleName($this->setup_module);
	}
	
	/*
	 * Private Functions
	 */
	private function option_link($name, $modelname)
	{
		if (is_array($modelname))
		{
		
			return array(
					'tag'	=> $name,
					'link'	=> array(
							'module'		=> $modelname['module'],
							'controller'	=> $modelname['controller'],
							'action'		=> $modelname['action']
					)
			);
				
		}
		else
		{
				
			return array(
					'tag'	=> $name,
					'link'	=> array(
							'module'		=> $this->setup_module,
							'controller'	=> 'setup',
							'action'		=> 'view',
							'option'		=> $name
					)
			);
		
		}
		
	}
	
	private function sidebar_options()
	{
		$sidebar			= new SidebarController($this->view);
		
		$sidebar->addList($this->setup_module 
						, array('index' => 
										array('tag'		=> $this->name . ' Index'
											 ,'link'	=> array('module'		=> $this->setup_module
																,'controller'	=> $this->name
																)
											  )
								)
				);
		
		$list = array();
		
		foreach ($this->setup_options as $name => $modelname)
		{
			
			if ($modelname == 'spacer')
			{
				$list[] = $modelname;
			}
			else
			{
				
				$list[]	= $this->option_link($name, $modelname);
			   	
			}
			
		}
		
		$sidebar->addList('Options', $list);
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	private function updatePositions($modelname, $fieldname, $new_sequence, $current_sequence = NULL, &$errors = array())
	{
		
		if ($new_sequence == $current_sequence)
		{
			// No update required
			return TRUE;
		}
			 
		$collectionname = $modelname . 'Collection';
		 
		$collection = new $collectionname(DataObjectFactory::Factory($modelname));
		
		$sh = new SearchHandler($collection, FALSE);
		
		if (is_null($current_sequence))
		{
			// This is an insert so need to increase sequences after inserted sequence
			$sh->addConstraint(new Constraint($fieldname, '>=', $new_sequence));
			$increment = '+1';
		}
		else
		{
			// This is an update
			if ($new_sequence > $current_sequence)
			{
				// Need to shuffle existing values up the list
				$current_sequence++;
				$sh->addConstraint(new Constraint($fieldname, 'between', $current_sequence . ' and ' . $new_sequence));
				$increment = '-1';
			}
			else
			{
				// Need to shuffle existing values down the list
				$current_sequence--;
				$sh->addConstraint(new Constraint($fieldname, 'between', $new_sequence . ' and ' . $current_sequence));
				$increment = '+1';
			}
		
		}
		
		if (!$collection->update($fieldname, '(' . $fieldname . $increment . ')', $sh))
		{
			$db = DB::Instance();
			$errors[] = 'Error updating ' . $fieldname . ' : ' . $db->ErrorMsg();
			return FALSE;
		}
		
		return TRUE;
		
	}

}

// end of MasterSetupController.php
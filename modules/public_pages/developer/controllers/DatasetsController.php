<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DatasetsController extends printController
{

	protected $version='$Revision: 1.9 $';
	
	protected $_templateobject;
	
	private $_schema = 'user_tables';
	
	private $_fields = array();
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('Dataset');
		
		$this->uses($this->_templateobject);
	
	}
	
	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$this->view->set('clickaction', 'view');
		
		$collection = new DatasetCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($collection);
		
		$sh->addConstraintChain(ownerConstraint());
		
		parent::index($collection, $sh);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'New Dataset'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null)
	{
		$flash = Flash::Instance();
		
		parent::delete($this->modeltype);
		
		sendTo($this->name,'index',$this->_modules);
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		
		$flash=Flash::Instance();
		
		$errors = array();
		
		if (empty($this->_data[$this->modeltype]['id']))
		{
			$create = true;	
		}
		
		$this->_data[$this->modeltype]['name'] = str_replace(' ', '_', strtolower($this->_data[$this->modeltype]['name']));
		
		$db = DB::Instance();
		
		$db->StartTrans();
		
		if(parent::save('Dataset', null, $errors))
		{
		
			if (!$create || $this->create_table($this->_data[$this->modeltype]) == 2)
			{
				$db->CompleteTrans();
				
				sendTo($this->name,'index',$this->_modules);
			}
			
			$message = $db->ErrorMsg();
			
			if (!empty($message))
			{
				$errors[] = $message;
			}
		}
		
		$flash->addErrors($errors);
		
		$db->FailTrans();
		$db->CompleteTrans();
		
		$this->refresh();

	}
	
	public function _new()
	{

		parent::_new();
		
// Get the Dataset dObject - if loaded, this is an edit
		$dataset = $this->_uses[$this->modeltype];
		
		if ($dataset->isLoaded())
		{
			$this->view->set('dataset_model', new DataObject($dataset->name));
		}
		
	}

	public function view()
	{
		
		if (!isset($this->_data) || !$this->loadData())
		{
			$this->dataError();
			sendBack();
		}	
		
		$dataset = $this->_uses[$this->modeltype];
				
		$this->view->set('dataset', $dataset);
		$this->view->set('dataset_model', new DataObject($dataset->name));
		$this->view->set('field_types', $dataset->getEnumOptions('field_type'));
		
		$this->view->set('links', ModuleComponent::getModelList());
		
		$sidebar = new SidebarController($this->view);
		
		$actions = array();
		
		$actions['viewall']=array(
					'link'=>array('modules'		=> $this->_modules
								, 'controller'	=> $this->name
								, 'action'		=> 'index'),
					'tag'=>'View All Datasets'
				);
		
		$actions['edit']=array(
					'link'=>array('modules'			=> $this->_modules
								, 'controller'		=> $this->name
								, 'action'			=> 'edit'
								, $dataset->idField	=> $dataset->{$dataset->idField}),
					'tag'=>'edit dataset'
				);

		$actions['delete']=array(
				'link'=>array('modules'			=> $this->_modules
						, 'controller'		=> $this->name
						, 'action'			=> 'delete'
						, $dataset->idField	=> $dataset->{$dataset->idField}),
						'tag'=>'delete dataset'
								);
		
		$actions['listreports']=array(
					'link'=>array('module'		=> 'reporting'
								, 'controller'	=> 'reports'
								, 'action'		=> 'index'
								, 'tablename'	=> $this->getViewname($dataset->name)),
					'tag'=>'list_reports'
				);
		
		$actions['newreport']=array(
					'link'=>array('module'		=> 'reporting'
								, 'controller'	=> 'reports'
								, 'action'		=> 'new'
								, 'tablename'	=> $this->getViewname($dataset->name)),
					'tag'=>'new_report'
				);
		
		$actions['viewdata']=array(
					'link'=>array('modules'			=> $this->_modules
								, 'controller'		=> $this->name
								, 'action'			=> 'view_collection'
								, $dataset->idField	=> $dataset->{$dataset->idField}),
					'tag'=>'view_data'
				);
		
		$sidebar->addList(
			'Actions',
			$actions
		);
		
//		$this->sidebarRelatedItems($sidebar, $dataset);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		$this->view->set('dataset_fields', DataObjectFactory::Factory('DatasetField'));
		
	}
	
	public function delete_field()
	{
		
		if (!$this->checkParams(array('id', 'dataset_id')))
		{
			$this->dataError();
			sendBack();
		}
		
		$dataset = $this->_uses[$this->modeltype];
		
		$flash = Flash::Instance();
		
		$db = DB::Instance();

		$errors = [];
		
		$db->StartTrans();
		
		if (!parent::delete('DatasetField') || !$this->change_table($this->_data, 'delete'))
		{
			$flash->addError('Error deleting field');
			$db->FailTrans();
		}
		else
		{
			$dataset->load($this->_data['dataset_id']);
			
			$this->createOverview($dataset, $errors);
		}
		
		$db->CompleteTrans();
		
		sendTo($this->name, 'view', $this->_modules, array($dataset->idField=>$this->_data['dataset_id']));
		
	}
	
	function save_field()
	{
		
		if (!isset($this->_data['DatasetField']))
		{
			$this->dataError();
			sendBack();
		}
		
		$dataset = $this->_uses[$this->modeltype];
		
		$data = $this->_data['DatasetField'];
		
		$data['old_name'] = $data['name'];
		
		$data['name'] = strtolower(str_replace(' ', '_', $data['title']));
			
		if (!empty($data['module_component_id']))
		{
			$data['name'] .= '_id';
			
			$data['type'] = $dataset::get_fk_field_type();
		}
		
		$booleans = array('mandatory', 'searchable', 'display_in_list');
		
		foreach ($booleans as $field)
		if (!isset($data[$field]))
		{
			$data[$field] = FALSE;
		}
		else
		{
			$data[$field] = TRUE;
		}
		
		$db = DB::Instance();
		
		$flash = Flash::Instance();
		
		$errors = array();
		
		if (empty($data['id']))
		{
			$action = 'add';
		}
		else
		{
			$action = 'alter';
			
			$current_field = DataObjectFactory::Factory('DatasetField');
			
			$current_field->load($data['id']);
		}
		
		$db->StartTrans();
		
		$dataset->load($data['dataset_id']);
		
		if ($data['mandatory'])
		{
			$model = $this->newModel($dataset);
			
			$cc = new ConstraintChain();
			
			if ($action == 'alter')
			{
				$cc->add(new Constraint($data['name'], 'IS', 'NULL'));
			}
			
			if ($model->getCount($cc)>0)
			{
				if (!$action == 'alter')
				{
					$errors[] = 'Data exists so first add the field then make it mandatory';
				}
				elseif ($data['default_value']=='')
				{
					$errors[] = 'Default Value required to make this field mandatory';
				}
				else
				{
					$collection = new DataObjectCollection($model);
					$sh = new SearchHandler($collection);
					$sh->addConstraintChain($cc);
					if (!$collection->update($data['name'], $data['default_value'], $sh))
					{
						$errors[] = 'Error updating existing data for '.$data['title'].' with default value';
					}
				}
			}
		}
		
		$datasetfield = DataObject::Factory($data, $errors, 'DatasetField');
		
		// TODO: if this is an update, only change table if field details have changed
		// otherwise do the change table to add the new field
		if (count($errors) > 0 || !$datasetfield || !$datasetfield->save() || !$this->change_table($data, $action))
		{
			$errors[] = 'Error '.((action == 'alter')?'updating':'inserting').' '.$data['title'].' field definition : '.$db->ErrorMsg();
			$db->FailTrans();
			$db->CompleteTrans();
		}
		else
		{
			$db->CompleteTrans();
			$this->createOverview($dataset, $errors);
		}
		
		if (count($errors) > 0)
		{
			$flash->addErrors($errors);
		}
		else
		{
			$flash->addMessage('"'.$data['title'].'" field saved OK');
		}
		
		sendTo($this->name, 'view', $this->_modules, array($dataset->idField=>$data['dataset_id']));
	}
	
	public function PrintCollection($collection = NULL)
	{
		if (!$this->isPrinting())
		{
			return parent::printCollection($collection);
		}
		
		if (!isset($this->_data) || !$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$dataset = $this->_uses[$this->modeltype];
		
		$model = $this->newModel($dataset);
		
		$title = $model->getTitle();
		
		$s_data=array();

		$search_id = $_SESSION['printing'][$this->_data['session_key']]['search_id'];
		
		$collection = new DataObjectCollection($model, $this->_schema.'.'.$dataset->name.'_overview');
		
		$collection->getHeadings();		
		$sh = $this->setSearchHandler($collection, $search_id, TRUE);
		
		$sh->setLimit(0);
		
		$this->load_collection($collection, $sh);
		
		parent::printCollection($collection);
		
	}
	
	function view_collection()
	{
		if (!isset($this->_data) || !$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$errors = [];
		
		$dataset = $this->_uses[$this->modeltype];
		
		$model = $this->newModel($dataset);
		
		$title = $model->getTitle();
		
		$s_data=array();

		$this->setSearch('datasetsSearch', 'useDefault', $s_data);
		
		$this->search->setSearchFields($dataset);
		
		$this->search->setSearchData($s_data, $errors);

		$collection = new DataObjectCollection($model, $this->_schema.'.'.$dataset->name.'_overview');
		
		$collection->setTitle($title);
		
		$this->setTemplateName('view_collection');
		
		parent::index($collection);
		
		$this->view->set('title', 'Viewing '.$title);
		
		$sidebar = new SidebarController($this->view);
		
		$actions = array();
		
		$actions['viewall']=array(
					'link'=>array('modules'		=> $this->_modules
								, 'controller'	=> $this->name
								, 'action'		=> 'index'),
					'tag'=>'View All Datasets'
				);
		
		$actions['viewthis']=array(
					'link'=>array('modules'		=> $this->_modules
								, 'controller'	=> $this->name
								, 'action'		=> 'view'
								, $dataset->idField	=> $dataset->{$dataset->idField}),
					'tag'=>'Edit Dataset Definition'
				);
		
		$actions['newdata']=array(
					'link'=>array('modules'		=> $this->_modules
								, 'controller'	=> $this->name
								, 'action'		=> 'edit_data'
								, 'dataset_id'	=> $dataset->{$dataset->idField}),
					'tag'=>'new '.$title
				);
		
		$actions['listreports']=array(
					'link'=>array('module'		=> 'reporting'
								, 'controller'	=> 'reports'
								, 'action'		=> 'index'
								, 'tablename'	=> $this->getViewname($dataset->name)),
					'tag'=>'list_reports'
				);
		
		$sidebar->addList(
			'Actions',
			$actions
		);

		$this->sidebarRelatedItems($sidebar, $dataset);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		$this->view->set('clickaction', 'edit_data');
		$this->view->set('linkdata', array('dataset_id'=>$dataset->{$dataset->idField}));
	}
	
	function view_data()
	{
		if (!isset($this->_data['dataset_id']) || !isset($this->_data['id']))
		{
			$this->dataError();
			sendBack();
		}
		
		$dataset = $this->_uses[$this->modeltype];
		
		$dataset->load($this->_data['dataset_id']);
		
		$model = $this->newModel($dataset);
				
		$model->load($this->_data['id']);
		
	}
	
	function edit_data()
	{
		if (!isset($this->_data['dataset_id']))
		{
			$this->dataError();
			sendBack();
		}
		
		$dataset = $this->_uses[$this->modeltype];
		
		$dataset->load($this->_data['dataset_id']);
		
		$model = $this->newModel($dataset);
		
		if (isset($this->_data['id']))
		{
			$model->load($this->_data['id']);
			$title = 'Edit';
		}
		else
		{
			$title = 'New';
		}
		
		$title .= ' '.$model->getTitle();
		
		$this->view->set('dataset_id', $this->_data['dataset_id']);
		$this->view->set('model', $model);
		$this->view->set('fields', $this->_fields);
		$this->view->set('title', $title);
		
	}
	
	function save_data()
	{
		if (!isset($this->_data['dataset_id']))
		{
			$this->dataError();
			sendBack();
		}
		
		$flash = Flash::Instance();
		
		$errors = array();
		
		$dataset = $this->_uses[$this->modeltype];
		
		$dataset->load($this->_data['dataset_id']);
		
		$model = $this->newModel($dataset);
		
		$save_model = DataObject::Factory($this->_data['DataObject'], $errors, $model);
		
		if (!$save_model || count($errors) > 0 || !$save_model->save())
		{
			$flash->addErrors($errors);
			
			$db = DB::Instance();
			
			$flash->addError('Error saving data to '.$model->getTitle().' : '.$db->ErrorMsg());
		}
		else
		{
			$flash->addMessage($model->getTitle().' saved OK');
		}
		
		if (isset($this->_data['saveAnother']))
		{
			sendTo($this->name, 'edit_data', $this->_modules, array('dataset_id'=>$dataset->{$dataset->idField}));
		}
		
		sendTo($this->name, 'view_collection', $this->_modules, array($dataset->idField=>$dataset->{$dataset->idField}));
		
	}
	
	function update_position()
	{
		
		$errors = array();
		
		// update the current permission position
		if (!DataObject::updatePositions('DatasetField', $this->_data['field_id'], 'position', $this->_data['new_position'], $this->_data['current_position'], $errors))
		{
			echo json_encode(array('success' => FALSE, 'errors' => '<li>Error updating desired position</li>'));
			exit;
		}
		
		echo json_encode(array('success' => TRUE));
		exit;
		
	}
	
	/*
	 * Protected Functions
	 */
	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName('Datasets');
	}
	
	/*
	 * Private functions
	 */
	private function change_table($_data, $_action)
	{
		
		$sqlarray = $this->DropViewSQL($_data['dataset_name']);
		
		$dict = $this->setDictionarySchema();
				
//		$flds is a string defining the field as follows:-
//			field_name
//			field_type - convert the input value into the ADODB type
//			field_length - optional, enclose in brackets if present
//			mandatory_field - optional set to NOTNULL if mandatory
//			field_default_value - optional, preceded by DEFAULT if present
		$flds = $_data['name'];
		
		if ($_action != 'delete')
		{
			$flds.= ' '.Dataset::get_ADODB_field_type($_data['type']);
			
			$flds.= (empty($_data['field_length']))?'':' ('.$_data['length'].')';
			
			$flds.= (empty($_data['mandatory']))?'':' NOTNULL';
			
			$flds.= (empty($_data['default_value']))?'':' DEFAULT '.$_data['default_value'];
		}
		
//		ChangeTableSQL generates incorrect sql syntax for postgres
//		$sqlarray = $dict->ChangeTableSQL($data['dataset_name'], $flds);
		if ($_action == 'alter')
		{
			if ($_data['old_name'] != $_data['name'])
			{
				$sqlarray = array_merge($sqlarray, $dict->RenameColumnSQL($_data['dataset_name'], $_data['old_name'], $_data['name']));
			}
			$sqlarray = array_merge($sqlarray, $dict->AlterColumnSQL($_data['dataset_name'], $flds));
		}
		elseif ($_action == 'delete')
		{
			$sqlarray = array_merge($sqlarray, $dict->DropColumnSQL($_data['dataset_name'], $flds));
		}
		elseif ($_action == 'add')
		{
			$sqlarray = array_merge($sqlarray, $dict->AddColumnSQL($_data['dataset_name'], $flds));
		}
		
		$result = ($dict->ExecuteSQLArray($sqlarray) == 2);
		
		if ($result == 2)
		{
			// Refresh the cache
			$tablename = $this->getTablename($_data['dataset_name']);
			$cache_id		= array('table_fields', $tablename);
				
			$cache = Cache::Instance();
			$cache->delete($cache_id);
			
			Fields::getFields_static($tablename, TRUE);
		}
		
		return ($result == 2);
	}
	
	private function createOverview($dataset, $errors = array())
	{
		$model = $this->newModel($dataset);
		
		$select = 'create view '.$this->getViewname($dataset->name)
				.' as select a.*';
		$from = ' from '.$model->getTableName().' a';
		
		$fk_count = 1;
		// Need to resolve FK definitions
		foreach ($dataset->fields as $field)
		{
			if (!is_null($field->module_component_id))
			{
				$fk_model = DataObjectFactory::Factory($field->fk_link);
				$alias = 'a'.$fk_count++;
				$select .= ','.$alias.'.'.implode('||\'' . $fk_model->identifierFieldJoin . '\'||'.$alias.'.', $fk_model->getIdentifierFields()).' as '.$model->belongsToField[$field->name];
				
				$from .= ($field->mandatory=='t')?'':' LEFT';
				$from .= ' join '.$fk_model->getTableName().' '.$alias
						.' on '.$alias.'.'.$fk_model->idField.' = a.'.$field->name;
			}
		
		}
		
		$db = DB::Instance();
		
		if (!$db->Execute($select.$from))
		{
			$errors[] = 'Error creating overview : '.$db->ErrorMsg();
		}
		
	}
	
	private function create_table($data)
	{
		$dict = $this->setDictionarySchema();
		
		$flds = "id I8 NOTNULL AUTOINCREMENT PRIMARY
				, created T DEFTIMESTAMP
				, createdby C
				, lastupdated T DEFTIMESTAMP
				, alteredby C
				, usercompanyid I8 NOTNULL
				";
		
		$sqlarray = $dict->CreateTableSQL($data['name'], $flds);
		
		return $dict->ExecuteSQLArray($sqlarray);
		
	}
	
	private function DropTableSQL($_name)
	{
		$dict = $this->setDictionarySchema();
				
		return $dict->DropTableSQL($this->getTablename($_name));
	}
	
	private function DropViewSQL($_name, $_dict = '')
	{
		if (empty($_dict))
		{
			$_dict = $this->setDictionarySchema();
		}
		
//		DropViewSQL not yet implemented
//		return $dict->DropViewSQL($this->getViewname($_name));
		$viewname = $this->getViewname($_name, $_dict);
		
		$cols = Fields::getFields_static($viewname);
		
		if (empty($cols))
		{
			return array();
		}
		
		return array('drop view '.$viewname);
	}
	
	private function getTablename($_name, $_dict = '')
	{
		if (empty($_dict))
		{
			$_dict = $this->setDictionarySchema();
		}
		
		return $_dict->TableName($_name);
	}
	
	private function getViewname($_name, $_dict = '')
	{
		return $this->getTablename($_name.'_overview', $_dict);
	}
	
	private function newModel($dataset)
	{
		$model = new DataObject($this->getTablename($dataset->name));
		
		$model->setTitle(prettify($dataset->name));
		
		$display_fields = array();
		
		// Clear the cached fields to make sure we get the latest
		$dataset->clear('fields');
		
		// Need to load FK definitions
		foreach ($dataset->fields as $field)
		{
			if (!is_null($field->module_component_id))
			{
				if (substr($field->name, -3) == '_id')
				{
					$name = str_replace('_id', '', $field->name);
				}
				else
				{
					$name = strtolower(str_replace(' ', '_', $field->title));
				}
				
				$model->belongsTo($field->fk_link, $field->name, $name);
			}
			else
			{
				$name = $field->name;
			}
			
			if ($field->display_in_list == 't')
			{
				$display_fields[$name] = $field->title;
			}
			
			// Note: DataObject::getFields does a sort into alphabetic field name order
			// which was required to dislpay the field list in alphabetic order when looking
			// at DataObject model components - need to get the order of fields from the
			// dataset fields definition (ordered on popsition) so store in the dataset
			// $_fields private array here. Also, the field title is only added to DataObject
			// display fields and we need the title as the field label on the edit data form.
			$this->_fields[$field->name] = $field->title;
		}
		
		if (!empty($display_fields))
		{
			$model->setDisplayFields($model->setDefaultDisplayFields($display_fields));
		}
		
		return $model;
	}
	
	private function setDictionarySchema()
	{
		$db = DB::Instance();
		
		$dict = NewDataDictionary($db);
		
		$dict->SetSchema($this->_schema);
		
		return $dict;
	}
	
}

// End of DatasetsController

<?php

/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

abstract class Controller {

	protected $version='$Revision: 1.120 $';

	private   $_action;
	protected $_uses=array();
	public static $accessControlled=false;
	public $_data=array();
	public    $_templateName;
	public 	  $modeltype;
	protected $saved_models=array();
	protected $saved_model=false;
	protected $relatedFields;
	protected $search;
	protected $sidebar;
	protected $name;
	protected $context=array();
	protected $_templateobject;
	/*
	 * @protected array[]	List of actions that are available via permissions
	 * 						contains array of mandatory parameters
	 * 						e.g. $actions['delete']=array('id','username')
	 */
	protected $actions=array();

	/**
	 * Constructor
	 *
	 * Constructs a controller based on module name
	 * @param string $module module name
	 * @param string $action action name
	 * @todo shouldn't need $module *and* $modules
	 */
	public function __construct($module=null,$view) {

		if (isset($_POST['submit_token']) && !isset($_POST['search_id'])) {
			// This is a form submission - check for double click!
			if (isset($_SESSION['submit_token'][$_POST['submit_token']])) {
				// First time through so delete session token and continue OK
				unset($_SESSION['submit_token'][$_POST['submit_token']]);
			} else {
				// Duplicate submission so go to the results page of the first submit
				// unless the original page had errors, in which case let it continue
				// to re-validate and display the error page
				$current=$_SESSION['submit_token']['current'];
				$flash=Flash::Instance();
				$flash->addMessages($current['messages']);
				$flash->addWarnings($current['warnings']);
				$flash->addErrors($current['errors']);
				$flash->save();
				if (count($current['errors'])==0) {
					sendTo(
						$current['controller'],
						$current['action'],
						$current['modules'],
						isset($current['other']) ? $current['other'] : null
						);
				}
			}
		}

		$this->module=$module;
		$this->view = $view;
		$this->name=strtolower(str_replace('Controller','',get_class($this)));
		$this->view->set('controller',$this->name);
		$system=system::Instance();
		$this->pid=$system->pid;
		$mod_text='module';
		if(!empty($system->modules)) {
			foreach ($system->modules as $module) {
				if (!empty($module)) {
					$this->_modules[$mod_text] = $module;
					$mod_text='sub'.$mod_text;
				}
			}
		} else {
			 $this->_modules[$mod_text]= $module;
		}

		if(!empty($this->_modules) && is_array($this->_modules))
		{
			foreach($this->_modules as $mod)
			{
				$this->_modules_string .='/'.$mod ;
			}
		}
	 }

	public function setView($view) {
		$this->view=$view;
	}

	/**
	 * Set template name
	 *
	 * Sets the template name based on an action name
	 * @param string $action action name
	 */
	public function setTemplateName($action) {
		$this->_templateName=$this->getTemplateName($action);
	}

	/**
	 * Get template name
	 *
	 * Returns the file-path of the template to be used for the current page.
	 * First, there is a check for a user-customised template
	 * If this isn't found, it looks in the standard location for templates
	 * @param string $action action name
	 * @param bool $mustexist
	 * @return string File path for template
	 */

	public function getTemplateName($action,$mustexist=true)
	{
		debug('Controller('.$this->name.')::getTemplateName Looking for template '.$action);

		$module=$this->_modules_string;

		$action = strtolower($action);

		if (!empty($action) && $action[0] == '_')
		{
			$action = substr($action,1);
		}

		return $this->view->getTemplateName($action);

	}

	/**
	 * Index
	 *
	 * Handles the logic behind overview pages
	 * called from extension-classes, which are left to handle actions and setting up the search fields
	 * @param DataObjectCollection $collection
	 */
	public function setSearchHandler(DataObjectCollection $collection, $search_id=NULL, $force_use_session=false) {
		if(!is_null($search_id)) {
			// leave the search as it is
		} elseif (isset($this->_data['search_id'])) {
			$search_id=$this->_data['search_id'];
		} elseif (isset($_GET['search_id'])) {
			$search_id=$_GET['search_id'];
		} else {
			$search_id='';
		}

		// included a parameter to force it to use the session, not sure if that's
		if($force_use_session || (isset($this->search) && isset($this->_data['ajax_print']))
			|| isset($this->_data['orderby'])
			|| isset($this->_data['page']))
		{
			$sh = new SearchHandler($collection, true, false, $search_id);

			$sh->extractOrdering();
			$sh->extractPaging();

		}
		else
		{
			$sh = new SearchHandler($collection, false, false, $search_id);

			$sh->extract();

			if (isset($this->_data['Search']['display_fields']))
			{
				// Set the 'id' field
				$fields[key($sh->fields)] = current($sh->fields);

				// Add the requested search fields
				foreach ($this->_data['Search']['display_fields'] as $fieldname=>$tag)
				{
					$fields[$fieldname] = new DataField($fieldname);
					$fields[$fieldname]->name = $fieldname;
					$fields[$fieldname]->tag = $tag;
				}

				// Now get any id fields
				foreach ($sh->fields as $fieldname=>$field)
				{
					if ($fieldname == 'usercompanyid' || substr($fieldname, -3) == '_id')
					{
						$fields[$fieldname] = $field;
					}
				}

				$sh->setFields($fields);
			}

		}

		return $sh;
	}

	public function index(DataObjectCollection $collection, $sh='', &$c_query = null) {
		showtime('start-controller-index');
		$collection->setParams();
		if (!($sh instanceof SearchHandler)) {
			$sh = $this->setSearchHandler($collection);
		}
		showtime('sh-extracted');

		if(isset($this->search)
		&& !isset($this->_data['orderby'])
		&& !isset($this->_data['page'])) {
			$cc = $this->search->toConstraintChain();
			$sh->addConstraintChain($cc);
			$sh->save();

			// cache the search string

			$search_string_array = array (
				'fop'	=> $this->search->toString('fop'),
				'html'	=> $this->search->toString('html')
			);

			$_SESSION['search_strings'][EGS_USERNAME][$sh->search_id] = $search_string_array;

		}
		// Need to set the orderby of the collection in the searchhandler?
		// But if this is set in the collection, seems to take it
		// so why not here?
		showtime('pre-load');

		$collection->load($sh, $c_query);
		$this->view->set('total_records',$collection->total_records);
		$this->view->set('num_records',$collection->num_records);
		$this->view->set('num_pages',$collection->num_pages);
		$this->view->set('cur_page',$collection->cur_page);

		showtime('post-load');
		$this->view->set(strtolower($collection->getModelName()).'s',$collection);

		if(isset($this->_data['json'])) {
			$this->view->set('echo',$collection->toJSON());
		}
		if ($this->_templateName===false)
		{
			$this->_templateName=$this->getTemplateName('index');
		}
		showtime('end-controller-index');
	}

	private function buildModels($data, &$models) {
		foreach ($data as $key=>$array) {
			if (is_array($array)) {
				if (class_exists($key)) {
					$models[]=array('ModelName'=>$key, 'data'=>$array);
				} else {
					$this->buildModels($array, $models);
				}
			}
		}
	}

	/**
	 * Save
	 *
	 * Passes data to the save function of a model to save an object
	 * @param string $modelName name of model to be saved
	 * @param array $dataIn data to save
	 * @return bool true on success, false on failure
	 */
	public function save($modelName, $dataIn=array(),&$errors=array()) {
		$db=&DB::Instance();
		$flash=Flash::Instance();

		$db->StartTrans();

		if(!empty($dataIn)) {
			$data = $dataIn;
		} else {
			$data = $this->_data;
		}

		$models=array();
		$this->buildModels($data, $models);
		if (count($models)==0) {
			$models[]=array('ModelName'=>$modelName, 'data'=>$data);
		}
// For each DataObject model to be saved
// Create fkfields array for any FK values as determined from the model's 'hasMany' definition
// Check if the model has any FK fields and get the required value from the fkfields array
		$fkfields=array();
		foreach ($models as $key=>$name) {

			if (empty($name['ModelName'])) {
				$flash->addError('Data is invalid for this action');
				$db->FailTrans();
				break;
			}
			$modelname = $name['ModelName'];
			$model=DataObjectFactory::Factory($name['ModelName']);
			if ($model instanceof DataObjectCollection) {
				$datamodel=get_class($model->getModel());
			} else {
				$datamodel=$name['ModelName'];
			}
			foreach ($fkfields as $do=>$fkvalues) {
				if ($do==$datamodel) {
					// model may have fk fields
					foreach ($fkvalues as $fkname=>$fkvalue) {
//						if (empty($name['data'][$fkname])) {
//							$name['data'][$fkname]=$fkvalue;
//						} elseif (is_array($name['data'][$fkname])) {
//							foreach ($name['data'][$fkname] as $key=>$value) {
//								$name['data'][$fkname][$key]=$fkvalue;
//							}
//						}
						if (is_array($name['data'][$fkname])) {
							foreach ($name['data'][$fkname] as $key=>$value) {
								$name['data'][$fkname][$key]=$fkvalue;
							}
						}
						else
						{
							$name['data'][$fkname]=$fkvalue;
						}
					}
				}
			}
// If the model is a sub class, and the fkfield pointing to the parent is empty
// go get the parent class id
			if ((isset($model->subClass) && $model->subClass) && empty($name['data'][$model->fkField])) {
				$parent=get_parent_class($model);
				$parent_class=DataObjectFactory::Factory($parent);
				while (isset($parent_class->subClass) && $parent_class->subClass) {
					$parent=get_parent_class($parent_class);
					$parent_class=DataObjectFactory::Factory($parent);
				}
				foreach ($this->saved_models as $saved_model) {
					if (isset($saved_model[$parent])) {
						$name['data'][$model->fkField]=$saved_model[$parent]->id;
					}
				}
			}
			debug('Controller('.$this->name.')::save Saving model '.$modelname);
			if ($this->saveModel($modelname, $name['data'], $errors)) {
				$this->saved_models[][$modelname]=$this->saved_model;
				// set the fk field on the child models of the saved model
				if ($this->saved_model instanceof DataObject) {
					foreach ($this->saved_model->getHasMany() as $name=>$hasMany) {
						if (!$this->saved_model->subClass) {
							$fkfields[$hasMany['do']][$hasMany['fkfield']]=$this->saved_model->{$this->saved_model->idField};
						}
					}
				}
			} else {
				$db_error=$db->errorMsg();
				if (!empty($db_error)) {
					$flash->addError($db_error);
				}
				$db->FailTrans();
				$db->CompleteTrans();
				return false;
			}
		}
		$success=$db->CompleteTrans();
		if($success) {
			$flash->addMessage($modelName.' saved successfully');
			$this->saved_model=$this->getSavedModel($modelName);
		}

		if (isset($this->_data['saveAnother'])) {
			$this->saveAnother();
		}

		return $success;
	}

	public function saveAnother ()
	{
		$res = getParamsArray();

		if (!empty($res))
		{
			unset($res['controller']);
			unset($res['action']);
			unset($res['modules']);
			unset($res['module']);
			unset($res['pid']);
		}

		foreach ($this->context as $parameter=>$value)
		{
			$res['other'][$parameter]=$value;
		}

		sendTo($_GET['controller'],$this->_data['original_action'],array($_GET['module']),$res['other']);

	}

	public function saveModel($modelName, $dataIn=array(),&$errors=array()) {

		$flash=Flash::Instance();
		if(!empty($dataIn)) {
			$data = $dataIn;
		}
		else {
			$data = $this->_data[$modelName];
		}
		debug('Controller('.$this->name.')::saveModel Validating Model '.$modelName);
//		echo 'Controller('.$this->name.')::saveModel Validating Model '.$modelName.'<pre>'.print_r($data, true).'</pre><br>';

		$model = $modelName::Factory($data, $errors, $modelName);

		if(is_a($model, $modelName)) {
			debug('Controller('.$this->name.')::saveModel Saving Model '.$modelName);
			$success=$model->save();
//			echo 'Controller('.$this->name.')::saveModel Saving Model '.$modelName.' result '.$success.'<br>';
			$this->saved_model=$model;
			if ($model instanceof DataObject) {
// TODO: Need to check this - used by complex structures such as parties?
//		 This seems a bit too complex; perhaps needs to be split up in some way
				$aliases = $model->aliases;
				foreach($aliases as $aliasname=>$alias) {
					if (!$success) {
						break;
					}
					if(isset($data[$aliasname]) && is_array($data[$aliasname])) {
						if (isset($alias['requiredField'])) {
							if (empty($data[$aliasname][$alias['requiredField']])) {
								continue;
							}
						}
						$aliasdata = $data[$aliasname];
						$aliasdata[strtolower($model->get_name()).'_id'] = $model->{$model->idField};
						$aliasmodel= DataObject::Factory($aliasdata,$errors,$alias['modelName']);
						if ($aliasmodel!==false) {
							$success=$aliasmodel->save();
						} else {
							//for debug
						}
					} elseif(isset($data[$aliasname])){
						$aliasdata=array();
						$aliasdata[strtolower(get_class($model)).'_id'] = $model->{$model->idField};
						foreach($alias['constraints'] as $constraint) {
							$aliasdata[$constraint->fieldname]=$constraint->value;
						}
						$aliasdata[$alias['requiredField']]=$data[$aliasname];

						$modelname = $alias['modelName'];
						$aliasmodel = $modelname::Factory($aliasdata, $errors, $alias['modelName']);
						if($aliasmodel!==false) {
							$success=$aliasmodel->save();
						}
					}
				}
			}

//			$this->_data[$model->idField] = $model->{$model->idField};
//			$this->$modelName=$model;
			return $success;
		}
		else {
			$flash->addErrors($errors, strtolower($modelName).'_');
			return false;
		}

	}

	protected function saveFiles($key,Array $filenames) {
		$file = new File();
		$errors=array();
		$data = $_FILES[$key];
		$fields=$file->getFields();
		foreach($filenames as $name) {
			$newdata=array();
			if(!empty($data['name'][$name])) {
				foreach($fields as $fieldname=>$field) {
					if(isset($data[$fieldname][$name]))
						$newdata[$fieldname]=$data[$fieldname][$name];
				}
				$newdata['tmp_name']=$data['tmp_name'][$name];
				$newdata['note']='Image attached to '.$key;
				$$name=File::Factory($newdata,$errors,new File());
				if($$name instanceof File)
					$$name->save();
				$this->_data[$key][$name]=$$name->id;
			}
		}


	}

	/**
	 * Save a collection
	 *
	 * Like save but for multiple records
	 * @param string $modelName name of model to be saved
	 * @param array $dataIn data to save
	 * @return bool true on success, false on failure
	 */
	public function saveCollection($modelName, $datain=array())
	{
		$errors=array();
		$flash=Flash::Instance();
		if(isset($datain) && !empty($datain))
		{
			$data = $datain;
		}
		else
		{
			$data = $this->_data[$modelName];
		}

		$collectionname = $modelName.'Collection';

		$collection = $collectionname::Factory($data, $errors, $modelName);

		if($collection)
		{
			if($collection->save())
			{
				$flash->addMessage('Collection saved successfully');
				return true;
			}
			else
			{
				$flash->addError('Unable to save collection');
				return false;
			}

		}
		else
		{
			$flash->addErrors($errors, strtolower($modelName).'_');
			return false;
		}
	}

	public function getSavedModel($do='') {
		foreach ($this->saved_models as $model) {
			if (isset($model[$do])) {
				return $model[$do];
			}
		}
		return false;
	}

	public function clearSavedModels($do='') {
		if (empty($do))
		{
			$this->saved_models=array();
		}
		else
		{
			foreach ($this->saved_models as $index=>$model) {
				if (isset($model[$do])) {
					unset($this->saved_models[$index]);
				}
			}
		}
	}

	/**
	 * new
	 *
	 * Sets template models used in a controller for a new record stored in view
	 */
	public function _new()
	{

		$models=array();

		foreach($this->_uses as $model)
		{
			$models[get_class($model)]=$model;
		}

		if(isset($_SESSION['formdata']))
		{
			$_POST=$_SESSION['formdata'];
			unset($_SESSION['formdata']);
		}

		$this->view->set('models',$models);

		if (!empty($this->_data['person_id']))
		{
			$person = DataObjectFactory::Factory('Person');
			$person->load($this->_data['person_id']);
			$this->_data['company_id'] = $person->company_id;
		}

		if (isset($this->_data['ajax']))
		{
			// only reason for ajaxing an insert is for dialog display
			unset($this->_data['ajax']);
			$ajax=true;
		}

		if (isset($this->_data['dialog']))
		{
			$this->view->set('dialog', true);
		}

	}

	/**
	 * edit
	 *
	 * Similar to new, but with data
	 */
	public function edit() {

		if (!isset($this->_data) || !$this->loadData()) {
// we are editing data, but either no id has been provided
// or the data for the supplied id does not exist
			$this->dataError();
			sendBack();
		}

		if (isset($this->_data['ajax'])) {
			// only reason for ajaxing an edit is for dialog display
			unset($this->_data['ajax']);
			$ajax=true;
		}

		$this->_new();
		$this->_templateName = $this->getTemplateName('edit');

		if (empty($this->_templateName))
		{
			$this->_templateName = $this->getTemplateName('new');
		}
	}

	protected function loadData ()
	{

		$loadcount=0;
// what about loading dependant data; do we need to register
// the name of the data field to use to load the model's data
		foreach($this->_uses as $modeltype)
		{
			$loaded = false;
			$model = get_class($modeltype);
			if($this->modeltype==$model && !empty($this->_data[$modeltype->idField]))
			{
				$id=$this->_data[$modeltype->idField];
				$loaded = true;
			}
			elseif (isset($this->_data[$model]['id']) && !empty($this->_data[$model][$modeltype->idField]))
			{
				$id=$this->_data[$model][$modeltype->idField];
				$loaded = true;
			}
			if($loaded)
			{
				$object = &$this->_uses[$model];
				$object->load($id);
				if ($object->isLoaded())
				{
					$loadcount++;
				}
			}
		}

		if ($loadcount>0) {
			return true;
		} else {
			return false;
		}

	}

	/*
	 * Load the model and store in the _uses array
	 */
	protected function loadUsesData($modelname = '', $id = '')
	{
		if (empty($modelname) || empty($id))
		{
			return;
		}

		if (!isset($this->_uses[$modelname]))
		{
			$this->_uses[$modelname] = DataObjectFactory::Factory($modelname);
		}

		if (!$this->_uses[$modelname]->isLoaded())
		{
			$this->_uses[$modelname]->load($id);
		}

	}

	/*
	 * get the instance of the specified object from the _uses array
	 */
	public function getUsesModel($modelname = '')
	{

		if (!empty($modelname) && isset($this->_uses[$modelname]))
		{
			return $this->_uses[$modelname];
		}

		return FALSE;

	}

	public function cancel()
	{

		$flash=Flash::Instance();

		$flash->addMessage('Action cancelled');

		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	}

	/**
	 * delete
	 *
	 * Instructs a model to delete a record based on id
	 */
	public function delete($modelName)
	{

		$flash=Flash::Instance();

		if ($modelName InstanceOf DataObject)
		{
			$model		= $modelName;
			$modelName	= get_class($model);
		}
		else
		{
//			$model = new $modelName();
			$model = DataObjectFactory::Factory($modelName);
		}

		$errors = array();

		if (isset($this->_data[$modelName][$model->idField]))
		{
			$id = $this->_data[$modelName][$model->idField];
		}
		elseif (isset($this->_data[$model->idField]))
		{
			$id = $this->_data[$model->idField];
		}
		else
		{
			$id = '';
		}

		if ($model->delete($id, $errors))
		{
			$flash->addMessage($modelName.' deleted successfully');
			return true;
		}
		else
		{
			$errors[] = $modelName.' not deleted successfully';
			$flash->addErrors($errors, strtolower($modelName).'_');
			return false;
		}

	}

	public function setData($array,$subarray=null) {
		if (is_array($array)&&!isset($subarray)) {
			$this->_data=$this->_data+$array;
		}
		else {
			$this->_data[$subarray] = array();
			$this->_data[$subarray] = $array;
		}
	}

	protected function setSearch($do, $method, $defaults=array(), $params=array(), $use_saved_search=false)
	{

		$errors = array();
		$s_data = array();

		if (isset($this->_data['search_id']))
		{
			$defaults['search_id'] = $this->_data['search_id'];
		}
		elseif (isset($this->_data['Search']['search_id']))
		{
			$defaults['search_id'] = $this->_data['Search']['search_id'];
		}
		elseif (!isset($this->_data['orderby'])
				 && !isset($this->_data['page']))
		{
			$defaults['search_id'] = strtotime('now');
		}

		if(isset($this->_data['Search']))
		{
			$s_data = $this->_data['Search'];
		}
		elseif (!isset($this->_data['orderby'])
			&& !isset($this->_data['page'])
			&& !$use_saved_search)
		{
			$s_data = $defaults;
		}

		// Call static method
		$this->search = $do::$method( $s_data, $errors, $defaults, $params);

		if(count($errors)>0)
		{
			$flash = Flash::Instance();
			$flash->addErrors($errors);
			$this->search->clear();
		}
		else
		{

			if (!empty($this->search->display_fields))
			{
				$this->_data['Search']['display_fields'] = $this->search->display_fields;
			}
			elseif(is_array($this->_data['Search']) && isset($this->_data['Search']['clear']) && isset($this->_data['Search']['display_fields']))
			{
				unset($this->_data['Search']['display_fields']);
			}

			$_GET['search_id'] = $this->search->getValue('search_id');
		}

	}

	/*
	 * store an instance of the specified object in the _uses array
	 */
	protected function uses($model, $primary=true)
	{

		if(is_string($model))
		{
			$model = DataObjectFactory::Factory($model);
		}

		$modelname = get_class($model);

		$this->_uses[$modelname] = $model;

		if ($primary)
		{
			$this->modeltype = $modelname;
		}

		return $modelname;

	}

	/**
	 * @todo change name
	 */
	function assignModels() {
		$jsos=array();
		foreach($this->_uses as $name=>$model) {
			$this->view->set($name,$model);
		//	$title = $model->{$model->getIdentifier()};
		//	$jsos[$name]=$model->toJSON();
		}
		if (!$this->view->get('page_title')) {
			$this->view->set('page_title',$this->getPageName());
		}
		//$this->view->set('jsos',$jsos);
		$this->view->set('controller_data',$this->_data);

		// we need to make sure the template path is fully qualified path, otherwise we
		// might find that smarty cannot find the correct file... and we wouldn't want that

		if(substr($this->_templateName, 0, strlen(FILE_ROOT)) !== FILE_ROOT) {
			$template_name=FILE_ROOT.$this->_templateName;
		} else {
			$template_name=$this->_templateName;
		}

		$this->view->set('templateName',$template_name);
		if(isset($this->search)) {
			$this->view->set('search',$this->search);
		}
	}

	protected function getPageName($base=null,$action=null)
	 {

		$inflector = new Inflector();
		if($base==null)
		{
			$base = str_replace('Controller','',get_class($this));
		}

		if (isset($this->_data['original_action']) && !empty($this->_data['original_action']))
		{
			$this->_action=$this->_data['original_action'];
		}
		elseif (isset($this->_data['action']) && !empty($this->_data['action']))
		{
			$this->_action=$this->_data['action'];
		}
		elseif (!is_null($action)) {
			$this->_action=$action;
		}
		else
		{
			$this->_action='';
		}

		$title = ($this->_templateobject instanceof DataObject)?$this->_templateobject->getTitle():'';

		$title = (empty($title))?$base:$title;

		if (isset($this->_data['pid']) && isset($this->_action))
		{
			$permission=DataObjectFactory::Factory('Permission');
			$permission->load($this->_data['pid']);

			if ($permission
				&& ((!empty($this->_action) && strtolower($permission->permission)==strtolower($this->_action))
					|| (empty($this->_action) && $permission->type=='c')))
			{

				$action = (!is_null($permission->title)?$permission->title:$this->_action);

				if (!empty($action) || !empty($title))
				{
					return $action.'_'.(!empty($title)?' - '.$title:'');
				}

			}

		}

		switch($this->_action)
		{
			case 'new':
			case '_new';
				$name= 'new_'.$inflector->singularize($title);
				break;
			case 'edit':
				$name= 'edit_'.$inflector->singularize($title);
				break;
			case 'index':
				$name = 'viewing_'.$inflector->pluralize($inflector->singularize($title));
				break;
			default:
				$name = $this->_action.'_'.$title;
		}

		return $name;

	}

	protected function checkParams ($params) {
		if (!is_array($params)) {
			$params=array($params);
		}
		foreach ($params as $param) {
			if (!isset($this->_data[$param])) {
				$this->dataError();
				return false;
			}
		}
		return true;
	}

	protected function dataError ($message='Data is invalid for this action') {
		$flash = Flash::Instance();
		if (isset($_SESSION['refererPage']['modules']['module']) && $_SESSION['refererPage']['modules']['module']=='login') {
			$flash->addError('Action has been cancelled due to session time out');
		} else {
			$flash->addError($message);
		}
	}

	/**
	 * Set dependency injector
	 */
	public function setInjector(&$injector) {
		$this->_injector=$injector;
	}

	/**
	* fills a collection of the specified model type with the fields specified,
	* also gives correct click controller, action and edit handlers so
	* that smarty datatable will work correctly.
	* finally outputs specified smarty variable as the collection for
	* datatable to use
	* used for alternate controller to display specific contents of a different
	* controller
	*/

	public function fillCollection($modelname, $fields, $constraints, $clickcontroller, $clickaction, $editclickaction, $deletecontroller, $smartyname, $tablename=null, $deleteaction=null, $newtext=null, $limit=null, $orderdir=null, $offset=null) {
		$collectionname = $modelname.'Collection';
		$collection = new $collectionname();
		$sh = new SearchHandler($collection);
		$sh->fields = $fields;
		$sh->constraints = $constraints;
		$sh->extractOrdering();
		$sh->extractPaging();
		$sh->perpage = 900000;
		if (isset($orderdir))
			$sh->orderdir = $orderdir;
		if (isset($limit) && isset($offset))
			$sh->setLimit($limit, $offset);
		if (isset($tablename))
			$collection->_tablename = $tablename;
		$collection->load($sh);
		$collection->clickcontroller = $clickcontroller;
		$collection->clickaction = $clickaction;
		$collection->editclickaction = $editclickaction;
		$collection->deletecontroller = $deletecontroller;
		if (isset($deleteaction))
			$collection->deleteclickaction = $deleteaction;
		if (isset($newtext))
			$collection->newtext = $newtext;
		$this->view->set($smartyname,$collection);
	}

	/**
	 * This is here so that viewXXXXXX() can be called for anything that you think you might want to see
	 *
	 */
	public function __call($method,$args) {

		if(strtolower(substr($method,0,4))=='view') {
			$view_name=substr($method,4);
			$this->viewRelated($view_name);
			return true;
		}

		if(strtolower(substr($method,0,3))=='get') {
			if (isset($this->_data['ajax']) && isset($this->_data['id'])) {
				$value=array();
				$id = $this->_data['id'];
				$inflector = new Inflector();
				$property = $inflector->pluralize(strtolower(substr($method,3)));
//				$model = new $this->modeltype;
				$model = DataObjectFactory::Factory($this->modeltype);
				if (method_exists($model, $method)) {
					// Use by:-
					// 1) ajax calls passing in the id of the selected object
					//    which may not be the id of controllers model
					unset($value);
					$value = $model->$method($id, $this->_data);
				} else {
					$model->load($id);
					$hasMany = $model->getHasMany();
					if (isset($hasMany[$property])) {
						$collection = $model->$property;
						$this->_templateName = 'get';
						$json = json_encode($collection->getAssoc());
					} elseif (isset($model->belongsToField[$property])) {
						$belongsTo = $model->belongsTo[$model->belongsToField[$property]];
//						$newModel = new $belongsTo['model'];
						$newModel = DataObjectFactory::Factory($belongsTo['model']);
						unset($value);
						$value = $newModel->getAll();
					} else {
						$property = substr($method,3);
						$value = $model->$property;
						$newModel = substr($property,0,-3);
//						$newModel = new $newModel;
						$newModel = DataObjectFactory::Factory($newModel);
						$newModel->load($value);
						unset($value);
						$value[$newModel->{$newModel->idField}]  = $newModel->{$newModel->getIdentifier()};
					}
				}
				echo json_encode($value);
				exit;
			}
		}
//		$this->index();
		$flash=Flash::Instance();
		$flash->addError('Invalid action '.$this->modeltype.' '.$method.' in '.$this->name);
		sendback();
	}

	public function refresh($template=null, $action=null) {
// Redisplay original page after form error

		if (!empty($this->_data['original_action'])) {
			$action=empty($action)?$this->_data['original_action']:$action;
			$this->view->set('action', $action);
			$this->_templateName=$this->getTemplateName(empty($template)?$this->_data['original_action']:$template);
			$this->{$action=='new'?'_new':$action}();
		}

	}

	public function view() {

		$flash=Flash::Instance();
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$model=$this->_uses[$this->modeltype];
		$this->view->set('model',$model);

		$sidebar = new SidebarController($this->view);

		$sidebarlist=array();

		$sidebarlist['all']=array('link'=>array('modules'=>$this->_modules
											   ,'controller'=>$this->name
											   ,'action'=>'index'
											   ),
								  'tag'=>'view all '.$model->getTitle()
								 );

		$sidebar->addList('Actions', $sidebarlist);

		$sidebarlist=array();

		$sidebarlist['view']=array('link'=>array('modules'=>$this->_modules
											   ,'controller'=>$this->name
											   ,'action'=>'view'
											   ,$model->idField=>$model->{$model->idField}
											   ),
								  'tag'=>'view'
								 );
		$sidebarlist['new']=array('link'=>array('modules'=>$this->_modules
											   ,'controller'=>$this->name
											   ,'action'=>'new'
											   ),
								  'tag'=>'new '.$model->getTitle()
								 );
		$sidebarlist['edit']=array('link'=>array('modules'=>$this->_modules
											   ,'controller'=>$this->name
											   ,'action'=>'edit'
											   ,$model->idField=>$model->{$model->idField}
											   ),
								  'tag'=>'edit'
								 );
		$sidebarlist['delete']=array('link'=>array('modules'=>$this->_modules
											   ,'controller'=>$this->name
											   ,'action'=>'delete'
											   ,$model->idField=>$model->{$model->idField}
											   ),
								  'tag'=>'delete'
								 );

		$sidebar->addList('This '.$model->getTitle(), $sidebarlist);

		$this->sidebarRelatedItems($sidebar, $model);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	protected function viewRelated($name)
	{

		if (empty($this->modeltype))
		{
			$system=System::Instance();
			$this->dataError('Unable to action request - check the registration of the '.$system->modules['module'].'  module');
			sendBack();
		}

		$collectionName = $this->modeltype.'Collection';

//		$model=new $this->modeltype;
		$model=DataObjectFactory::Factory($this->modeltype);

		$related_collection=new $collectionName($model);

		$sh=$this->setSearchHandler($related_collection);

		$qstring=$_GET;
		unset($qstring['module']);
		unset($qstring['page']);
		unset($qstring['orderby']);
		unset($qstring['action']);
		unset($qstring['controller']);
		unset($qstring['get'.$name]);
		unset($qstring['ajax']);
		unset($qstring['id']);
		unset($qstring['pid']);
		unset($qstring['_']);

		$sh->constraints = new ConstraintChain();

		if ($model->isField('usercompanyid'))
		{
			$sh->addConstraint(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		}

		$link=array();
		$link['module']=$_GET['module'];
		$link['controller']=str_replace('Controller','',get_class($this));
		$link['action']='view'.$name;

		foreach($qstring as $key=>$value)
		{
			if ($key == 'type')
			{
				$value = ucfirst($value);
			}
			$sh->addConstraint(new Constraint($key,'=',$value));
			$link[$key]=$value;
		}

		unset($sh->fields[$name]);
		unset($sh->fields[$name.'_id']);

		$related_collection->load($sh);

		$this->_templateName=$this->getTemplateName('view_related');

		$c_action=(isset($this->related[$name]['clickaction'])?$this->related[$name]['clickaction']:'view');

		if (isset($this->related[$name]['allow_delete']) && $this->related[$name]['allow_delete'])
		{
			$this->view->set('allow_delete',true);
		}

		if(isset($this->related[$name]['include_id']))
		{
			$c_action.='&'.$name.'_id='.$_GET[$name.'_id'];
		}

		$this->view->set('clickaction',$c_action);
		$this->view->set('related_collection',$related_collection);
		$this->view->set('num_pages',$related_collection->num_records);
		$this->view->set('num_pages',$related_collection->num_pages);
		$this->view->set('cur_page',$related_collection->cur_page);
		$this->view->set('paging_link',$link);
		$this->view->set('no_ordering',true);

		if($this->modeltype=='Project' || $this->modeltype == 'WebpageRevision' || $this->modeltype=='OrderItem')
		{
			$this->view->set('no_delete',true);
		}

	}

	protected function sidebarActions(SidebarController $sidebar, DataObject $model, $actions = array())
	{

		$sidebarlist=array();

		$sidebarlist['all']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 ),
					'tag'=>'view all '.$model->getTitle()
				);

		$sidebarlist['new']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 ),
					'tag'=>'new '.$model->getTitle()
				);

		$sidebar->addList(
			'All Actions',
			$sidebarlist
		);

		$sidebarlist = array();

		if (!isset($actions['view']) || $actions['view'])
		{
			$sidebarlist['view']=array(
						'link'=>array('modules'=>$this->_modules
									 ,'controller'=>$this->name
									 ,'action'=>'view'
									 ,'id'=>$model->id
									 ),
						'tag'=>'view'
					);
		}

		if (!isset($actions['edit']) || $actions['edit'])
		{
			$sidebarlist['edit']=array(
						'link'=>array('modules'=>$this->_modules
									 ,'controller'=>$this->name
									 ,'action'=>'edit'
									 ,'id'=>$model->id
									 ),
						'tag'=>'edit'
					);
		}

		if (!isset($actions['delete']) || $actions['delete'])
		{
			$sidebarlist['delete']=array(
						'link'=>array('modules'=>$this->_modules
									 ,'controller'=>$this->name
									 ,'action'=>'delete'
									 ,'id'=>$model->id
									 ),
						'tag'=>'delete'
					);
		}

		$sidebar->addList(
			'this '.$model->getTitle(),
			$sidebarlist
		);

	}

	/**
	 * Model 'related' sidebar
	 *
	 * @param SidebarController $sidebar
	 * @param DataObject $model
	 * @param array $whitelist only show actions with these names in the related sidebar
	 */
	protected function sidebarRelatedItems(SidebarController $sidebar, DataObject $model, $whitelist=NULL)
	{

		$sidebarlist = array();

		// need to get the module name from the controller name
		$action_name=array('new'=>'new');

		foreach ($model->getLinkRules() as $name => $hasmany)
		{
			if (method_exists($this, $name))
			{
				$controller_name		= $this->name;
				$action_name['link']	= $name;
				$field					= $hasmany['field'];
			}
			elseif (method_exists($this, 'view'.$name))
			{
				$controller_name		= $this->name;
				$action_name['link']	= 'view' . $name;
				$field					= $hasmany['field'];
			}
			elseif (method_exists($this, 'view_' . $name))
			{
				$controller_name		= $this->name;
				$action_name['link']	= 'view_'.$name;
				$field					= $hasmany['field'];
			}
			else
			{
				$controller_name		= $hasmany['do'].'s';
				$action_name['link']	= 'view_'.strtolower(str_replace(' ', '_', $model->getTitle()));
				$field					= $hasmany['fkfield'];
			}

			$link = array();

			foreach ($hasmany['actions'] as $action)
			{
			    if (!is_null($whitelist) && !in_array($name, $whitelist)){
			        continue;
			    }

				if ($action == 'new')
				{
					$controller_name	= $hasmany['do'].'s';
					$field				= $hasmany['fkfield'];
				}

				$modules = isset($hasmany['modules'][$action]) ? $hasmany['modules'][$action] : $this->_modules;

				if (isset($hasmany['newtab'][$action]))
				{

					$link[$action] = array(
						'modules'		=> $modules,
						'controller'	=> $controller_name,
						'action'		=> strtolower($action_name[$action]),
						$field			=> $model->{$model->idField},
						'newtab'		=> TRUE
					);

				}
				else
				{

					$link[$action] = array(
						'modules'		=> $modules,
						'controller'	=> $controller_name,
						'action'		=> strtolower($action_name[$action]),
						$field			=> $model->{$model->idField}
					);

				}

			}

			if (!empty($link))
			{

				$sidebarlist[$name] = array_merge(
					array('tag' => (isset($hasmany['label']) ? $hasmany['label'] : 'Show ' . $name)),
					$link
				);

			}

		}

		$sidebar->addList('related_items', $sidebarlist);

	}

	public function sharing($model='') {
		$flash=Flash::Instance();
		if (!$this->checkParams(array('id', 'model'), $flash)) {
			sendTo();
		}
		if (empty($model)) {
			$modelname=$this->_data['model'];
		} else {
			$modelname=$model;
		}
		$object=$this->_uses[$modelname];
		$object->load($this->_data['id']);
// What if 'owner' is not a field on the model?
		if ($object->owner != EGS_USERNAME && !isModuleAdmin()) {
// We're not the owner, are we /really/ allowed to read this object?
			$objectPermissions = new ObjectRoleCollection();
			if ($objectPermissions->getRows($object->id, $object->getTableName(), 'write')->count() == 0) {
				if (empty($model)) {
					$flash=Flash::Instance();
					$flash->addError('You do not have permission to edit this '.$modelname);
					sendTo($this->name,'view',$this->_data['module'], array('id' => $this->_data['id']));
				}
				return false;
			}
		}

		$roles=array();

		$roleCollection = new RoleCollection();
		$sh = new SearchHandler($roleCollection, false);
		$sh->AddConstraint(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
		$roleCollection->load($sh);


//		$ObjectRole = new ObjectRole;
		$ObjectRole = DataObjectFactory::Factory('ObjectRole');
		$writeRoles=$ObjectRole->getRoleID($this->_data['id'], $object->getTableName(), 'write');

		if ($writeRoles === false) { $writeRoles = array(); }

		foreach ($roleCollection->getContents() as $role) {
			$roles[$role->id]['name'] = $role->name;

			if (array_key_exists($role->id, $writeRoles)) {
				$roles[$role->id]['selected'] = true;
			}
		}

		$this->view->set('writeRoles',$roles);

//		$ObjectRole = new ObjectRole;
		$ObjectRole = DataObjectFactory::Factory('ObjectRole');
		$readRoles=$ObjectRole->getRoleID($this->_data['id'], $object->getTableName(), 'read');

		if ($readRoles === false) { $readRoles = array(); }

		foreach ($roleCollection->getContents() as $role) {
			$roles[$role->id]['name'] = $role->name;

			if (array_key_exists($role->id, $readRoles)) {
				$roles[$role->id]['selected'] = true;
			}
		}

		$this->view->set('readRoles',$roles);

		// FIXME: I'm sure this isn't the way this is done
		$this->view->set('id',$this->_data['id']);
		$this->view->set('model_name',$this->_data['model']);
		$this->view->set('model',$object);

		return true;
	}

	public function sharingsave($model='') {
		// FIXME: Add injection protection
		$flash=Flash::Instance();
		if (!$this->checkParams(array('id', 'model'), $flash)) {
			sendTo();
		}
		if (empty($model)) {
			$modelname=$this->_data['model'];
		} else {
			$modelname=$model;
		}
		$object=$this->_uses[$modelname];
		$object->load($this->_data['id'],true);

		// If we own it, we can do anything we like.
// What if 'owner' is not a field on the model?
		if ($object->owner != EGS_USERNAME && !isModuleAdmin()) {
			// We're not the owner, are we /really/ allowed to read this company?
			$objectPermissions = new ObjectRoleCollection();
			if ($objectPermissions->getRows($object->id, $object->getTableName(), 'write')->count() == 0) {
				if (empty($model)) {
					$flash=Flash::Instance();
					$flash->addError('You do not have permission to edit this '.$modelname);
					sendTo($this->name,'view',$this->_data['module'], array('id' => $this->_data['id']));
				}
				return false;
			}
		}

// Get rid of existing roles for this object

//		$objectrole = new ObjectRole();
		$objectrole = DataObjectFactory::Factory('ObjectRole');
		$objectrole->deleteAll($objectrole->getIds($object->id, $object->getTableName()));

		// Note permissions that are needed and for which roles
		$roles = array();

		if (isset($this->_data['read'])) {
			foreach ($this->_data['read'] as $role) {
				$roles[$role][] = 'read';
			}
		}
		if (isset($this->_data['write'])) {
			foreach ($this->_data['write'] as $role) {
				$roles[$role][] = 'write';
			}
		}
		foreach ($roles as $role => $permissions) {
			$roles_data=array();
			$roles_data['object_id']=$this->_data['id'];
			$roles_data['object_type']=$object->getTableName();
			$roles_data['role_id']=$role;

			foreach ($permissions as $permission) {
				$roles_data[$permission]=true;
			}
			$errors=array();
			$objectrole=ObjectRole::Factory($roles_data,$errors,'ObjectRole');
			$objectrole->save();
		}

		$flash = Flash::instance();
		$flash->addMessage('Sharing changes saved.');
		if (empty($model)) {
			sendTo($this->name,'view',$this->_data['module'], array('id' => $this->_data['id']));
		}
		return true;
	}

	/*
	 * return the complete _uses array
	 */
	public function usesModels () {
		return $this->_uses;
	}

	protected function getDefaultValue($model, $field, $value) {
		if (isset($this->_uses[$model])) {
			$field=$this->_uses[$model]->getField($field);
			if ($field->has_default) {
				return $field->default_value;
			}
		}
		return $value;
	}

/*
 * public getOptions
 *
 * parameters	$_model
 * 				$_field
 * 				$_action
 * 				$_function
 * 				$_smarty_params
 * 				$_depends
 * 				$_identifierField
 *
 */
	public function getOptions($_model='', $_field='', $_action='', $_function='', $_smarty_params=array(), $_depends=array(), $_identifierField='') {
		if (empty($_action)) {
			$_action='getOptions';
		}
		if (empty($_function)) {
			$_function='getOptions';
		}

		if (empty($_model)) {
			$_model=$this->_templateobject;
		} elseif (is_string($_model)) {
//			$_model=new $_model;
			$_model=DataObjectFactory::Factory($_model);
		}
		if (empty($_field)) {
			if (!empty($this->_data['field'])) {
				$_field=$this->_data['field'];
			} else {
				return array();
			}
		}
		$field_options=new fieldOptions();
		if (isset($this->_data['autocomplete'])) {
			$field_options->_autocomplete=true;
			$field_options->_autocomplete_value=$this->_data['id'];
		}
		if (!empty($_depends)) {
			$field_options->_depends=$_depends;
		} elseif (!empty($this->_data['depends'])) {
			$array=explode(',', $this->_data['depends']);
			foreach ($array as $key) {
				if (!empty($this->_data[$key])) {
					$_depends[$key]=$this->_data[$key];
				}
			}
			if (empty($_smarty_params['depends'])) {
				$_smarty_params['depends']=$this->_data['depends'];
			}
			$field_options->_depends=$_depends;
		}
		if (!empty($_identifierField)) {
			$field_options->_identifierField=$_identifierField;
		} elseif (!empty($this->_data['identifierfield'])) {
			$field_options->_identifierField=explode(',', $this->_data['identifierfield']);
		}
		if (isset($_smarty_params['use_collection']) && $_smarty_params['use_collection']) {
			$field_options->_use_collection=true;
		}
		if (isset($this->_data['use_collection']) && $this->_data['use_collection']) {
			$field_options->_use_collection=true;
		}
		$field_options->_modules=$this->_modules;
		$field_options->_controller=$this->name;
		$field_options->_action=$_action;
		$_model->setOptions($_field, $field_options);
		$options=$_model->$_function($_field);
//
//	Need to return in one of four ways:-
//	1) response to autocomplete request - return encoded data
//	2) indirect ajax request - return html to calling function
//	3) direct ajax request - echo html and exit
//	4) not an ajax call - just return the data array
//
		if (isset($this->_data['autocomplete']) && $options->_autocomplete) {
			//	1) response to autocomplete request is autocomplete
			//     - return encoded data
			echo json_encode(DataObject::toJSONArray($options->_data));
			exit;
		} elseif(isset($this->_data['ajax']) || isset($this->_data['ajax_call'])) {
			$this->view->set('model', $_model);
			$this->view->set('attribute', $_field);
			foreach ($_smarty_params as $key=>$value) {
				$this->view->set($key, $value);
			}
			if (current($options->_data)=='None') {
				$this->view->set('nonone', true);
			}
			$html=$this->view->fetch('select');
			if (isset($this->_data['ajax_call'])) {
				//	2) indirect ajax request - return html to calling function
				return $html;
			} else {
				//	3) direct ajax request - echo html and exit
				$output[$_field]=array('data'=>$html,'is_array'=>is_array($html));
				$this->view->set('data',$output);
				echo $this->view->fetch('ajax_multiple');
				exit;
			}
		} else {
			//	4) not an ajax call - just return the data array
			return $options->_data;
		}

	}

	protected function buildSelect ($_model='', $_field='', $_data=array(), $_value='', $_smarty_params=array(), $_template='select') {
		if (empty($_model)) {
			$_model=$this->_templateobject;
		}
		$this->view->set('model', $_model);
		$this->view->set('attribute', $_field);
		$this->view->set('options', $_data);
		if (!key_exists($_value, $_data))
		{
			$_value = key($_data);
		}
		$this->view->set('value', $_value);
		foreach ($_smarty_params as $key=>$value) {
			$this->view->set($key, $value);
		}
		return $this->view->fetch($_template);

	}

	protected function getOtherParams () {
		$data=$this->_data;
		unset($data['module']);
		unset($data['action']);
		unset($data['controller']);
		unset($data['id']);
		unset($data['pid']);
		return $data;
	}

	public function version() {
		return $this->version;
	}

	protected function returnJSONResponse($status,$extra=array()) {

		/*
		header('Content-type: application/json');
		$response = array();
		$response['status']=$status;
		if(is_array($extra) && !empty($extra)) {
			$response+=$extra;
		}
		audit(print_r($this->_data,true).print_r($response,true));
		return json_encode($response);
		*/

		returnJSONResponse($status,$extra);

	}

	public function getProgress() {
// Used by Ajax to return data written by a parallel process
// e.g. to update a progress bar

		echo json_encode(empty($this->_data['monitor_name'])?0:$_SESSION[$this->_data['monitor_name']]);
		exit;

	}

}

// End of Controller

<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModuledefaultsController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new ModuleDefault();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		
		// Search
		$errors=array();
	
		$s_data=array();

// Set context from calling module
		$s_data['module']='';
				
		$this->setSearch('ModuleDefaultSearch', 'useDefault', $s_data);
		
		parent::index(new ModuleDefaultCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'New Module Default'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('page_title', $this->getPageName('', 'List'));
		$this->view->set('clickaction', 'edit');
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null) {

		if (!$this->CheckParams($this->_templateobject->idField)) {
			sendBack();
		}
				
		$flash = Flash::Instance();
		parent::delete($this->modeltype);
		sendTo('modulecomponents','view',$this->_modules, array('id'=>$this->_data['module_components_id']));
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {

		if(!$this->checkParams($this->modeltype)) {
			sendBack();
		}
		
		if (isset($this->_data[$this->modeltype]['_checkbox_exists_default_value'])) {
			if (isset($this->_data[$this->modeltype]['default_value'])) {
				$this->_data[$this->modeltype]['default_value']='true';
			} else {
				$this->_data[$this->modeltype]['default_value']='false';
			}
		}
		$flash=Flash::Instance();
		if(parent::save($this->modeltype)) {
			sendTo('modulecomponents','view',$this->_modules, array('id'=>$this->_data[$this->modeltype]['module_components_id']));
		}
		$this->_data=$this->_data[$this->modeltype];
		$this->refresh();
	}	

	public function edit() {
		$this->view->set('field_name', $this->_data['field_name']);
		
		$modulecomponent=new ModuleComponent();
		$modulecomponent->load($this->_data['module_components_id']);
		$model=new $modulecomponent->name;
		$field=$model->getField($this->_data['field_name']);
		$this->view->set('field', $field);
		if(isset($model->belongsToField[$field->name])) {
			$x = $model->belongsTo[$model->belongsToField[$field->name]]["model"];
			$cc = new ConstraintChain();
			if ($model->belongsTo[$model->belongsToField[$field->name]]["cc"] instanceof ConstraintChain) {
				$cc->add($model->belongsTo[$model->belongsToField[$field->name]]["cc"]);
			}
			$x = new $x();
			$this->view->set('options', $x->getAll($cc));
			$field->type='select';
		}
		if ($model->isEnum($field->name)) {
			$this->view->set('options', $model->getEnumOptions($field->name));
			$field->type='select';
		}
		
		
		if (empty($this->_data['id'])) {
//			sendTo($this->name,'new',$this->_modules);
			unset($this->_data['id']);
			$this->view->set('module_components_id', $this->_data['module_components_id']);
			parent::_new();
			$this->_templateName=$this->getTemplateName('new');
		} else {
			parent::edit();
		}
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'Module Defaults ':$base), $action);
	}

}
?>

<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DatadefinitiondetailsController extends EdiController {

	protected $_templateobject;
	protected $version='$Revision: 1.9 $';
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new DataDefinitionDetail();
		$this->uses($this->_templateobject);

	}

	public function index(){

		if (isset($this->_data['data_definition_id'])) {
			$other=array('id'=>$this->_data['data_definition_id']);
		} else {
			$other=array();
		}
		sendTo('datadefinitions','view',$this->_modules,$other);
			
	}

	public function delete(){
		
		if (!$this->CheckParams($this->_templateobject->idField)) {
			sendBack();
		}

		parent::delete($this->modeltype);
		
		$this->index();
	
	}
	
	public function _new() {
		parent::_new();
		
		$datadefdetail=$this->_uses[$this->modeltype];
		
		if ($datadefdetail->isLoaded()) {
			$cc=new ConstraintChain();
			$cc->add(new Constraint('data_definition_id', '=', $datadefdetail->data_definition_id));
			$this->view->set('parent', $datadefdetail->getAll($cc));
		} elseif (isset($this->_data['data_definition_id'])) {
			$datadefdetail=new DataDefinitionDetail();
			$cc=new ConstraintChain();
			$cc->add(new Constraint('data_definition_id', '=', $this->_data['data_definition_id']));
			$this->view->set('parent', $datadefdetail->getAll($cc));
		} else {
			$this->view->set('parent', array());
		}
	}
	
	public function view () {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$datadef=$this->_uses[$this->modeltype];
		$this->view->set('datadefinitiondetails', $datadef);
		
		$this->view->set('datamap', $datadef->data_map);
		$this->view->set('datamappingrule', $datadef->data_mapping_rule);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'actions',
			array(
				'View All' => array(
					'tag' => 'View All Data Definition Details',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 )
				),
				'Add' => array(
					'tag' => 'Add Data Definition Detail',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 )
				)
			)
		);
		
		$sidebarlist=array();
		$sidebarlist[$datadef->element] = array(
					'tag' => 'view',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'view'
								 ,'id'=>$datadef->id
								 )
				);
		$sidebarlist['delete'] = array(
					'tag'=>'delete',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'delete'
								 ,'id'=>$datadef->id
								 )
				);
		
		$sidebar->addList($datadef->element, $sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('page_title',$this->getPageName());
		
	}
	
	protected function getPageName($base='',$action='') {
		return parent::getPageName((empty($base)?'Data Definition Details':$base),$action);
	}

}
?>

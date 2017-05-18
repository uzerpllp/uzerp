<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DatamappingsController extends EdiController {

	protected $_templateobject;
	protected $version='$Revision: 1.9 $';
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new DataMapping();
		$this->uses($this->_templateobject);

	}

	public function index(){
		$this->view->set('clickaction', 'view');
		
		$s_data=array();

		$this->setSearch('datamappingsSearch', 'useDefault', $s_data);
		
		parent::index(new DataMappingCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'edi'
								 ,'controller'=>$this->name
								 ,'action'=>'new'
									   ),
					'tag'=>'new data mapping'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function view() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$mapping = $this->_uses[$this->modeltype];
		
		$this->view->set('clickcontroller', 'datamappingrules');
		$this->view->set('linkvaluefield', 'data_mapping_rule_id');
		$this->view->set('clickaction','view');
		$this->view->set('mapping',$mapping);
		
		$mapping_rule_ids=array();
		foreach ($mapping->data_mapping_rules as $mappingrule) {
			$mapping_rule_ids[]=$mappingrule->id;
		}
//		echo '<pre>'.implode(',', $mapping_rule_ids).'</pre><br>';
		$definitiondetails=new DataDefinitionDetailCollection(new DataDefinitionDetail());
		$sh=$this->setSearchHandler($definitiondetails);
		$cc=new ConstraintChain();
		$cc->add(new Constraint('data_mapping_id', '=', $mapping->id));
		$sh->addConstraintChain($cc);
		
		if (count($mapping_rule_ids)>0) {
			$cc=new ConstraintChain();
			$cc->add(new Constraint('data_mapping_rule_id', 'in', '('.implode(',', $mapping_rule_ids).')'));
			$sh->addConstraintChain($cc, 'OR');
		}
		parent::index($definitiondetails, $sh);

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'actions',
			array(
				$mapping->name => array(
					'tag' => 'View All Mappings',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 )
				)
			)
		);
		
		$sidebarlist=array();
		$sidebarlist[$mapping->name] = array(
					'tag' => 'view',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'view'
								 ,'id'=>$mapping->id
								 )
				);
		$sidebarlist['edit'] = array(
					'tag'=>'edit',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'edit'
								 ,'id'=>$mapping->id
								 )
				);
		$sidebarlist['delete'] = array(
					'tag'=>'delete',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'delete'
								 ,'id'=>$mapping->id
								 )
				);
		if (!is_null($mapping->internal_type) && $mapping->isLookupModel()) {
			$sidebarlist['Add Mapping Detail'] = array(
					'tag'=>'add '.$mapping->name.' mapping',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'datamappingdetails'
								 ,'action'=>'new'
								 ,'mapping_id'=>$mapping->id
								 )
				);
		}		

		$sidebar->addList($mapping->name.' Mapping', $sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	protected function getPageName($base='',$action='') {
		return parent::getPageName((empty($base)?'Data Mappings':$base),$action);
	}

}
?>

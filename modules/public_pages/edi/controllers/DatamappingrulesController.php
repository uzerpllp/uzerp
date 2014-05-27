<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DatamappingrulesController extends EdiController {

	protected $_templateobject;
	protected $version='$Revision: 1.7 $';
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new DataMappingRule();
		$this->uses($this->_templateobject);

	}

	public function index(){
		$this->view->set('clickaction', 'view');
		parent::index(new DataMappingRuleCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'edi'
								 ,'controller'=>$this->name
								 ,'action'=>'new'
									   ),
					'tag'=>'new data mapping rule'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('page_title',$this->getPageName());
	}

	public function view() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$mappingrule = $this->_uses[$this->modeltype];
		
		$this->view->set('clickaction','view');
		$this->view->set('mappingrule',$mappingrule);
		
		$childrules=new DataMappingRuleCollection(new DataMappingRule());
		$sh=$this->setSearchHandler($childrules);
		$sh->addConstraint(new Constraint('parent_id', '=', $mappingrule->id));
		parent::index($childrules, $sh);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'actions',
			array(
				$mappingrule->name => array(
					'tag' => 'View All Mapping Rules',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 )
				)
			)
		);
		
		$sidebarlist=array();
		$sidebarlist[$mappingrule->name] = array(
					'tag' => 'view',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'view'
								 ,'id'=>$mappingrule->id
								 )
				);
		$sidebarlist['edit'] = array(
					'tag'=>'edit',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'edit'
								 ,'id'=>$mappingrule->id
								 )
				);
		$sidebarlist['delete'] = array(
					'tag'=>'delete',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'delete'
								 ,'id'=>$mappingrule->id
								 )
				);

		$sidebar->addList($mappingrule->external_system.' '.$mappingrule->name, $sidebarlist);
		
		if (!is_null($mappingrule->parent_id)) {
			$mappingrule->addLinkRule(array('data_translations'=>array()));
		}
		$this->sidebarRelatedItems($sidebar, $mappingrule);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('page_title',$this->getPageName());
	}

	protected function getPageName($base='',$action='') {
		return parent::getPageName((empty($base)?'data mapping rules':$base),$action);
	}

}
?>

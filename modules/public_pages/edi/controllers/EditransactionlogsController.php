<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EditransactionlogsController extends EdiController {

	protected $_templateobject;
	protected $version='$Revision: 1.5 $';
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new EDITransactionLog();
		$this->uses($this->_templateobject);

	}

	public function index(){
		$this->view->set('clickaction', 'view');
		$collection=new EDITransactionLogCollection($this->_templateobject);
		$sh=$this->setSearchHandler($collection);
		if (isset($this->_data['data_definition_id'])) {
			$sh->addConstraint(new COnstraint('data_definition_id', '=', $this->_data['data_definition_id']));
			$datadef=new DataDefinition();
			$datadef->load($this->_data['data_definition_id']);
			$this->view->set('datadef',$datadef);
		}
		parent::index(new EDITransactionLogCollection($this->_templateobject), $sh);
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'externalsystems'
								 ,'action'=>'index'
									   ),
					'tag'=>'view external systems'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function viewDataDefinition () {
		sendTo($this->name
			  ,'index'
			  ,$this->_modules
			  ,array('data_definition_id'=>$this->_data['data_definition_id']));
	}
	
	protected function getPageName($base='',$action='') {
		return parent::getPageName((empty($base)?'edi transaction log':$base),$action);
	}

}
?>

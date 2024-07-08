<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SalespersonsController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new SalesPerson();
		$this->uses($this->_templateobject);
	
		

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		global $smarty;
		$this->view->set('clickaction', 'edit');
		parent::index(new SalesPersonCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'crm','controller'=>'SalesPersons','action'=>'new'),
					'tag'=>'new_sales_person'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null){
		$flash = Flash::Instance();
		parent::delete('SalesPerson');
		sendBack();
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		if(parent::save('SalesPerson'))
			sendBack();
		else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}
	}
}
?>

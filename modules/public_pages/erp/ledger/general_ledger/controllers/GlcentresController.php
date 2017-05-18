<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GlcentresController extends printController {

	protected $version='$Revision: 1.6 $';
	protected $_templateobject;
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new GLCentre();
		$this->uses($this->_templateobject);

	}

	public function index(){
		$errors=array();

		$this->setSearch('glcentresSearch', 'useDefault');

		$this->view->set('clickaction', 'view');
		parent::index(new GLCentreCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'viewall'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'glaccounts'
											 ,'action'=>'index'
											 )
									   ),
					'tag'=>'View All Accounts'
				),
				'viewbalances'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'glbalances'
											 ,'action'=>'index'
											 )
									   ),
					'tag'=>'View Balances'
				),
				'viewtransactions'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'gltransactions'
											 ,'action'=>'index'
											 )
									   ),
					'tag'=>'View Transactions'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function view(){
		$id=$this->_data['id'];
		$transaction=&$this->_uses['GLCentre'];
		$transaction->load($id);
		$this->view->set('transaction',$transaction);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'viewall'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>$this->name
											 ,'action'=>'index'
											 )
									   ),
					'tag'=>'View All Centres'
				),
				'viewbalances'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'glbalances'
											 ,'action'=>'index'
											 ,'id'=>$id
											 ,'type'=>'centres'
											 )
									   ),
					'tag'=>'View Balances'
				),
				'viewtransactions'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'gltransactions'
											 ,'action'=>'index'
											 ,'id'=>$id
											 ,'transtype'=>'centres'
											 )
									   ),
					'tag'=>'View Transactions'
				)
					)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
}
?>

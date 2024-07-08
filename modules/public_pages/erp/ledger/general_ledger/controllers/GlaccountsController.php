<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GlaccountsController extends printController {

	protected $version='$Revision: 1.9 $';
	protected $_templateobject;
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new GLAccount();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$errors=array();
		
		$this->setSearch('glaccountsSearch', 'useDefault');

		$this->view->set('clickaction', 'view');
		parent::index(new GLAccountCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'viewall'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'glcentres'
											 ,'action'=>'index'
											 )
									   ),
					'tag'=>'View All Centres'
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
		$transaction=&$this->_uses['GLAccount'];
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
					'tag'=>'View All Accounts'
				),
				'viewbalances'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'glbalances'
											 ,'action'=>'index'
											 ,'id'=>$id
											 ,'type'=>'accounts'
											 )
									   ),
					'tag'=>'View Balances'
				),
				'viewtransactions'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'gltransactions'
											 ,'action'=>'index'
											 ,'id'=>$id
											 ,'transtype'=>'accounts'
											 )
									   ),
					'tag'=>'View Transactions'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function getCentres($_id='') {
	
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}
		
		$account=$this->_uses['GLAccount'];
		
		if(!empty($_id)) {
			$account->load($_id);
		}
		
		$centres=$account->getCentres();

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$centres);
			$this->setTemplateName('select_options');
		} else {
			return $centres;
		}
	}
	
}
?>

<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WhactionsController extends ManufacturingController {

	protected $version='$Revision: 1.10 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new WHAction();
		$this->uses($this->_templateobject);
	}

// This is a temporary solution to display the list of available actions
	public function actionsMenu(){
		$this->view->set('clickaction', 'new');
		$this->view->set('clickcontroller', 'STTransactions');
		$this->view->set('linkfield', 'whaction_id');
		$this->view->set('linkvaluefield', 'id');
		$collection = new WHActionCollection($this->_templateobject);
		$sh = new SearchHandler($collection, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('type', '=', 'M'));
		$sh->extractOrdering();
		$sh->extractPaging();
		$collection->load($sh);
		$this->view->set('whactions',$collection);
		$this->view->set('num_pages',$collection->num_pages);
		$this->view->set('cur_page',$collection->cur_page);
		
	}
	
	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'view');
		$this->view->set('clickcontroller', $this->name);
		parent::index(new WHActionCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>$this->name
											 ,'action'=>'new')
										),
					'tag'=>'New Action'
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
		$transaction=$this->_uses[$this->modeltype];
		$id=$transaction->id;
		$this->view->set('transaction',$transaction);

		$elements = new WHTransferruleCollection(new WHTransferrule);
		$sh = new SearchHandler($elements, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('whaction_id','=', $id));
		$sh->extractOrdering();
		$sh->extractPaging();
		$elements->load($sh);
		$this->view->set('elements',$elements);
		
		$sidebar=new SidebarController($this->view);
		$sidebar->addList('Show',
				array(
					'all' => array(
						'tag' => 'All Actions',
						'link' => array_merge($this->_modules
											 ,array('controller'=>$this->name
												   ,'action'=>'index')
											 )
					),
					'locations' => array(
						'tag' => 'All Stores',
						'link' => array_merge($this->_modules
											 ,array('controller'=>'WHStores'
												   ,'action'=>'index')
											 )
					)
				)
		);
		
		$sidebarlist=array();
		$sidebarlist['edit']= array(
					'tag' => 'Edit',
					'link' => array_merge($this->_modules
										 ,array('controller'=>$this->name
											   ,'action'=>'edit'
											   ,'id'=>$id)
										 )
				);
		$sidebarlist['delete']= array(
					'tag' => 'Delete',
					'link' => array_merge($this->_modules
										 ,array('controller'=>$this->name
											   ,'action'=>'delete'
											   ,'id'=>$id)
										 )
				);
		if (is_null($transaction->max_rules) || $elements->count()<$transaction->max_rules) {
			$sidebarlist['Add']= array(
						'tag' => 'Add Rule',
						'link' => array_merge($this->_modules
											 ,array('controller'=>'WHTransferrules'
												   ,'action'=>'new'
												   ,'whaction_id'=>$id)
											 )
					);
		}
		$sidebar->addList('This Action',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('clickaction', 'view');
		$this->view->set('clickcontroller', 'WHTransferrules');
		$this->view->set('no_ordering',true);
		
	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'actions':$base), $action);
	}

}
?>
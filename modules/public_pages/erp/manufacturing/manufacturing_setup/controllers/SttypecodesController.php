<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SttypecodesController extends ManufacturingController {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new STTypecode();
		$this->uses($this->_templateobject);
	
	}

	public function index($collection = null, $sh = '', &$c_query = null){
		// Set search defaults and class
		$s_data = ['active' => 'T'];
		$this->setSearch('STTypecodeSearch', 'useDefault', $s_data);

		$this->view->set('clickaction', 'view');
		parent::index(new STTypecodeCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'New Stock Type Code'
							,'link'=>array_merge($this->_modules
												,array('controller'=>$this->name
													  ,'action'=>'new'
													  )
												)
							)
				 )
			);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new() {
		parent::_new();
		$whaction=new WHAction();
		$stitem = new STItem();

		// Check if this type code is in use on any stock items.
		// If it is in use, changing the comp class will not be allowed on the edit form.
		// This enables the comp class to be changed if the user chooses the wrong one.
		$in_use = false;
		if ($this->_data['id'] !=''){
			$items = new STItemCollection();
			$sh = new SearchHandler($items, false);
			$sh->addConstraint(new Constraint('type_code_id', '=',  $this->_data['id']));
			$sh->setLimit(1);
			$items->load($sh);

			if (count($items) > 0) {
				$in_use = true;
			}
		}
		$this->view->set('in_use', $in_use);

		$this->view->set('backflush_actions',$whaction->getActions('B'));
		$this->view->set('complete_actions',$whaction->getActions('C'));
		$this->view->set('issue_actions',$whaction->getActions('I'));
		$this->view->set('return_actions', $whaction->getActions('X'));
		$this->view->set('despatch_actions',$whaction->getActions('D'));
		$this->view->set('comp_class',$stitem->getEnumOptions('comp_class'));
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		$flash = Flash::Instance();
		$form_data = $this->_data['STTypecode'];
		if (isset($form_data['comp_class']) && $form_data['comp_class'] == ''){
			$flash->addError('A Comp Class must be specified');
			sendBack();
		} else {
			parent::save();
		}
	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName('Stock Type Codes');
	}
}
?>
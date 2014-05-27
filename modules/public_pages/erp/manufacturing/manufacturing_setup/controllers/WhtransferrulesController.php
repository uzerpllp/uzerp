<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WHtransferrulesController extends ManufacturingController {

	protected $version='$Revision: 1.9 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new WHTransferrule();
		$this->uses($this->_templateobject);
	
	}

	public function index(){
		$this->view->set('clickaction', 'edit');
		parent::index(new WHTransferruleCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'New Transfer Rule'
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

	public function delete(){
		$flash = Flash::Instance();
		Controller::delete($this->modeltype);
		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		
	}

	public function _new(){
		
		parent::_new();

		$transferrule=$this->_uses[$this->modeltype];
		
// Get the Action
		$whaction=new WHAction();
		
		if ($transferrule->isLoaded())	{
			$whaction_id=$transferrule->whaction_id;
		} elseif (isset($this->_data['whaction_id'])) {
			$whaction_id=$this->_data['whaction_id'];
		} else {
			$flash=Flash::Instance();
			$flash->addError('Data invalid for this action');
			$this->sendBack();
		}
		$whaction->load($whaction_id);

// Get the From Locations list
		$locations=new WHLocationCollection(new WHLocation);
		$cc=new ConstraintChain();
		$names=array('has_balance', 'bin_controlled', 'saleable');
		foreach ($names as $name) {
			$type=$whaction->getFormatted('from_'.$name);
			if ($type!='All') {
				$cc->add(new Constraint($name, 'is', $type));
			}
		}
		$from_location=$locations->getLocationList($cc);
		$this->view->set('from_location',$from_location);

// Get the To Locations list
		$locations=new WHLocationCollection(new WHLocation);
		$cc=new ConstraintChain();
		foreach ($names as $name) {
			$type=$whaction->getFormatted('to_'.$name);
			if ($type!='All') {
				$cc->add(new Constraint($name, 'is', $type));
			}
		}
		$to_location=$locations->getLocationList($cc);
		$this->view->set('to_location',$to_location);

	}
	
	public function view() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$transaction=$this->_uses[$this->modeltype];
		$id=$transaction->id;
		$this->view->set('transaction',$transaction);

		$from_store=WHLocation::getStoreLocation($transaction->from_whlocation_id);
		$this->view->set('from_store',$from_store);
		$to_store=WHLocation::getStoreLocation($transaction->to_whlocation_id);
		$this->view->set('to_store',$to_store);
		
		$sidebar=new SidebarController($this->view);
		$sidebar->addList('Show',
			array(
				'all' => array('tag' => 'All rules for Action'
							  ,'link' => array_merge($this->_modules
													,array('controller'=>'WHActions'
														  ,'action'=>'view'
														  ,'id'=>$transaction->whaction_id
														  )
													)
					)
				)
		);
		$sidebar->addList('This Rule',
			array(
				'edit' => array('tag' => 'Edit'
							   ,'link' => array_merge($this->_modules
													 ,array('controller'=>$this->name
														   ,'action'=>'edit'
														   ,'id'=>$id
														   )
													 )
					),
				'delete' => array('tag' => 'Delete'
								 ,'link' => array_merge($this->_modules
													   ,array('controller'=>$this->name
															 ,'action'=>'delete'
															 ,'id'=>$id
															 ,'whaction_id'=>$transaction->whaction_id
															 )
													   )
					)
				)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function getFromLocations($_id='') {
// Used by Ajax to return From Locations list after selecting the Transfer Action

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}
		
		$whtransferrules = new WHTransferruleCollection(new WHTransferrule);
		$sh=new SearchHandler($whtransferrules, false);
		$sh->addConstraint(new Constraint('whaction_id', '=', $_id));
		$whtransferrules->load($sh);

		$locations=array();
		if ($whtransferrules->count()>0) {
			foreach ($whtransferrules as $whtransferrule) {
				$locations[$whtransferrule->from_whlocation_id]=$whtransferrule->from_location;
			}
		}
		
		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$locations);
			$this->setTemplateName('select_options');
		} else {
			return $locations;
		}
	}
	
	public function getToLocations($_id='',$_whaction_id='') {
		
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
			if(!empty($this->_data['whaction_id'])) { $_whaction_id=$this->_data['whaction_id']; }
		}
		
// Used by Ajax to return From Locations list after selecting the Transfer Action
		$whtransferrules = new WHTransferruleCollection(new WHTransferrule);
		$sh=new SearchHandler($whtransferrules, false);
		$sh->addConstraint(new Constraint('whaction_id', '=', $_whaction_id));
		$sh->addConstraint(new Constraint('from_whlocation_id', '=', $_id));
		$whtransferrules->load($sh);

		$locations=array();
		if ($whtransferrules->count()>0) {
			foreach ($whtransferrules as $whtransferrule) {
				$locations[$whtransferrule->to_whlocation_id]=$whtransferrule->to_location;
			}
		}
			
		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$locations);
			$this->setTemplateName('select_options');
		} else {
			return $locations;
		}
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName('Stock Transfer Rules');
	}

}
?>

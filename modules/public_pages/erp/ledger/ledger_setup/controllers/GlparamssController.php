<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GlparamssController extends LedgerController {

	protected $version='$Revision: 1.9 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new GLParams();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'edit');
		parent::index(new GLParamsCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['new']= array(
							'tag'=>'New Parameter',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'new'
													 )
											   )
							);
		$sidebar->addList('Actions',$sidebarlist);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}

	public function _new(){

		$flash = Flash::Instance();
		parent::_new();

		$gl_params=$this->_uses[$this->modeltype];
		
		$unassigned = $gl_params->unassignedParams();
		if (count($unassigned)>0) {
			$this->view->set('unassigned',$unassigned);
		} elseif (count($unassigned)==0 && $this->_data['action']=='new' ) {
			$flash->addMessage('All parameters have been assigned');
			sendTo($this->name, 'index', $this->_modules);
			
		} elseif ($this->_data['action']=='new' ) {
			$flash->addError('Error getting Parameter List');
			sendback();
		}
		
		if (isset($_POST[$this->modeltype]['paramdesc'])) {
			$this->selectlist($_POST[$this->modeltype]['paramdesc']);
		} elseif ($gl_params->isLoaded()) {
			$this->selectlist($gl_params->paramdesc);
			$this->view->set('selected',$gl_params->paramvalue_id);
		} else {
			$this->selectlist(key($unassigned));
		}

		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['view']= array(
							'tag'=>'View Parameters',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'index'
													 )
											   )
							);
		$sidebar->addList('Actions',$sidebarlist);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}
	
	public function selectlist ($_id='') {
// Used by Ajax to return select list after selecting the parameter type
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}

		$gl_params=$this->_templateobject;
		$selectlist=$gl_params->getSelectList($_id);
		$this->view->set('glparams',$gl_params);
		$this->view->set('selectlist',$selectlist);
	}
	
	public function defaultProductAccount () {
// Used by Ajax to return Default Product Account after selecting the item
		if(isset($this->_data['ajax']) && !empty($this->_data['id'])) {
			$gl_params=$this->_templateobject;
			echo json_encode($gl_params->product_account());
		} else {
			echo json_encode(0);
		}
		exit;
	}
	
	public function defaultProductCentre () {
// Used by Ajax to return Default Product Centre after selecting the item
		if (isset($this->_data['ajax']) && !empty($this->_data['id'])) {
			$gl_params=$this->_templateobject;
			echo json_encode($gl_params->product_centre());
		} else {
			echo json_encode(0);
		}
		exit;
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'GL Parameters':$base), $action);
	}

}
?>

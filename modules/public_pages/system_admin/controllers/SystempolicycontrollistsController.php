<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SystempolicycontrollistsController extends Controller {

	protected $version='$Revision: 1.2 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = DataObjectFactory::Factory('SystemPolicyControlList');
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		
		// Search
		$errors=array();
	
		$s_data=array();

// Set context from calling module
				
//		$this->setSearch('SystemPolicySearch', 'useDefault', $s_data);
		
		parent::index(new SystemPolicyControlListCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'New System Policy Access List'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('page_title', $this->getPageName('', 'List'));
		$this->view->set('clickaction', 'edit');
		$this->view->set('sidebar',$sidebar);
		
	}

	public function delete($modelName = null) {

		$result = parent::delete($this->modeltype);
		
		sendTo($this->name, 'index', $this->_modules);
		
	}

	public function _new() {
		
		parent::_new();
		
		$policypermission = $this->_uses[$this->modeltype];
		
		if ($policypermission->isLoaded())
		{
			$this->_data['object_policies_id'] = $policypermission->object_policies_id;
		}
		
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {

		if(!$this->checkParams($this->modeltype)) {
			sendBack();
		}
		
		$errors = array();
//		echo 'SystempolicycontrollistsController::save data<pre>'.print_r($this->_data, true).'</pre><br>';
//		exit;
		if (count($errors) > 0 || !parent::save($this->modeltype))
		{
			$this->refresh();
		}
		
		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
			
	}	

/*
 * Protected functions
 */
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'System Policy':$base), $action);
	}

/*
 * Private Functions
 */
/*
 * Output functions - called by ajax
 */
}

// End of SystempolicycontrollistsController

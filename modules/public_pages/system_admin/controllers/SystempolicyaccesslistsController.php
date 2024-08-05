<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SystempolicyaccesslistsController extends Controller {

	protected $version='$Revision: 1.1 $';

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = DataObjectFactory::Factory('SystemPolicyAccessList');
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){

		// Search
		$errors=array();

		$s_data=array();

// Set context from calling module
		$s_data['access_type']='';
		$s_data['name']='';

//		$this->setSearch('SystemPolicySearch', 'useDefault', $s_data);

		parent::index(new SystemPolicyAccessListCollection($this->_templateobject));

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
		$this->view->set('sidebar',$sidebar);

		$this->view->set('page_title', $this->getPageName('', 'List'));

		$this->view->set('clickaction', 'view');
	}

	public function _new() {

		parent::_new();

		$access_list = $this->_uses[$this->modeltype];

		$access_types = $access_list->getEnumOptions('access_type');

		if (!$access_list->isLoaded())
		{
			$access_list->access_type = key($access_types);
		}

		$this->view->set('access_types', $access_types);
		$this->view->set('options', $this->get_values($access_list->access_type));

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {

		if(!$this->checkParams($this->modeltype)) {
			sendBack();
		}

		if (!parent::save($this->modeltype))
		{
			$this->refresh();
		}

		sendTo($this->name, 'index', $this->_modules);

	}	

	public function view()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$accesslist = $this->_uses[$this->modeltype];

//		$this->addSidebar($systempolicy);

		$policy_permissions = new SystemPolicyControlListCollection();

		$sh = $this->setSearchHandler($policy_permissions);
		$sh->addConstraint(new Constraint('access_lists_id', '=', $accesslist->{$accesslist->idField}));

		parent::index($policy_permissions, $sh);

		$this->view->set('no_ordering', true);

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['alllists']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 ),
					'tag'=>'View All System Access Lists'
					);

		$sidebar->addList('Actions',$sidebarlist);

		$sidebarlist = array();

		$sidebarlist['edit']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'edit'
								 ,'id'=>$accesslist->{$accesslist->idField}),
					'tag'=>'edit_access_list'
					);

		$sidebarlist['delete']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'delete'
								 ,'id'=>$accesslist->{$accesslist->idField}),
					'tag'=>'delete_access_list'
					);

		$sidebarlist['addpermission']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'systempolicycontrollists'
								 ,'action'=>'_new'
								 ,'object_policies_id'=>$accesslist->{$accesslist->idField}),
					'tag'=>'add_policy_permission'
					);

		$sidebar->addList('This Policy',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	/**
	 * Delete
	 *
	 * @param $modelName
	 * @return void
	 */
	public function delete($modelName = null) {
		$result = parent::delete($this->modeltype);
		sendTo($this->name, 'index', $this->_modules);
	}

/*
 * Protected functions
 */
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'System Policy Access Lists':$base), $action);
	}

/*
 * Private Functions
 */

/*
 * Output functions - called by ajax
 */
	public function get_values($_access_type = '')
	{

		if (isset($this->_data['access_type'])) { $_access_type = $this->_data['access_type']; }

		$values = array();

		if (!empty($_access_type))
		{
//			$access_type = DataObjectFactory::Factory($_access_type);
//			$values = $access_type->getAll();
			$values = $this->_templateobject->getAccessValues($_access_type);

		}

		if (isset($this->_data['ajax']))
		{
			$this->view->set('options',$values);
			echo $this->view->fetch('select_options');
			exit;
		}
		else
		{
			return $values;
		}

	}

}

// End of SystempolicysController

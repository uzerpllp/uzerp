<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class RolesController extends Controller
{

	protected $version='$Revision: 1.19 $';

	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{

		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('Role');

		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		parent::index(new RoleCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);

		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'admin','controller'=>'roles','action'=>'new'),
					'tag'=>'New Role'
				)
			)
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('clickaction', 'view');
		$this->view->set('sidebar',$sidebar);

	}

	public function delete($modelName = null)
	{

		$flash = Flash::Instance();

		parent::delete('Role');

		sendTo('Roles','index',array('admin'));

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		$flash=Flash::Instance();

		$errors=array();

		$db = DB::Instance();
		$db->StartTrans();	

		if(isset($this->_data['permission']))
		{
			$permissions=$this->_data['permission'];
			unset($this->_data['permission']);
		}
		else
		{
			$permissions=array();
		}

		if(isset($this->_data['admin']))
		{
			$admin=$this->_data['admin'];
			unset($this->_data['admin']);
		}
		else
		{
			$admin=array();
		}

		if(parent::save('Role'))
		{
			$role = $this->saved_model;
			if(isset( $this->_data['Role']['users'])&&is_array( $this->_data['Role']['users']))
			{
				$users = $this->_data['Role']['users'];
				Role::setUsers($role, $users, $errors);
				$flash->addErrors($errors);
			}
			if(($role->setPermissions($permissions, $errors))&&($role->setAdmin($admin)))
			{
				$db->CompleteTrans();
				sendTo('Roles','index', array('admin'));
			}
		}

	//	$db->FailTrans();
	//	$db->CompleteTrans();

		$this->refresh();

	}

	public function _new()
	{

		parent::_new();

		$role = $this->_uses['Role'];

		$users = DataObjectFactory::Factory('User');
		$this->view->set('users',$users->getActive());

		$companypermissions = DataObjectFactory::Factory('Companypermission');
		$modulepermissions = $companypermissions->getAll();

// Note: If no company permissions have been defined ($modulepermissions is empty)
//       then all permissions will be displayed; i.e. default is to allow access to
//       all permissions if no company permissions override

		$permissions = new PermissionCollection(DataObjectFactory::Factory('Permission'));

		$this->view->set('items',$permissions->getPermissionTree($modulepermissions));

		$this->view->set('permissions_tree',$this->getTemplateName('permissions_tree'));

		$sidebar = new SidebarController($this->view);

		$sidebar->addList(
			'Actions',
			array(
				'viewall'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name),
					'tag'=>'View All Roles'
				)
			)
		);

		if ($role->isLoaded())
		{
			$hasrole = DataObjectFactory::Factory('hasRole');

			$this->view->set('current_users', $hasrole->getUsers($role->{$role->idField}));

			$this->view->set('current',$role->getPermissions());

			$sidebar->addList(
				'Actions',
				array(
				    'View'=>array(
						'link'=>array('modules'=>$this->_modules
									 ,'controller'=>$this->name
									 ,'action'=>'view'
									 ,'id'=>$role->{$role->idField}),
						'tag'=>'View "'.$role->getIdentifierValue().'" role"'
					)
				)
			);
		}
		else
		{
			$this->view->set('current',array());
		}

		$this->view->register('sidebar',$sidebar);
		$this->view->set('clickaction', 'view');
		$this->view->set('sidebar',$sidebar);

	}

	private function addSidebar($role)
	 {

		$sidebar = new SidebarController($this->view);

		$roleid=$role->{$role->idField};

		$sidebar->addList(
			'Actions',
			array(
				'All'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name),
					'tag'=>'View all roles'
				)
				,'View'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'view'
								 ,'id'=>$roleid),
					'tag'=>'View "'.$role->getIdentifierValue().'" role"'
				)
				,'edit'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'edit'
								 ,'id'=>$roleid),
					'tag'=>'Edit'
				)
				,'spacer'
				,'delete'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'delete'
								 ,'id'=>$roleid),
					'tag'=>'Delete'
				)
			)
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function view()
	{

		$flash=Flash::Instance();

		if (!$this->loadData())
		{
			sendBack();
		}

		$role=$this->_uses['Role'];

		if($role===false)
		{
			sendBack();
		}

		$this->addSidebar($role);	

		$moduleadmin = DataObjectFactory::Factory('ModuleAdmin');
		$moduleadmins = $moduleadmin->getModuleName($role->{$role->idField});
		$this->view->set('moduleadmin',$moduleadmins);

		$this->view->set('no_ordering',true);
		$this->view->set('reports',$role->getReports());
		$this->view->set('users',$role->getUsers());
		$companypermissions = DataObjectFactory::Factory('Companypermission');
		$modulepermissions = $companypermissions->getAll();

// Note: If no company permissions have been defined ($modulepermissions is empty)
//       then all permissions will be displayed; i.e. default is to allow access to
//       all permissions if no company permissions override

		$permissions = new PermissionCollection(DataObjectFactory::Factory('Permission'));

		$this->view->set('items',$permissions->getPermissionTree($modulepermissions));

		$this->view->set('permissions_tree',$this->getTemplateName('permissions_tree'));
		$this->view->set('current',$role->getPermissions());
		$this->view->set('view',true);

	}

}

// End of RolesController

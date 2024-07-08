<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MfdeptsController extends ManufacturingController
{

	protected $version='$Revision: 1.9 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = new MFDept();
		$this->uses($this->_templateobject);
	
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$errors=array();
		$s_data=array();
		
// Set context from calling module
		if (isset($this->_data['id']))
		{
			$s_data['id'] = $this->_data['id'];
		}
		
		$this->setSearch('mfdeptsSearch', 'useDefault', $s_data);
		
		parent::index(new MFDeptCollection($this->_templateobject));
		
		$this->view->set('clickaction', 'view');
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'New Dept'
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

	public function view()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$transaction=$this->_uses[$this->modeltype];
		
		$id=$transaction->id;
		
		$this->view->set('transaction',$transaction);

		$elements = new MFCentreCollection(new MFCentre);
		
		$sh = $this->setSearchHandler($elements);

		$sh->addConstraint(new Constraint('mfdept_id','=', $id));
		
		parent::index($elements, $sh);
		
		$sidebar=new SidebarController($this->view);

		$sidebarlist=array();
		
		$sidebarlist['all']= array(
					'tag'  => 'All Departments',
					'link' => array_merge($this->_modules
												,array('controller'=>$this->name
													  ,'action'=>'index'
													  )
										 )
								  );
		
		$sidebar->addList('Show',$sidebarlist);
		
		$sidebarlist=array();

		$sidebarlist['edit']= array(
					'tag'  => 'Edit',
					'link' => array_merge($this->_modules
												,array('controller'=>$this->name
													  ,'action'=>'edit'
													  ,'id'=>$id
													  )
										 )
								  );
								  
		if ($elements->count()==0)
		{
			$sidebarlist['delete']= array(
					'tag'  => 'Delete',
					'link' => array_merge($this->_modules
												,array('controller'=>$this->name
													  ,'action'=>'delete'
													  ,'id'=>$id
													  )
										 )
								  );
		}
		
		$sidebarlist['add']= array(
					'tag'  => 'Add Centre',
					'link' => array_merge($this->_modules
												,array('controller'=>'MFCentres'
													  ,'action'=>'new'
													  ,'mfdept_id'=>$id
													  )
										 )
								  );
		
		$sidebar->addList('This Department',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('clickaction', 'view');
		$this->view->set('clickcontroller', 'MFCentres');
		
	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName('departments', $action);
	}

}

// End of MfdeptsController

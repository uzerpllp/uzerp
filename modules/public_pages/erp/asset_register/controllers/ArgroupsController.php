<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ArgroupsController extends Controller
{

	protected $version='$Revision: 1.6 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('ARGroup');
		
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		
		$this->view->set('clickaction', 'view');
		
		parent::index(new ARGroupCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist=array();
		
		$sidebarlist['showall']= array(
							'tag'=>'View All Assets',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'assets'
													 ,'action'=>'index'
													 )
											   )
							);

		$sidebarlist['viewtrans']= array(
							'tag'=>'View All Transactions',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'artransactions'
													 ,'action'=>'index'
													 )
											   )
							);

		$sidebarlist['new']= array(
							'tag'=>'New Transaction',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'artransactions'
													 ,'action'=>'new'
													 )
											   )
							);

		$sidebar->addList('Actions',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
	
	}

	public function delete($modelName = null)
	{
		
		$flash = Flash::Instance();
		
		$flash->addError('delete is not allowed here');
		
		sendTo($this->name,'index',$this->_modules);

	}
	
	public function view()
	{

		if (isset($this->_data['id']))
		{
			$asset = $this->_uses['ARGroup'];
			$asset->load($this->_data['id']);

			$sidebar = new SidebarController($this->view);

			$sidebarlist=array();

			$sidebarlist['showall']= array(
								'tag'=>'View All Assets',
								'link'=>array_merge($this->_modules
												   ,array('controller'=>'assets'
														 ,'action'=>'index'
														 )
												   )
								);

			$sidebarlist['viewtrans']= array(
								'tag'=>'View All Transactions',
								'link'=>array_merge($this->_modules
												   ,array('controller'=>'artransactions'
														 ,'action'=>'index'
														 )
												   )
							);

			$sidebarlist['new']= array(
								'tag'=>'New Asset',
								'link'=>array_merge($this->_modules
													   ,array('controller'=>'assets'
															 ,'action'=>'new'
															 ,'argroup_id'=>$this->_data['id']
															 )
													   )
								);

			$sidebar->addList('Actions',$sidebarlist);

			$this->view->register('sidebar',$sidebar);
			$this->view->set('sidebar',$sidebar);
		
		}
		else
		{
			sendTo($this->name,'index',$this->_modules);
		}

	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName((empty($base)?'Asset_Groups':$base), $action);
	}

}

// End of ArgroupsController

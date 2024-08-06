<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ArlocationsController extends Controller
{

	protected $version='$Revision: 1.6 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('ARLocation');
		
		$this->uses($this->_templateobject);
	
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$this->view->set('clickaction', 'edit');
		
		parent::index(new ARLocationCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
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
			$asset = $this->_uses['ARLocation'];
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
		else
		{
			sendTo($this->name,'index',$this->_modules);
		}

	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName((empty($base)?'Asset_Locations':$base), $action);
	}

}

// End of ArlocationsController

<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ArtransactionsController extends Controller
{

	protected $version='$Revision: 1.6 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('ARTransaction');
		
		$this->uses($this->_templateobject);
	
	}

	public function index()
	{

// Search
		$errors=array();
	
		$s_data=array();

// Set context from calling module
				
		$this->setSearch('assetsSearch', 'transactions', $s_data);
		
		$collection = new ARTransactionCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($collection);
		
		if (isset($this->_data['id']))
		{
			$sh->addConstraint(new Constraint('id', '=', $this->_data['id']));
		}
		
		parent::index($collection, $sh);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist=array();
		
		$sidebarlist['viewassets']= array(
							'tag'=>'View All Assets',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'assets'
													 ,'action'=>'index'
													 )
											   )
							);
		
		$sidebar->addList('Actions',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
	}

	public function delete()
	{
		
		$flash = Flash::Instance();
		
		$flash->addError('delete is not allowed here');
		
		sendTo($this->name,'index',$this->_modules);
		
	}
	
	public function save()
	{
		
		$flash=Flash::Instance();
		
		if (strtolower($this->_data['saveform'])=='cancel')
		{
			$flash->addMessage('Action cancelled');
			sendTo($this->name,'index',$this->_modules);
		}
		
		if(parent::save('ARTransaction'))
		{
			if (strtolower($this->_data['saveform'])=='save')
			{
				sendTo($this->name,'index',$this->_modules);
			}
			else
			{
				sendTo($this->name,'new',$this->_modules);
			}
		}
		else
		{
			$this->refresh();
		}

	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName((empty($base)?'Asset_Transactions':$base), $action);
	}

}

// End of ArtransactionsController

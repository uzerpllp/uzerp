<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ArgroupsController extends Controller
{

	protected $version='$Revision: 1.5 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = new ARGroup();
		
		$this->uses($this->_templateobject);

	}

	public function index()
	{
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new ARGroupCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>$this->name
											 ,'action'=>'new'
											 )
									   ),
					'tag'=>'New Group'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
	}

	public function delete()
	{
		
		$flash = Flash::Instance();
		
		parent::delete('ARGroup');
		
		sendTo($this->name,'index',$this->_modules);
		
	}
	
	public function save()
	{
		
		$flash=Flash::Instance();
		
		if(parent::save('ARGroup'))
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
		return parent::getPageName((empty($base)?'Asset_Groups':$base), $action);
	}

}

// End of ArgroupsController

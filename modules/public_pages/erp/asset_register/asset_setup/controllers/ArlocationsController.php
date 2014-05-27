<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ArlocationsController extends Controller
{

	protected $version='$Revision: 1.5 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = new ARLocation();
		
		$this->uses($this->_templateobject);
	
	}

	public function index()
	{
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new ARLocationCollection($this->_templateobject));
		
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
					'tag'=>'New Location'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
	}

	public function delete()
	{
		
		$flash = Flash::Instance();
		
		parent::delete('ARLocation');
		
		sendTo($this->name,'index',$this->_modules);
		
	}
	
	public function save()
	{
		$flash=Flash::Instance();
		
		if(parent::save('ARLocation'))
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
		return parent::getPageName((empty($base)?'Asset_Locations':$base), $action);
	}

}

// End of ArlocationsController

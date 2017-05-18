<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CsfailurecodesController extends PrintController
{

	protected $version='$Revision: 1.7 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('CSFailureCode');
		
		$this->uses($this->_templateobject);
	
	}

	public function index()
	{
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new CSFailureCodeCollection($this->_templateobject));
		
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
					'tag'=>'New Failure Code'
				),
				'summary'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'customerservices'
											 ,'action'=>'index'
											 )
									   ),
					'tag'=>'Customer Service Summary'
				)
					)
		);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
	}

	public function delete()
	{
		
		$flash = Flash::Instance();
		
		parent::delete('CSFailureCode');
		
		sendTo($this->name,'index',array($this->_modules));
		
	}
	
	public function save()
	{
		
		$flash=Flash::Instance();
		
		if(parent::save('CSFailureCode'))
		{
			sendTo($this->name,'index',$this->_modules);
		 }
		 else
		 {
			$this->refresh();
		}

	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName('Customer Service Failure Codes');
	}
}

// End of CsfailurecodesController


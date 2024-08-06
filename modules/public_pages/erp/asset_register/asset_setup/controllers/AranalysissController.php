<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class AranalysissController extends Controller
{

	protected $version='$Revision: 1.5 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = new ARAnalysis();
		
		$this->uses($this->_templateobject);
	
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new ARAnalysisCollection($this->_templateobject));
		
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
					'tag'=>'New Analysis'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
	}

	public function delete($modelName = null)
	{
		
		$flash = Flash::Instance();
		
		parent::delete('ARAnalysis');
		
		sendTo($this->name,'index',$this->_modules);

	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		
		$flash=Flash::Instance();
		
		if(parent::save('ARAnalysis'))
		{
			if (strtolower((string) $this->_data['saveform'])=='save')
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
		return parent::getPageName((empty($base)?'Asset_Register_Analysis':$base), $action);
	}

}

// End of AranalysissController

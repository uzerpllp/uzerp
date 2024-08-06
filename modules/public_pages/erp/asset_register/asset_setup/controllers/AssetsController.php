<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class AssetsController extends Controller
{

	protected $version='$Revision: 1.6 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = new Asset();
		$this->uses($this->_templateobject);
	
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$this->view->set('clickaction', 'edit');
		
		parent::index(new AssetCollection($this->_templateobject));
		
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
					'tag'=>'New Asset'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	
	}

	public function delete($modelName = null)
	{
		
		$flash = Flash::Instance();
		
		$flash->addError('delete is not allowed here');
		
		sendTo($this->name,'index',$this->_modules);

	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		
		if(!$this->checkParams($this->modeltype)) {
			sendBack();
		}
		
		$flash=Flash::Instance();
		
		$errors = array();
		
		$period=new GLPeriod();
		
		if ($period->loadPeriod($this->_data[$this->modeltype]['purchase_date']))
		{
			$this->_data[$this->modeltype]['purchase_period_id']=$period->id;
		}
		else
		{
			$errors[]='No period defined for this purchase date';
		}
		
		if(count($errors)==0 && parent::save($this->modeltype))
		{
			sendTo($this->name, 'index', $this->_modules);
		}
		else
		{
			$flash->addErrors($errors);
			$this->refresh();
		}

	}

}

// End of AssetsController

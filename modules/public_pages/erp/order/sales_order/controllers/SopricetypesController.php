<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SopricetypesController extends Controller
{

	protected $version = '$Revision: 1.4 $';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('SOPriceType');
		
		$this->uses($this->_templateobject);
		
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$this->view->set('clickaction', 'edit');
		
		parent::index(new SOPriceTypeCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 ),
					'tag'=>'new_Price_Type'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null)
	{
		$flash = Flash::Instance();
		
		parent::delete($this->modeltype);
		
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? null);
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		$flash = Flash::Instance();
		
		if(parent::save($this->modeltype))
		{
			sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? null);
		}
		else
		{
			$this->refresh();
		}

	}

	protected function getPageName($base=null, $action=null)
	{
		return parent::getPageName((!empty($base))?$base:'sales_order_price_types',$action);
	}

}

// End of SopricetypesController

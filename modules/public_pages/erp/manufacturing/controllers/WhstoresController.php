<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WhstoresController extends printController
{

	protected $version = '$Revision: 1.8 $';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('WHStore');
		
		$this->uses($this->_templateobject);
	}

	public function index()
	{
		$this->view->set('clickaction', 'view');
		
		parent::index(new WHStoreCollection($this->_templateobject));
		
	}

	public function view ()
	{
		sendTo('WHLocations', 'index', $this->_modules, array('whstore_id'=>$this->_data['id']));
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'stores':$base), $action);
	}
	
	public function getBinLocationList($_id = '')
	{
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id = $this->_data['id']; }
		}
		
		if (!empty($_id))
		{
			$locations = $this->_templateobject->getBinLocationList($_id);
		}
		
		if(isset($this->_data['id']))
		{
			$this->view->set('options',$locations);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $locations;
		}
	}

}

// End of WhstoresController

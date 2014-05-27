<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PartysController extends Controller
{

	protected $version = '$Revision: 1.5 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('Party');
		
		$this->uses($this->_templateobject);
		
//		$this->related['company']=array('clickaction'=>'edit');

	}

	public function index()
	{
		global $smarty;
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new PartyCollection($this->_templateobject));
	}

	public function delete()
	{
		$flash = Flash::Instance();
		
		parent::delete('Party');
		
		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	}
	
	public function save()
	{
		$flash=Flash::Instance();
		if(parent::save('Party'))
		{
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		else
		{
			$this->refresh();
		}

	}
}

// End of PartysController

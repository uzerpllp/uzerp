<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CurrencysController extends LedgerController
{

	protected $version='$Revision: 1.8 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = new Currency();
		$this->uses($this->_templateobject);	

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$this->view->set('clickaction', 'edit');
		parent::index(new CurrencyCollection($this->_templateobject));
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
					'tag'=>'new_currency'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new()
	{
		
		$flash=Flash::Instance();
		
		$errors=array();
		
		$glaccounts=new GLAccount();
		
		if ($glaccounts->getCount()==0)
		{
			$errors[]='No GL Accounts defined';
		}
		
		$glcentres=new GLCentre();
		
		if ($glcentres->getCount()==0)
		{
			$errors[]='No GL Centres defined';
		}
		
		if (count($errors)>0)
		{
			$flash->addErrors($errors);
			sendback();
		}

		parent::_new();
		
		$currency = $this->_uses[$this->modeltype];
		
		$glaccounts=$glaccounts->getAll();
	
		if (isset($_POST[$this->modeltype]['writeoff_glaccount_id']))
		{
			$default_glaccount_id=$_POST[$this->modeltype]['writeoff_glaccount_id'];
		}
		elseif (isset($this->_data['writeoff_glaccount_id']))
		{
			$default_glaccount_id=$this->_data['writeoff_glaccount_id'];
		}
		elseif ($currency->isLoaded())
		{
			$default_glaccount_id=$currency->writeoff_glaccount_id;
		}
		else
		{
			$default_glaccount_id=key($glaccounts);
		}
		
		$this->view->set('glcentres',$this->getCentres($default_glaccount_id));
		
	}
	
}

// End of CurrencysController

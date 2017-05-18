<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CurrencyratesController extends LedgerController {

	protected $version='$Revision: 1.7 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new CurrencyRate();
		$this->uses($this->_templateobject);

	}

	public function index(){
		$this->view->set('clickaction', 'edit');
		parent::index(new CurrencyRateCollection($this->_templateobject));
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
					'tag'=>'new_currency_rate'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new() {
		
		$flash=Flash::Instance();
		$errors=array();
		$currency=new Currency();
		
		if ($currency->getCount()==0) {
			$errors[]='No Currencies defined';
		}
		
		if (count($errors)>0) {
			$flash->addErrors($errors);
			sendback();
		}
		
		parent::_new();

	}
	
}
?>

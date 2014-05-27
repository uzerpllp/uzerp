<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GlbalancesController extends LedgerController {

	protected $version='$Revision: 1.7 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new GLBalance();
		$this->uses($this->_templateobject);

	}

	public function index(){
		$this->view->set('clickaction', 'edit');
		parent::index(new GLBalanceCollection($this->_templateobject));
	}

}
?>

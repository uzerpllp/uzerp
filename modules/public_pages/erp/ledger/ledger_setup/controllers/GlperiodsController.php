<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GlperiodsController extends LedgerController {

	protected $version='$Revision: 1.8 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new GLPeriod();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'edit');
		parent::index(new GLPeriodCollection($this->_templateobject));
		
		$period=new GLPeriod();
		$period->getCurrentPeriod();
		$newperiod=new GLPeriod();
		if ($period->isLoaded()) {
			$nextyear=date(DATE_FORMAT, strtotime('+12 months', strtotime($period->enddate)));
			$newperiod->loadPeriod($nextyear);
		}
		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['new'] = array(
							'tag'=>'new_glperiod',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'new'
													 )
											   )
							);
		$sidebarlist['close'] = array(
							'tag'=>'Close Period '.$period->year.' - Period '.$period->period,
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'close'
													 ,'id'=>$period->id
													 )
											   )
							);
		if (!$newperiod->isLoaded() && $period->isLoaded()) {
			$sidebarlist['viewtrans']= array(
							'tag'=>'Create Future Periods',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'createperiods'
													 )
											   )
							);
		}
		$sidebar->addList('Actions',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function close(){
		$flash = Flash::Instance();
		$db=DB::Instance();
		$db->StartTrans();
		if (isset($this->_data['id'])) {
			$period=periodHandling::close($this->_data['id'], $errors);
		} else {
			$errors[]='No period has been selected';
		}
		
		if (count($errors)===0 && $db->CompleteTrans()) {
			$flash->addMessage('Year '.$period->year.' - Period '.$period->period.' has been closed');
		} else {
			$flash->addErrors($errors);
			$flash->addError('Failed to close period');
			$db->FailTrans();
		}
		
		sendTo($this->name, 'index', $this->_modules);
	}
	
	public function createperiods () {
		$flash=Flash::Instance();
		$errors=array();
		
		periodHandling::createPeriods($errors);
		
		if (count($errors)>0) {
			$flash->addErrors($errors);
		}
		sendTo($this->name, 'index', $this->_modules);
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'GL Periods':$base), $action);
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		$vat_return = new VatReturn();
		$period_data = $this->_data['GLPeriod'];

		try
		{
			$vat_return->newVatReturn($period_data['year'], $period_data['tax_period']);
		}
		catch (VatReturnStorageException $e)
		{
			$flash->addError($e->getMessage());
			sendTo($this->name, 'index', $this->_modules);
		}
		parent::save();
	}

}
?>

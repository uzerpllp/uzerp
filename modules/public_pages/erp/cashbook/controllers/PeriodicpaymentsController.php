<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PeriodicpaymentsController extends printController {

	protected $version='$Revision: 1.11 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new PeriodicPayment();
		$this->uses($this->_templateobject);

	}

	public function index(){

		$errors=array();

		
		// set context from calling module

		$defaults=array();
		
		if (isset($this->_data['cbaccount_id'])) {
			$defaults['cbaccount_id']=$this->_data['cbaccount_id'];
		}
		if (isset($this->_data['company_id'])) {
			$defaults['company_id']=$this->_data['company_id'];
		}
		if (isset($this->_data['source'])) {
			$defaults['source']=$this->_data['source'];
		}
		if (isset($this->_data['status'])) {
			$defaults['status']=$this->_data['status'];
		}
		if (isset($this->_data['frequency'])) {
			$defaults['frequency']=$this->_data['frequency'];
		}

		$this->setSearch('PeriodicPaymentsSearch', 'useDefault', $defaults);
		
		$this->view->set('clickaction', 'view');
		parent::index(new PeriodicPaymentCollection($this->_templateobject));		
		
		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['makepayment']= array(
							'tag'=>'Make Payments',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'makepayments'
													 )
											   )
							);
		$sidebar->addList('Actions',$sidebarlist);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function makePayments () {
	$flash=Flash::Instance();
		$errors=array();
		// set context from calling module

		$defaults=array();
		
		if (isset($this->_data['cb_account_id'])) {
			$defaults['cb_account_id']=$this->_data['cb_account_id'];
		}
		if (isset($this->_data['company_id'])) {
			$defaults['company_id']=$this->_data['company_id'];
		}
		if (isset($this->_data['source'])) {
			$defaults['source']=$this->_data['source'];
		}
		if (isset($this->_data['status'])) {
			$defaults['status']=$this->_data['status'];
		}
		if (isset($this->_data['frequency'])) {
			$defaults['frequency']=$this->_data['frequency'];
		}
		if (isset($this->_data['from_date'])) {
			$defaults['next_due_date']['from']=un_fix_date($this->_data['from_date']);
			$defaults['next_due_date']['to']=un_fix_date($this->_data['from_date']);
		}
		if (isset($this->_data['to_date'])) {
			$defaults['next_due_date']['to']=un_fix_date($this->_data['to_date']);
			if (!isset($this->_data['from_date'])) {
				$defaults['next_due_date']['from']=un_fix_date($this->_data['to_date']);
			}
		}
		
		$this->setSearch('PeriodicPaymentsSearch', 'makePayments', $defaults);
		
		parent::index(new PeriodicPaymentCollection($this->_templateobject));		

		$this->view->set('clickaction', 'view');
		
	}
	
	public function savePayments()
	{
		$flash=Flash::Instance();
		
		$errors=array();
		$data=$this->_data['PeriodicPayment'];

		$db=DB::Instance();
		$db->StartTrans();
		
		foreach ($data as $id=>$set) {
			if (isset($set['pay']) || isset($set['skip']))
			{
				$pp=new PeriodicPayment();
				$pp->load($id);
				
				if (isset($set['pay']))
				{
					//	Create payment record array
					$data=$pp->makePaymentTransaction();
					
					$data['description']		= $set['description'];
					$data['ext_reference']		= $set['ext_reference'];
					
					if (isset($set['next_due_date']))
					{
						$data['transaction_date'] = $set['next_due_date'];
					}
					
					if (isset($set['net_value'])) {
						$data['net_value']	= $set['net_value'];
						$data['tax_value']	= $set['tax_value'];
						$data['value']		= bcadd($data['net_value'], $data['tax_value']);
					}
					
					if (isset($set['gross_value'])) {
						$data['tax_value'] = 0.00;
						$data['net_value'] = $data['value'] = $set['gross_value'];
					}
					
					if ($pp->source=='CR' || $pp->source=='CP')
					{
						if ($pp->source=='CP')
						{
							$data['net_value']	= bcmul($data['net_value'], -1);
							$data['tax_value']	= bcmul($data['tax_value'], -1);
							$data['value']		= bcmul($data['value'], -1);
						}
						
						CBTransaction::saveCashPayment($data, $errors);
					}
					else
					{
						if ($pp->source=='PP')
						{
							$payment=new PLTransaction();
						}
						else
						{
							$payment=new SLTransaction();
						}
						
						$payment->saveTransaction($data, $errors);
					}
					
					//	Update Periodic Payment record
					$pp->current++;
					if (!is_null($pp->end_date) && $pp->next_due_date>$pp->end_date)
					{
						$pp->status='S';
					}
					
					if (!is_null($pp->occurs) && $pp->current>=$pp->occurs)
					{
						$pp->status='S';
					}
					
					if ($pp->variable=='t' && $pp->write_variance=='t')
					{
						$pp->net_value		= $data['net_value'];
						$pp->tax_value		= $data['tax_value'];
						$pp->gross_value	= $data['value'];
					}
					
					if ($pp->status=='S')
					{
						$pp->glaccount_centre_id = null;
					}
					else
					{
						$pp->glaccount_centre_id = GLAccountCentre::getAccountCentreId($pp->glaccount_id, $pp->glcentre_id, $errors);
					}
				
				}
				// Update the date if skipping or paying
				$pp->nextDate();
				
				if (count($errors)>0 || !$pp->save())
				{
					$errors[]='Failed to update Periodic Payment details';
					break;
				}
			}
		}
		
		if(count($errors)==0 && $db->completeTrans())
		{
			$flash->addMessage('Payments processed OK');
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		else
		{
			$db->FailTrans();
			$db->completeTrans();
			$flash->addErrors($errors);
			$flash->addError('Failed to make payments');
			$this->refresh();
		}

	}

	public function view () {
		if (isset($this->_data['id'])) {
			$pp=new PeriodicPayment();
			$pp->load($this->_data['id']);
			$this->view->set('periodicpayment', $pp);

			$sidebar = new SidebarController($this->view);
			$sidebarlist=array();
			$sidebarlist['showall']= array(
							'tag'=>'View All Payments',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'index'
													 )
											   )
							);
			$sidebar->addList('Actions',$sidebarlist);
			$this->view->register('sidebar',$sidebar);
			$this->view->set('sidebar',$sidebar);
		} else {
			sendTo($this->name,'index', $this->_modules);
		}
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'periodic_payments':$base), $action);
	}

}

?>
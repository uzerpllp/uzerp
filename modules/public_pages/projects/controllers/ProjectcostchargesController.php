<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectcostchargesController extends Controller {

	protected $version='$Revision: 1.4 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		
		$this->_templateobject = new ProjectCostCharge();
		$this->uses($this->_templateobject);
		
	}
	
	public function index($collection = null, $sh = '', &$c_query = null) {
		$this->view->set('clickaction', 'view');
		
		parent::index(new ProjectCostChargeCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'New Project Budget Cost'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function _new() {
		parent::_new();
		
		$costcharge=$this->_uses[$this->modeltype];
		
		if (!$costcharge->isLoaded())
		{
			$costcharge->item_type=key($this->_templateobject->getEnumOptions('item_type'));
			$costcharge->source_type=key($this->_templateobject->getEnumOptions('source_type'));
		}
		
		if (isset($this->_data['project_id']))
		{
			$costcharge->project_id = $this->_data['project_id'];
		}

		if (isset($this->_data['task_id']))
		{
			$costcharge->task_id = $this->_data['task_id'];
			$task = new Task();
			$task->load($this->_data['task_id']);
			$costcharge->project_id = $task->project_id;
		}
		
		if (!is_null($costcharge->project_id) && is_null($costcharge->task_id))
		{
			$this->view->set('tasks', $this->getTaskList($costcharge->project_id));
		}
		
//		switch ($costcharge->item_type) {
//			case 'PO':
//				$account=new PLSupplier();
//				$this->view->set('account_type', 'Supplier');
//				break;
//			case 'SI':
//				$account=new SLCustomer();
//				$this->view->set('account_type', 'Customer');
//				break;
//		}
		
//		$this->view->set('accounts', $account->getAll());
	
		$sidebarlist['equipment_cost_charge']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new_cost_charge'
								 ,'project_id'=>$costcharge->project_id
								 ,'task_id'=>$costcharge->task_id
								 ,'source_type'=>'E'),
					'tag'=>'Equipment Usage'
					);
					
		$sidebarlist['resource_cost_charge']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new_cost_charge'
								 ,'project_id'=>$costcharge->project_id
								 ,'task_id'=>$costcharge->task_id
								 ,'source_type'=>'R'),
					'tag'=>'Timesheets'
					);
					
		$sidebarlist['expenses_cost_charge']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new_cost_charge'
								 ,'project_id'=>$costcharge->project_id
								 ,'task_id'=>$costcharge->task_id
								 ,'source_type'=>'X'),
					'tag'=>'Expenses'
					);
					
		$sidebarlist['material_cost_charge']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new_cost_charge'
								 ,'project_id'=>$costcharge->project_id
								 ,'task_id'=>$costcharge->task_id
								 ,'source_type'=>'M'),
					'tag'=>'Purchase Order'
					);
					
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Create Charge From',
			$sidebarlist
		);

		$sidebarlist=array();
		$sidebarlist['budget_cost_charge']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new_cost_charge'
								 ,'project_id'=>$costcharge->project_id
								 ,'task_id'=>$costcharge->task_id
								 ,'source_type'=>'B'),
					'tag'=>'Budget Cost'
					);
					
		$sidebar->addList(
			'Create Cost From',
			$sidebarlist
		);
					
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	
	}
	
	public function new_cost_charge() {
		
		$flash=Flash::Instance();
		
		if (!$this->checkParams('source_type'))
		{
			$this->dataError();
			$this->sendback();
		}
		
		if (empty($this->_data['project_id']) && empty($this->_data['task_id']))
		{
			$flash->addError('No project or task suipplied');
			$this->sendback();
		}
		
		$cc=new ConstraintChain();
		if (!empty($this->_data['project_id']))
		{
			$cc->add(new Constraint('project_id', '=', $this->_data['project_id']));
			$type='Project';
		}
		if (!empty($this->_data['task_id']))
		{
			$cc->add(new Constraint('task_id', '=', $this->_data['task_id']));
			$type='Task';
		}
		
		$this->view->set('source_type', $this->_data['source_type']);
		switch ($this->_data['source_type']) {
			case 'B':
				$title='Materials Costs';
				$quantity='quantity';
				$unit_price='unit_price';
				$description='description';
				$equipment=new ProjectBudget();
				$unassignedlist=new ProjectBudgetCollection($equipment);
				$cc->add(new Constraint('budget_item_type', '=', 'M'));
				$fields=array('id', 'description', 'quantity', 'uom_name', 'charge_rate as unit_price');
				break;
			case 'E':
				$title='Equipment Charges';
				$quantity='quantity';
				$unit_price='charge_rate';
				$description='equipment';
				$equipment=new ProjectEquipmentAllocation();
				$unassignedlist=new ProjectEquipmentAllocationCollection($equipment, 'project_equipment_charges');
				$fields=array('id', 'equipment', 'start_date', 'end_date', 'setup_charge', 'quantity', 'uom_name', 'charge_rate', 'total_charges');
				break;
			case 'M':
				$title='Materials Charges';
				$quantity='quantity';
				$unit_price='unit_price';
				$description='description';
				$equipment=new ProjectCostCharge();
				$unassignedlist=new ProjectCostChargeCollection($equipment);
				$cc->add(new Constraint('source_type', '=', 'PO'));
				$fields=array('id', 'description', 'quantity', 'unit_price', 'net_value');
				break;
			case 'R':
				$title='Hours Charges';
				$quantity='duration';
				$unit_price='resource_rate';
				$description='person';
				$equipment=new Resource();
				$unassignedlist=new ResourceCollection($equipment, 'project_hours_overview');
				$fields=array('id', 'person', 'start_time', 'duration', 'resource_rate');
				break;
			case 'X':
				$title='Expenses Charges';
				$quantity='qty';
				$unit_price='unit_price';
				$description='item_description';
				$equipment=new ExpenseLine;
				$unassignedlist=new ExpenseLineCollection($equipment);
				$fields=array('id', 'person', 'expense_date', 'item_description', 'qty', 'purchase_price as unit_price', 'net_value');
				break;
		}

		$sh=$this->setSearchHandler($unassignedlist);
		$sh->setFields($fields);
		$sh->addConstraint($cc);

		$this->view->set('collection', $unassignedlist);

		parent::index($unassignedlist, $sh);

		$this->view->set('quantity', $quantity);
		$this->view->set('unit_price', $unit_price);
		$this->view->set('description', $description);
		$this->view->set('page_title', $this->getPageName($type.' Actuals', 'New '.$title));
	}

	public function new_purchase_order () {
		
	}
	
	public function new_sales_invoice () {
		
	}
	
	public function link_purchase_orders() {
		
		// Search
		$errors=array();
		$s_data=array();

		// Set context from calling module
		if (isset($this->_data['project_id'])) { $s_data['project_id']=$this->_data['project_id']; }
		if (isset($this->_data['Search']['project_id'])) { $this->_data['project_id']=$this->_data['Search']['project_id']; }
		
		$this->setSearch('ProjectcostchargeSearch', 'purchaseOrders', $s_data);
		// End of search
		
		$unassigned_list=new POrderLineCollection(new POrderLine());
		$sh=$this->setSearchHandler($unassigned_list);
		$sh->setFields(array('id', 'order_number', 'order_date', 'supplier', 'description', 'net_value', 'due_date'));
		
		$subquery="select item_id from project_costs_charges where item_type='PO'";
		$sh->addConstraint(new Constraint('id', 'not in', '('.$subquery.')'));
		$sh->setOrderby('order_number');
		
//		$this->view->set('clickaction', 'view');
		parent::index($unassigned_list, $sh);
		$this->view->set('unassigned_list', $unassigned_list);
		
		$this->setTemplateName('link_costs_charges');
		
	}
	
	public function link_sales_invoices() {
		
		// Search
		$errors=array();
		$s_data=array();

		// Set context from calling module
		if (isset($this->_data['project_id'])) { $s_data['project_id']=$this->_data['project_id']; }
		if (isset($this->_data['Search']['project_id'])) { $this->_data['project_id']=$this->_data['Search']['project_id']; }
		
		$project=new Project();
		$project->load($this->_data['project_id']);
		
		$customer=new SLCustomer();
		$customer->loadBy('company_id', $project->company_id);
		
		$s_data['slmaster_id']=$customer->id;
		
		$this->setSearch('ProjectcostchargeSearch', 'salesInvoices', $s_data);
		// End of search
		
		$unassigned_list=new SInvoiceLineCollection(new SInvoiceLine());
		$sh=$this->setSearchHandler($unassigned_list);
		$sh->setFields(array('id', 'invoice_number', 'invoice_date', 'customer', 'description', 'net_value', 'tax_value', 'gross_value'));
		
		$db=DB::Instance();
		$subquery="select item_id from project_costs_charges where item_type='SI'";
		$sh->addConstraint(new Constraint('id', 'not in', '('.$subquery.')'));
		$sh->setOrderby('invoice_number');

		parent::index($unassigned_list, $sh);
		$this->view->set('unassigned_list', $unassigned_list);
		
		$this->setTemplateName('link_costs_charges');
		
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		if(parent::save($this->modeltype))
		{
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,$_SESSION['refererPage']['other'] ?? null);
		}
		else
		{
			$this->refresh();
		}
	}

	
/* Ajax functions */
	public function getAccountList($_item_type='', $_account='') {
		
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['item_type'])) { $_item_type=$this->_data['item_type']; }
		}
		
		switch ($_item_type) {
			case 'PO':
				$account=new PLSupplier();
				break;
			case 'SI':
				$account=new SLCustomer();
				break;
		}
		
		$accounts=$account->getAll();
		
		if(isset($this->_data['ajax'])) {
			$output['account']=array('data'=>$accounts, 'is_array'=>true);
			$this->view->set('data', $output);
			$this->setTemplateName('ajax_multiple');
		} else {
			return $accounts;
		}
		
	}
	
	public function getOrderList($_item_type='', $_account='') {
		
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['item_type'])) { $_item_type=$this->_data['item_type']; }
			if(!empty($this->_data['account'])) { $_account=$this->_data['account']; }
		}
		
		switch ($_item_type) {
			case 'PO':
				$order=new POrder();
				$field='plmaster_id';
				break;
			case 'SI':
				$order=new SInvoice();
				$field='slmaster_id';
				break;
		}
		
		$cc=new ConstraintChain();
		$cc->add(new Constraint($field, '=', $_account));
		$db=DB::Instance();
		$subquery='select item_id from project_costs_charges where item_type='.$db->qstr($_item_type);
		$cc->add(new Constraint('id', 'not in', '('.$subquery.')'));
		
		$orders=$order->getAll($cc);
		
		if(isset($this->_data['ajax'])) {
			$output['item_id']=array('data'=>$orders, 'is_array'=>true);
			$this->view->set('data', $output);
			$this->setTemplateName('ajax_multiple');
		} else {
			return $orders;
		}
		
	}
	
	public function getTaskList($_project_id='') {
		
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['project_id'])) { $_project_id=$this->_data['project_id']; }
		}
		
		if (!empty($_project_id)) {
			$depends=array('project_id'=>$_project_id);
		} else {
			$depends=array();
		}
		
		$tasks=$this->getOptions($this->_templateobject, 'task_id', '', '', array(), $depends);
		
		if(isset($this->_data['ajax'])) {
			echo $tasks;
			exit;
		} else {
			return $tasks;
		}
		
	}
	
	
/* protected functions */
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName(empty($base)?'project_costs_charges':$base, $action);
	}
	
}
?>
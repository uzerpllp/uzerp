<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectsController extends Controller {

	protected $version='$Revision: 1.15 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		
		$this->uses(new Resource());
		$this->uses(new Task());
		$this->related['tasks']=array('clickaction'=>'viewtask');
		$this->_templateobject = new Project();
		$this->uses($this->_templateobject);

	}

	public function index(){
		$this->view->set('clickaction', 'view');
		$s_data = null;
		$errors=array();
		if(isset($this->_data['Search'])) {
			$s_data = $this->_data['Search'];
		}
		$this->search = ProjectSearch::useDefault($s_data,$errors);
		if(count($errors)>0) {
			$flash = Flash::Instance();
			$flash->addErrors($errors);
			$this->search->clear();
		}
		parent::index(new ProjectCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'new'),
					'tag'=>'new_project'
				),
				'timesheet'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'timesheets'),
					'tag'=>'project_hours_summary'
				)
			)
		);
		$this->view->set('no_delete',true);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function work_type_analysis() {
		$graph = new EGSPie(Project::getTotalsByWorkType(),'work_type_analysis'.EGS_COMPANY_ID);
		$graph->setTitle('Analysis of Work-Type');
		$graph->render();
		$this->setTemplateName('image_view');
		$this->view->set('img_src',$graph->getFilename());
	}
	
	public function category_analysis() {
		$graph = new EGSPie(Project::getTotalsByCategory(),'category'.EGS_COMPANY_ID);
		$graph->setTitle('Project Category');
		$graph->render();
		$this->setTemplateName('image_view');
		$this->view->set('img_src',$graph->getFilename());
	}
	
	public function geographical_analysis() {
		$graph = new EGSPie(Project::getTotalsByCountry(),'country'.EGS_COMPANY_ID);
		$graph->setTitle('Geographical Analysis');
		$graph->render();
		$this->setTemplateName('image_view');
		$this->view->set('img_src',$graph->getFilename());
	}
	public function equipment_time_analysis() {
		$graph = new EGSPie(Project::getTotalhoursByEquipment(),'equipment_time'.EGS_COMPANY_ID);
		$graph->setTitle('Equipment Analysis');
		$graph->render();
		$this->setTemplateName('image_view');
		$this->view->set('img_src',$graph->getFilename());
	}
	public function equipment_cost_analysis() {
		$graph = new EGSPie(Project::getTotalcostByEquipment(),'equipment_cost'.EGS_COMPANY_ID);
		$graph->setTitle('Equipment Cost Analysis');
		$graph->render();
		$this->setTemplateName('image_view');
		$this->view->set('img_src',$graph->getFilename());
	}
	public function hour_type_analysis() {
		$graph = new EGSPie(Project::getTotalHoursByHourType(),'labour_time'.EGS_COMPANY_ID);
		$graph->setTitle('Labour Type Analysis');
		$graph->render();
		$this->setTemplateName('image_view');
		$this->view->set('img_src',$graph->getFilename());
	}
	
	public function equipment_usage_analysis() {
		$graph = new EGSPie(Project::getEquipmentUsageByWorktype(),null,900,600);
		$graph->setTitle('Equipment Usage Analysis');
		$graph->render();
		$this->setTemplateName('image_view');
		$this->view->set('img_src',$graph->getFilename());
	}

	public function profit_loss_analysis() {
		$graph = new EGSBar(Project::getProfitLossInformation(),'profit_loss_analysis'.EGS_COMPANY_ID);
		$graph->setTitle('Profit / Loss Information');
		$graph->render();
		$this->setTemplateName('image_view');
		$this->view->set('img_src',$graph->getFilename());
	}
	
	public function resourcesindex(){
		$this->view->set('clickaction', 'editresource');
		parent::index(new ResourceCollection($this->_templateobject));
		$this->view->set('no_ordering',true);
	}
	
	public function tasksindex(){
		$this->view->set('clickaction', 'viewtask');
		if(!empty($this->_data['project_id'])) {
			$project=new Project();
			$project->load($this->_data['project_id']);
			$tasks=$project->tasks;
			$this->view->set('no_ordering',true);
		}
		else {
			$tasks=new TaskCollection($this->_templateobject);
		}
		parent::index($tasks);		
	}
	
	public function complete() {
		if (isset($this->_data['id'])) {
			$project=$this->_uses['Project'];
			$project->load($this->_data['id']);
			$project->update($this->_data['id'],'status','C');
			foreach ($project->tasks as $task) {
				$task->complete();
			}
			sendBack();
		}
	}
	
	public function view() {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$project=$this->_uses[$this->modeltype];
		
		$db				= &DB::Instance();
		$query			= 'select id from projectsoverview where id ='.$db->qstr($project->id).' AND usernameaccess='.$db->qstr(EGS_USERNAME);
		$has_permission	= $db->GetCol($query);
		
		if ($has_permission === false && !isModuleAdmin() && $project->owner != EGS_USERNAME)
		{
			$flash = Flash::Instance();
			$flash->addError('You do not have permission to view that project');
			sendTo($this->name,'index',$this->_modules);
		}
		
		$viewing_sidebar = array(
			$project->name => array(
				'tag'	=> $project->name,
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'view','id'=>$project->id)
			),
			'edit' => array(
				'tag'	=> 'Edit',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'edit','id'=>$project->id)
			),
			'delete' => array(
				'tag'	=> 'Delete',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'delete','id'=>$project->id)
			),
			'mark_as_complete' => array(
				'tag'	=> 'Mark as Complete',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'complete','id'=>$project->id)
			),
	        	'time-shift' => array(
				'tag'	=> 'time-shift',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'timeshift','id'=>$project->id)
			)
			);
		
		
		$sidebar=new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'view'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'index'),
					'tag'=>'view_projects'
				),
				'new'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'new'),
					'tag'=>'new_project'
				)			)
		);



		$sidebar->addList(
			'currently_viewing',
			$viewing_sidebar
		);

		$this->sidebarRelatedItems($sidebar, $project);

		$sidebarlist['link_purchase_orders']= array('tag'=>'link_purchase_orders'
												  ,'link'=> array('modules'=>$this->_modules
												  				 ,'controller'=>'projectcostcharges'
												  				 ,'action'=>'link_purchase_orders'
												  				 ,'project_id'=>$project->id)
												  ,'new'=> array('modules'=>$this->_modules
												  				 ,'controller'=>'projectcostcharges'
												  				 ,'action'=>'new_purchase_order'
												  				 ,'project_id'=>$project->id)
												  );
		$sidebarlist['link_sales_invoices']= array('tag'=>'link_sales_invoices'
												  ,'link'=> array('modules'=>$this->_modules
												  				 ,'controller'=>'projectcostcharges'
												  				 ,'action'=>'link_sales_invoices'
												  				 ,'project_id'=>$project->id)
												  ,'new'=> array('modules'=>$this->_modules
												  				 ,'controller'=>'projectcostcharges'
												  				 ,'action'=>'new_sales_invoice'
												  				 ,'project_id'=>$project->id)
												  );
		$sidebarlist['view_project_totals']= array('tag'=>'view_project_totals'
												  ,'link'=> array('modules'=>$this->_modules
												  				 ,'controller'=>$this->name
												  				 ,'action'=>'viewproject_totals'
												  				 ,'id'=>$project->id)
												  );
		$sidebar->addList('related_items',$sidebarlist);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
		if ($project instanceof Project)
		{
			$pl = new PreferencePageList('recently_viewed_projects');
			$pl->addPage(
				new Page(
					array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'view',
						'id'			=> $project->id
					),
					'task',
					$project->name
				)
			);
			$pl->save();
		}
		
		
	}
	
	public function viewproject_totals () {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
				
		$project=$this->_uses[$this->modeltype];
		
		$projectbudgets=new ProjectBudget();
		$budget_totals=$projectbudgets->getTotals($project->id);
		
		$budget_totals['Other']=array_merge_recursive($budget_totals['Other']
													, $project->getExpensesTotals());
		
		$projectcostscharges=new ProjectCostCharge();
		$budget_totals['Materials']=array_merge_recursive($budget_totals['Materials']
														, $projectcostscharges->getTotals($project->id));
		
		$projectequipment=new ProjectEquipmentAllocation();
		$budget_totals['Equipment']=array_merge_recursive($budget_totals['Equipment']
														, $projectequipment->getChargeTotals($project->id));
	
		$budget_totals['Resources']=array_merge_recursive($budget_totals['Resources']
														, $project->getHourTotals());

		$this->view->set('budget_totals', $budget_totals);
	
	}
	
	public function viewpurchase_orders () {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$project=$this->_uses[$this->modeltype];
		
		$porders=new ProjectCostChargeCollection(new ProjectCostCharge('project_purchase_orders', 'PO'));
		$sh=$this->setSearchHandler($porders);
		$sh->addConstraint(new Constraint('project_id', '=', $project->id));
		$sh->setFields(array('id', 'order_id', 'order_number', 'line_number', 'order_date', 'supplier', 'description', 'net_value', 'due_delivery_date'));
		parent::index($porders, $sh);
		
		$this->setTemplateName('view_project_costs_charges');
	
		$this->view->set('page_title', $this->getPageName('Project', 'View Purchase Orders for'));
		
	}
	
	public function viewsales_invoices () {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$project=$this->_uses[$this->modeltype];
		
		$sinvoices=new ProjectCostChargeCollection(new ProjectCostCharge('project_sales_invoices', 'SI'));
		$sh=$this->setSearchHandler($sinvoices);
		$sh->addConstraint(new Constraint('project_id', '=', $project->id));
		$sh->setFields(array('id', 'invoice_id', 'invoice_number', 'line_number', 'invoice_date', 'customer', 'description', 'net_value', 'tax_value', 'gross_value'));
		parent::index($sinvoices, $sh);
		
		$this->setTemplateName('view_project_costs_charges');
		
		$this->view->set('page_title', $this->getPageName('Project', 'View Sales Invoices for'));
		
	}
	
	public function timeshift() {
		$flash = Flash::Instance();
		
		if (!isModuleAdmin() && !in_array(EGS_USERNAME, Project::getProjectManagers($this->_data['id']))) {
			$flash->addError('You must be a project manager or module admin to time-shift projects.');
			sendBack();
		}
		
		if(isset($this->_data['shift'])) {			
			if (!is_numeric($this->_data['weeks'])) {
				$flash->addError('Must be numeric');
			} else if (floor($this->_data['weeks']) != $this->_data['weeks']) {
				$flash->addError('Must be whole number of weeks');
			}
			
			if ($flash->hasErrors()) {
				$this->view->set('weeks', $this->_data['weeks']);
			} else {
				$db = DB::Instance();
				
				$db->StartTrans();
				
				// Shift project
				$db->Execute("
					UPDATE
						projects
					SET
						start_date = start_date + '" . $this->_data['weeks'] . " weeks'::interval,
						end_date = end_date + '" . $this->_data['weeks'] . " weeks'::interval
					WHERE
						id = " . $db->qstr($this->_data['id']) . "
				");
				
				// Shift tasks
				$db->Execute("
					UPDATE
						tasks
					SET
						start_date = start_date + '" . $this->_data['weeks'] . " weeks'::interval,
						end_date = end_date + '" . $this->_data['weeks'] . " weeks'::interval
					WHERE
						project_id = " . $db->qstr($this->_data['id']) . "
				");
				
				$db->CompleteTrans();
				
				$flash->addMessage('Project shifted by ' . $this->_data['weeks'] . ' weeks.');
				sendBack();
			}
		}
	}
	
	public function timesheets() {

		$errors=array();
		$s_data=array();
		
		// Set context from calling module
		$this->setSearch('hoursSearch', 'useDefault', $s_data);
		
		$hours=new HourCollection(new Hour());
		$sh=$this->setSearchHandler($hours);
		
		$fields=array('name', 'project', 'project_id');
		
		if (!isset($this->_data['page']) && !isset($this->_data['orderby']))
		{
			$sh->setOrderBy($fields);
		}
		
		$sh->setGroupBy($fields);
		$fields=array(
			"person||'-'||project_id as id",
			'person as name',
			'project',
			'project_id',
			'sum(duration) as total_hours'
		);
		$sh->setFields($fields);
		$sh->addConstraint(new Constraint('project_id', 'is not', 'NULL'));
		
		parent::index($hours, $sh);
		
		$this->view->set('fields', array('name', 'project', 'total_hours'));
		$this->view->set('page_title', 'Project Hours Summary');
		$this->view->set('clickcontroller', 'hours');
		$this->view->set('clickaction', 'index');
		$this->view->set('linkfield', 'owner');
		$this->view->set('linkvaluefield', 'name');
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'view'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'index'),
					'tag'=>'view_projects'
				),
				'new'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'new'),
					'tag'=>'new_project'
				)			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	
	}

	
	public function _new() {
		parent::_new();

		$project = $this->_uses[$this->modeltype];
		
		$companies=$this->getOptions($this->_templateobject, 'company_id', 'getOptions', 'getOptions');
		if (isset($this->_data['company_id'])) {
			// this is set if there has been error and we are redisplaying the screen
			$default_company=$this->_data['company_id'];
		} else {
			if (!$project->isLoaded()) {
				$default_company=$this->getDefaultValue($this->modeltype, 'company_id', '');
			} else {
				$default_company=$project->company_id;
			}
		}
		if (empty($default_company)) {
			$default_company=key($companies);
		}
		if (!$project->isLoaded()) {
			$project->company_id=$default_company;
		}
		$people=$this->getOptions($this->_templateobject, 'person_id', 'getOptions', 'getOptions', null, array('company_id'=>$default_company));
		
		if ($project->isLoaded()) {
			// Not sure what this does - doesn't seem to be used anymore
			// Needs investigating
			$work_type = new Projectworktype();
			$work_type->load($this->_uses['Project']->work_type_id);
			$ancestors=$work_type->getAncestors();
			$this->view->set('other_values',array_reverse($ancestors));
		}
			
	}
	
	public function delete(){
		$flash = Flash::Instance();
		parent::delete('Project');
		sendTo('Projects','index',array('projects'));
	}
	
	public function save() {
		$flash=Flash::Instance();
		$errors=array();
		if(parent::save('Project', '', $errors)) {
			$idField=$this->saved_model->idField;
			sendTo('projects','view',array('projects'),array($idField=>$this->saved_model->{$idField}));
		} else {
			$flash->addErrors($errors);
			$this->refresh();
		}

	}
	
	public function getProjectWorkTypes() {
		$work_type=new Projectworktype();
		$work_type->load($this->_data['parent_id']);
		$children=$work_type->getChildren();
		
		$this->view->set('echo',json_encode($children));
	}
	
	
	public function viewequipment_allocations() {
		
		$equipment=new ProjectEquipmentAllocation();
		$equipmentallocation=new ProjectEquipmentAllocationCollection($equipment);
		$sh=$this->setSearchHandler($equipmentallocation);
		$sh->setFields(array('id'
							,'equipment'
							,'task'
							,'start_date'
							,'end_date'
							,'charging_period_uom'
							,'setup_charge'
							,'charge_rate'
							,'charge_rate_uom'));
		$sh->addConstraint(new Constraint('project_id', '=', $this->_data['id']));
		
		parent::index($equipmentallocation, $sh);

		$this->view->set('clickcontroller', 'projectequipmentallocations');
		$this->view->set('clickaction', 'edit');
		$this->view->set('page_title', $this->getPageName('', 'view equipment_allocations'));
	}
	
	public function opportunitytoproject() {
		$models=array();
		foreach($this->_uses as $model) {
			$models[get_class($model)]=$model;
		}
		$this->view->set('models',$models);		
		$opportunity = new Opportunity();
		$opportunity->load($this->_data['opportunity_id']);
		$this->view->set('opportunity',$opportunity);		
	}
	
	

/* protected functions */
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName(empty($base)?'projects':$base, $action);
	}
	

/* private functions */
	private function getHours() {
		$hours_cc = new ConstraintChain();
		if(empty($this->_data['week_ending'])) {
			$this->_data['week_ending'] = date(DATE_FORMAT,strtotime('friday'));
		}
		$hours_cc->add(new Constraint('start_time','<=',fix_date($this->_data['week_ending'])));
		$hours_cc->add(new Constraint('start_time','>',date('Y-m-d',strtotime('last monday',strtotime(fix_date($this->_data['week_ending']))))));
		
		if(!isModuleAdmin()) {
			$hours_cc->add(new Constraint('owner','=',EGS_USERNAME));
		}	
		else if(!empty($this->_data['username'])) {
			$hours_cc->add(new Constraint('owner','=',$this->_data['username']));
		}
		if(!empty($this->_data['project_id'])) {
			$hours_cc>add(new Constraint('project_id','=',$this->_data['project_id']));	//for a single project
		}
		$hours = Hour::getForTimesheet($hours_cc);
		return $hours;
	}

}
// end of ProjectsController.php
?>

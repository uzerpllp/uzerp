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
					'tag'=>'timesheets'
				),
				'print_project_breakdowns'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'project_breakdowns'),
					'tag'=>'print_project_breakdowns'
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
			$project->update($this->_data['id'],'completed','true');
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
			'sharing' => array(
				'tag'	=> 'Sharing',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'sharing','id'=>$project->id,'model'=>'Project')
			),
			'spacer',
			'mark_as_complete' => array(
				'tag'	=> 'Mark as Complete',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'complete','id'=>$project->id)
			),
			'view_gantt_chart' => array(
				'tag'	=> 'view_gantt_chart',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'viewgantt','id'=>$project->id)
			)
			);
		
		$viewing_sidebar += array(
			'time-shift' => array(
				'tag'	=> 'time-shift',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'timeshift','id'=>$project->id)
			),
			'spacer',
			'print_project_definition' => array(
				'tag'	=> 'print_project_definition',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'print_definition','id'=>$project->id)
			),
			'print_progress_report' => array(
				'tag'	=> 'print_progress_report',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'progress_report','id'=>$project->id)
			),
			'print_task_form' => array(
				'tag'	=> 'print_task_form',
				'link'	=> array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'print_task_form','id'=>$project->id)
			)
		);
		
		$sidebar=new SidebarController($this->view);
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
				),
				'print_project_breakdowns'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'project_breakdowns'),
					'tag'=>'print_project_breakdowns'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	
	}

	public function progress_report() {
		if (!isset($this->_data['id']))
			sendBack();
		if (isset($this->_data['print']))
			$this->print_progress_report();
		$this->view->set('id',$this->_data['id']);
		
	}

	public function print_progress_report() {
		require FILE_ROOT.'lib/ezpdf/EGSpdf.php';
		$pdf = new EGSpdf('a4','landscape');
		$tableOptions = array(
			'textCol'=>$pdf->colours['body_text'],
			'cols'=>array(
				0=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>100
				),
				1=>array(
					'width'=>166
				),
				2=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>100
				),
				3=>array(
					'width'=>167
				),
				4=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>133
				),
				5=>array(
					'width'=>134
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>800,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$tableOptions2 = array(
			'minHeight'=>35,
			'textCol'=>$pdf->colours['body_text'],
			'cols'=>array(
				0=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>100
				),
				1=>array(
					'width'=>166
				),
				2=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>100
				),
				3=>array(
					'width'=>167
				),
				4=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>80
				),
				5=>array(
					'width'=>53
				),
				6=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>80
				),
				7=>array(
					'width'=>54
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>800,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$tableOptions3 = array(
			'minHeight'=>25,
			'textCol'=>$pdf->colours['body_text'],
			'cols'=>array(
				0=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>100
				),
				1=>array(
					'width'=>433
				),
				2=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>133
				),
				3=>array(
					'width'=>134
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>800,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$tableOptions4 = array(
			'minHeight'=>25,
			'textCol'=>$pdf->colours['body_text'],
			'cols'=>array(
				0=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>266
				),
				1=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>267
				),
				2=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>267
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>800,
			'showLines'=>2,
			'shaded'=>2,
			'shadeCol2'=>array(200/255,200/255,200/255),
			'lineCol'=>$pdf->colours['table_border']
		);
		$tableOptions5 = array(
			'minHeight'=>25,
			'textCol'=>$pdf->colours['body_text'],
			'cols'=>array(
				0=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>266
				),
				1=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>200
				),
				2=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>67
				),
				3=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>200
				),
				4=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>67
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>800,
			'showLines'=>2,
			'shaded'=>2,
			'shadeCol2'=>array(200/255,200/255,200/255),
			'lineCol'=>$pdf->colours['table_border']
		);	
		$tableOptions6 = array(
			'textCol'=>$pdf->colours['body_text'],
			'cols'=>array(
				0=>array(
					'width'=>266
				),
				1=>array(
					'width'=>200
				),
				2=>array(
					'width'=>67
				),
				3=>array(
					'width'=>200
				),
				4=>array(
					'width'=>67
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>800,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$tableOptions7 = array(
			'textCol'=>$pdf->colours['body_text'],
			'cols'=>array(
				0=>array(
					'textCol'=>$pdf->colours['form_label']
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>800,
			'showLines'=>2,
			'shaded'=>2,
			'shadeCol2'=>array(200/255,200/255,200/255),
			'lineCol'=>$pdf->colours['table_border']
		);	
		$tableOptions8 = array(
			'textCol'=>$pdf->colours['body_text'],
			'cols'=>array(
				0=>array(
					'width'=>600
				),
				1=>array(
					'width'=>200
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>800,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);	
		$project = $this->_uses['Project'];
		$project->load($this->_data['id']);
		if(empty($this->_data['period_start'])) {
			$this->_data['period_start'] = $project->start_date;
		}
		if(empty($this->_data['period_end'])) {
			$this->_data['period_end'] = date(DATE_FORMAT);
		}
		$db = DB::Instance();
		$pdf->doLogo();
		$pdf->addTitle('Highlight/Progress Report',$project->name);
		$pdf->doColouredLine();
		$query = 'select person from resourcesoverview where project_id='.$db->qstr($this->_data['id']).' and project_manager';
		$project_manager = $db->GetOne($query);
		$form = array();
		$form[] = array('<b>Reporting Period:</b>',"{$this->_data['period_start']}-{$this->_data['period_end']}",
			'<b>Project Manager:</b>',$project_manager,
			'<b>Study Director:</b>','');
		$pdf->ezTable($form,null,null,$tableOptions);
		$form = array();
		switch($project->rag_status(false)) {
				case 'red':
					$rag_colour = '255,0,0';
					break;
				case 'amber':
					$rag_colour = '234,145,3';
					break;
				case 'green':
					$rag_colour = '0,255,0';
					break;
			}
		$user=getCurrentUser();
		$form[] = array('<b>Prepared by:</b>',$user->getPersonName(),
			'<b>Date prepared:</b>',date(DATE_FORMAT),
			//make the RAG Status be the progress % in the appropriate colour
			'<b>RAG Status:</b> <c:setDifferentColor:0,0,0>(expected}</c:setDifferentColor>','<b><c:setDifferentColor:'.$rag_colour.'>'.$project->progress().'</c:setDifferentColor></b> ('.$project->expected_progress().')',
			'<b>Project phase:</b>',$project->phase);
		$pdf->ezTable($form,null,null,$tableOptions2);
		$form = array();
		$form[] = array('<b>Project description:</b>',$project->description,
			'<b>Project end date:</b>',date(DATE_FORMAT,strtotime($project->end_date)));
		$pdf->ezTable($form,null,null,$tableOptions3);
		$form = array();
		$form[] = array('<b>Key Deliverables Completed this period</b>',
			'<b>Key Deliverables Outstanding this period</b>',
			'<b>Key Deliverables for next reporting period</b>');
		$pdf->ezTable($form,null,null,$tableOptions4);
		$form = array();
		$form[] = array('','','<b>Delivery Date','','<b>Delivery Date');
		$pdf->ezTable($form,null,null,$tableOptions5);

		//dates want to be YYYY-MM-DD for comparisons
		$period_start = fix_date($this->_data['period_start']);
		$period_end = fix_date($this->_data['period_end']);
		
		$query = 'select name from tasks where end_date between '.$db->qstr($period_start).' and '.$db->qstr($period_end).' and deliverable and progress=100 and project_id='.$db->qstr($this->_data['id']);
		$completed_deliverables = $db->GetCol($query);
		
		$query = 'select name,end_date from tasks where end_date between '.$db->qstr($period_start).' and '.$db->qstr($period_end).' and deliverable and progress<100 and project_id='.$db->qstr($this->_data['id']);
		$outstanding_deliverables = $db->GetArray($query);
		
		$next_period_start = $period_end;
		$next_period_end = date('Y-m-d',strtotime($period_end)+(strtotime($period_end)-strtotime($period_start)));
		$query = 'select name,end_date from tasks where end_date between '.$db->qstr($next_period_start).' and '.$db->qstr($next_period_end).' and deliverable and project_id='.$db->qstr($this->_data['id']);
		$next_period_deliverables = $db->GetArray($query);
		
		//need as many rows as the biggest list.
		$count = max(count($completed_deliverables),count($outstanding_deliverables),count($next_period_deliverables));
		$form = array();
		for ($i=0;$i<$count;$i++) {
			$form[] = array(isset($completed_deliverables[$i])?$completed_deliverables[$i]:'',isset($outstanding_deliverables[$i])?$outstanding_deliverables[$i]['name']:'',isset($outstanding_deliverables[$i])?date(DATE_FORMAT,strtotime($outstanding_deliverables[$i]['end_date'])):'',isset($next_period_deliverables[$i])?$next_period_deliverables[$i]['name']:'',isset($next_period_deliverables[$i])?date(DATE_FORMAT,strtotime($next_period_deliverables[$i]['end_date'])):'');
		}
		//and then a blank row
		$form[] = array('','','','','');
		$pdf->ezTable($form,null,null,$tableOptions6);
		$form = array();
		$form[] = array('<b>Issue Management</b>');	
		$pdf->ezTable($form,null,null,$tableOptions7);
		$query = 'select problem_description, status from project_issuesoverview where project_id='.$db->qstr($this->_data['id']);
		$project_issues = $db->GetAssoc($query);
		$form = array();
		$form[] = array('<b>Issue</b>','<b>Action/Status</b>');
		foreach ($project_issues as $name=>$status) {
			$form[] = array($name,$status);
		}
		$pdf->ezTable($form,null,null,$tableOptions8);
		$pdf->ezStream();
		
	}


	public function print_timesheets() {
		require FILE_ROOT.'lib/ezpdf/EGSpdf.php';
		$pdf = new EGSpdf('a4');
		$pdf->doLogo();
		$center = array('justification'=>'center');
		$tableOptions = array(
			'textCol'=>$pdf->colours['body_text'],
			'headCol'=>$pdf->colours['table_headings'],
			'rowGap'=>6,
			'showHeadings'=>true,
			'width'=>555,
			'showLines'=>1,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$username=$this->_data['username'];
		$week_ending=$this->_data['week_ending'];
		$user=getCurrentUser();
		$pdf->addTitle('Timesheet',$user->getPersonName());
		$pdf->addTitle('Week Ending',$week_ending);
		
		$pdf->doColouredLine();
		
		
		$hours =$this->getHours();
		$headings=array('Day','Type','Project','Task','Description','BIllable','Hours','Cumulative');
		foreach($headings as $i=>$heading) {
			$headings[$i]='<b>'.$heading.'</b>';
		}
		//$rows[]=$headings;
		$cumulative=new Interval();
		$unallocated=new Interval();
		$prev_day='';
		foreach($hours as $row) {
			$cumulative=$cumulative->add(new Interval($row['duration']));
			$row['cumulative']=$cumulative->getValue();
			if($prev_day==$row['day']) {
				$row['day']='';
			}
			else {
				$prev_day=$row['day'];
				$row['day']=date('l',strtotime($row['day']));
			}
			if(empty($row['project'])) {
				$unallocated=$unallocated->add(new Interval($row['duration']));
			}
			$rows[]=array_values($row);
		}
		//print_r($rows);exit;
		$pdf->ezTable($rows,$headings,null,$tableOptions);
		$pdf->doColouredLine();
		$data = array(
			array('<b>Total:</b>',$cumulative->getValue()),
			array('<b>Unallocated:</b>',$unallocated->getValue()),
			array('<b>Authorised By:</b>','')
		);
		$tableOptions2 = array(
			'xOrientation'=>'left',
			'textCol'=>$pdf->colours['body_text'],
			'xPos'=>218,
			'width'=>200,
			'cols'=>array(
				0=>array('textCol'=>$pdf->colours['form_label']),
				1=>array('justification'=>'right')
			),
			'showLines'=>0,
			'showHeadings'=>false,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$pdf->ezTable($data,null,null,$tableOptions2);
		$pdf->ezStream();
		exit(0);
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
	
	public function viewGantt() {
		$project = $this->_uses['Project'];
		$db=DB::Instance();
		$project->load($this->_data['id']) or sendBack();
		$flash=Flash::Instance();
		if($project->tasks->count()==0) {
			$flash->addError('You can\'t view a gantt chart if a project has no tasks');
			sendBack();
		}
		$installed=EGSGantt::Installed();
		if(!$installed) {
			$flash->addError('Gantt Chart viewing requires jpgraph to be installed in app/plugins, contact your system administrator to fix this');
			sendBack();
		}
		$sidebar=new SidebarController($this->view);
		$sidebar->addList(
			'currently_viewing',
			array(
				$project->name => array(
					'tag' => $project->name,
					'link' => array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'view','id'=>$project->id)
				),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'edit','id'=>$project->id)
				),
				'delete' => array(
					'tag' => 'Delete',
					'link' => array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'delete','id'=>$project->id)
				),
				'mark_as_complete' => array(
					'tag' => 'Mark as Complete',
					'link' => array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'complete','id'=>$project->id)
				)
				,
				'view_gantt_chart' => array(
					'tag' => 'view_gantt_chart',
					'link' => array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'viewgantt','id'=>$project->id)
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		$graph = new EGSGantt($project,false);
		if(!$graph->isCached()) {
			foreach($project->tasks as $task) {
				$graph->addGanttBar($task);
			}
			$graph->setTitle($project->name);
			$graph->process();
			$graph->render();
		}
		$this->view->set('img_src',$graph->getFilename());
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
	
	public function print_definition() {
		require FILE_ROOT.'lib/ezpdf/EGSpdf.php';
		$db = DB::Instance();
		$project = $this->_uses['Project'];
		$project->load($this->_data['id']);
		$pdf = new EGSpdf('a4');
		
		$tableOptions = array(
			'textCol'=>$pdf->colours['body_text'],
			'cols'=>array(
				0=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>125
				),
				2=>array(
					'textCol'=>$pdf->colours['form_label'],
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>555,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$tableOptions3 = array(
			'textCol'=>$pdf->colours['body_text'],
			'cols'=>array(
				0=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>277
				),
				1=>array(
					'textCol'=>$pdf->colours['form_label'],
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>555,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$tableOptions4 = array(
			'textCol'=>$pdf->colours['body_text'],
			'minHeight'=>105,
			'cols'=>array(
				0=>array(
					'width'=>277
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>555,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$tableOptions5 = array(
			'textCol'=>$pdf->colours['body_text'],
			'minHeight'=>56,
			'cols'=>array(
				0=>array(
					'textCol'=>$pdf->colours['form_label'],
					'width'=>125
				),
				2=>array(
					'textCol'=>$pdf->colours['form_label'],
				)
			),
			'rowGap'=>6,
			'showHeadings'=>0,
			'width'=>555,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$pdf->addTitle('Project Definition Form',$project->job_no);
		$pdf->doLogo();
		$pdf->doColouredLine();
		$form = array();
		$form[] = array('<b>Project Title:</b>',$project->name);
		$pdf->ezTable($form,null,null,$tableOptions);		
		$form = array();
		$form[] = array('<b>Client:</b>',$project->company,'<b>BD Contact:</b>',$project->opp_contact());
		$pdf->ezTable($form,null,null,$tableOptions);		
		$query = 'select name from tasks where project_id='.$db->qstr($this->_data['id']).' and deliverable';
		$tasks = $db->GetCol($query);
		$form = array();
		$form[] = array('<b>Project Background:</b>',$project->description);
		$form[] = array('<b>Project Objectives:</b>',$project->objectives);
		$form[] = array('<b>Project Deliverables:</b>',implode(', ',$tasks));
		$pdf->ezTable($form,null,null,$tableOptions5);		
		$form = array();
		$form[] = array('<b>This project will include:</b>','<b>This project will not include:</b>');
		$pdf->ezTable($form,null,null,$tableOptions3);		
		$form = array();
		$form[] = array($project->requirements,$project->exclusions);
		$pdf->ezTable($form,null,null,$tableOptions4);		
		$query = 'select name from tasks where project_id='.$db->qstr($this->_data['id']).' and milestone';
		$tasks = $db->GetCol($query);
		$form = array();
		$form[] = array('<b>Key deadlines:</b>',implode(', ',$tasks));
		$form[] = array('<b>Constraints:</b>',$project->constraints);
		$form[] = array('<b>Key assumptions:</b>',$project->key_assumptions);
		$pdf->ezTable($form,null,null,$tableOptions5);		
		$form = array();
		$form[] = array('<b>MP Consultant:</b>',$project->consultant_details);
		$pdf->ezTable($form,null,null,$tableOptions);		
		$query = 'select person from resourcesoverview where project_id='.$db->qstr($this->_data['id']).' and project_manager';
		$project_manager = $db->GetOne($query);
		$query = 'select person from resourcesoverview where project_id='.$db->qstr($this->_data['id']);
		$team = $db->GetCol($query);
		$form = array();
		$form[] = array('<b>Study director:</b>',$project->key_contact,'<b>Project manager:</b>',$project_manager);
		$pdf->ezTable($form,null,null,$tableOptions);		
		$form = array();
		$form[] = array('<b>Project team:</b>',implode(', ',$team));
		$form[] = array('<b>Man-hours estimate:</b>',$project->duration() .'');
		$pdf->ezTable($form,null,null,$tableOptions5);		
		$form = array();
		$form[] = array('<b>Start Date:</b>',date(DATE_FORMAT,strtotime($project->start_date)),'<b>Expected completion date:</b>',date(DATE_FORMAT,strtotime($project->end_date)));
		$pdf->ezTable($form,null,null,$tableOptions5);		
		$pdf->ezStream();
		exit(0);
	}

	public function print_task_form() {
		require FILE_ROOT.'lib/ezpdf/EGSpdf.php';
		$db = DB::Instance();
		$project = $this->_uses['Project'];
		$project->load($this->_data['id']);
		$pdf = new EGSpdf('a4','landscape');
		$tableOptions = array(
			'textCol'=>$pdf->colours['body_text'],
			'headCol'=>$pdf->colours['table_headings'],
			'rowGap'=>6,
			'showHeadings'=>1,
			'width'=>800,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$pdf->addTitle('Project Task Form',$project->name);	
		$pdf->doLogo();
		$pdf->doColouredLine();
		$query = 'select * from tasks where project_id='.$db->qstr($this->_data['id']).' order by end_date ASC';
		$tasks = $db->GetAssoc($query);
		$form = array();
		$headings = array('<b>Key Tasks</b>','<b>Task Owner</b>','<b>Time Allocated</b>','<b>Deadline</b>','<b>Completed</b>');
		foreach ($tasks as $task) {
			$form[] = array($task['name'],$task['owner'],$task['duration'],date(DATE_FORMAT,strtotime($task['end_date'])),($task['progress']==100)?'Yes':'No');
		}
		$pdf->ezTable($form,$headings,null,$tableOptions);		
		$pdf->ezStream();
		exit(0);
	}

	public function project_breakdowns() {
		
	}

	public function print_project_breakdowns() {
		require FILE_ROOT.'lib/ezpdf/EGSpdf.php';
		$db = DB::Instance();

		$pdf = new EGSpdf('a4');
		$tableOptions = array(
			'textCol'=>$pdf->colours['body_text'],
			'headCol'=>$pdf->colours['table_headings'],
			'rowGap'=>4,
			'showHeadings'=>1,
			'width'=>550,
			'showLines'=>2,
			'shaded'=>0,
			'lineCol'=>$pdf->colours['table_border']
		);
		$pdf->addTitle('Project Breakdowns','All projects');	
		$pdf->doColouredLine();
		$query = 'select id,title from project_work_types where usercompanyid='.EGS_COMPANY_ID;
		$work_types = $db->GetAssoc($query);
		if (isset($this->_data['geographical'])) {
			$data = array();
			$headers = array();
			$headers[] = '<b>Geographical</b>';
			$totals = array();
			$totals[] = '<b>Total</b>';
			$query = "select coalesce(c.name,'Undefined') from projects p left join company on company.id=p.company_id left join companyaddress ca on ca.company_id=company.id left join countries c on ca.countrycode=c.code where p.usercompanyid=".EGS_COMPANY_ID." group by c.name";
			$countries = $db->GetCol($query);
			foreach ($countries as $country) {
				$data[$country] = array();
			}
			foreach ($work_types as $id=>$title) {
				$query = "select coalesce(c.name,'Undefined'),count(p.id) from projects p left join company on company.id=p.company_id left join companyaddress ca on ca.company_id=company.id left join countries c on ca.countrycode=c.code where p.work_type_id=$id and p.usercompanyid=".EGS_COMPANY_ID." group by c.name";
				$results = $db->GetAssoc($query);
				$headers[] = '<b>'.$title.'</b>';
				$total = 0;
				foreach ($results as $name=>$count) {
					$data[$name][$id] = $count;
					$total += $count;
				}
				$totals[] = '<b>'.$total.'</b>';
				foreach ($data as $key=>$value) {
					if (!isset($data[$key][$id]))
						$data[$key][$id] = 0;
				}
			}
			$headers[] = '<b>Total</b>';
			$table_data = array();
			$total_count = 0;
			foreach ($data as $key=>$values) {
				$row = array();
				$row[] = $key;
				$count = 0;
				foreach ($values as $value) {
					$row[] = $value;
					$count += $value;
				}
				$total_count += $count;
				$row[] = '<b>'.$count.'</b>';
				$table_data[] = $row;
			}
			$totals[] = '<b>'.$total_count.'</b>';
			$table_data[] = $totals;
			$pdf->ezTable($table_data,$headers,null,$tableOptions);
			$pdf->ezLn();		
		}
		if (isset($this->_data['project_type'])) {
			$data = array();
			$headers = array();
			$headers[] = '<b>Project Type</b>';
			$totals = array();
			$totals[] = '<b>Total</b>';
			$query = "select pc.name from projects p left join project_categories pc on p.category_id = pc.id where p.usercompanyid=".EGS_COMPANY_ID." group by pc.name";
			$project_types = $db->GetCol($query);
			foreach ($project_types as $project_type) {
				$data[$project_type] = array();
			}
			foreach ($work_types as $id=>$title) {
				$query = "select pc.name,count(p.id) from projects p left join project_categories pc on p.category_id = pc.id where p.work_type_id=$id and p.usercompanyid=".EGS_COMPANY_ID." group by pc.name";
				$results = $db->GetAssoc($query);
				$headers[] = '<b>'.$title.'</b>';
				$total = 0;
				foreach ($results as $name=>$count) {
					$data[$name][$id] = $count;
					$total += $count;
				}
				$totals[] = '<b>'.$total.'</b>';
				foreach ($data as $key=>$value) {
					if (!isset($data[$key][$id]))
						$data[$key][$id] = 0;
				}
			}
			$headers[] = '<b>Total</b>';
			$table_data = array();
			$total_count = 0;
			foreach ($data as $key=>$values) {
				$row = array();
				$row[] = $key;
				$count = 0;
				foreach ($values as $value) {
					$row[] = $value;
					$count += $value;
				}
				$total_count += $count;
				$row[] = '<b>'.$count.'</b>';
				$table_data[] = $row;
			}
			$totals[] = '<b>'.$total_count.'</b>';
			$table_data[] = $totals;
			$pdf->ezTable($table_data,$headers,null,$tableOptions);	
			$pdf->ezLn();	
		}
		if (isset($this->_data['cost'])) {
			$data = array();
			$headers = array();
			$headers[] = '<b>Results</b>';
			$data['Income'] = array();
			$data['Costs'] = array();
			$data['<b>Profit/Loss</b>'] = array();
			$data['Net Profit Margin'] = array();
			$data['Markup'] = array();
			foreach ($work_types as $id=>$title) {
				$headers[] = '<b>'.$title.'</b>';
				$query = "select sum(p.cost) from projects p where p.work_type_id=$id and p.usercompanyid=".EGS_COMPANY_ID;
				$data['Income'][$id] = sprintf('%01.2f',$db->GetOne($query));
				if (empty($data['Income'][$id]))
					$data['Income'][$id] = '';
				$query = "select r.standard_rate, sum(h.duration) from projects p left join resources r on r.project_id=p.id left join users u on u.person_id=r.person_id left join hours h on u.username=h.owner where p.work_type_id=$id and p.usercompanyid=".EGS_COMPANY_ID." group by r.standard_rate";
				$total_cost = 0;
				$results = $db->GetAssoc($query);
				foreach ($results as $cost=>$duration) {
					if (!empty($duration)) {
						list($hours,$mins,$secs) = explode(':',$duration);
						$hours += $mins/60;
						$total_cost += $cost*$hours;
					}
				}
				$query = "select (sum((to_char(h.duration,'HH24')::float+(to_char(h.duration,'MI')::float/60))*eq.hourly_cost)+count(t.id)*eq.setup_cost) from projects p left join hours h on h.project_id=p.id left join tasks t on (t.id=h.task_id and h.equipment) right join project_equipment eq on (t.equipment_id=eq.id) where p.work_type_id=$id and p.usercompanyid=".EGS_COMPANY_ID." group by eq.setup_cost";
				$total_cost += $db->GetOne($query);
				if ($total_cost==0)
					$data['Costs'][$id] = '';
				else			
					$data['Costs'][$id] = sprintf('%01.2f',$total_cost);
			}
			$headers[] = '<b>Total</b>';
			$data['Income'][] = sprintf('%01.2f',array_sum($data['Income']));
			$data['Costs'][] = sprintf('%01.2f',array_sum($data['Costs']));
			foreach($data['Income'] as $key=>$value) {
				$data['<b>Profit/Loss</b>'][$key] = sprintf('%01.2f',$value - $data['Costs'][$key]);
				if ($data['<b>Profit/Loss</b>'][$key]==0)
					$data['<b>Profit/Loss</b>'][$key] = '';
				if ($value<>0)
					$data['Net Profit Margin'][$key] = sprintf('%01.2f%%',($data['<b>Profit/Loss</b>'][$key]/$value)*100);
				else
					$data['Net Profit Margin'][$key] = '';
				if ($data['Costs'][$key]<>0)
					$data['Markup'][$key] =  sprintf('%01.2f%%',($data['<b>Profit/Loss</b>'][$key]/$data['Costs'][$key])*100);
				else
					$data['Markup'][$key] = '';
				
			}
			$table_data = array();
			foreach ($data as $key=>$values) {
				$row = array();
				$row[] = $key;
				foreach ($values as $value) {
					$row[] = $value;
				}
				$table_data[] = $row;
			}
			foreach ($table_data as &$row) {
				$row[count($row)-1] = '<b>'.$row[count($row)-1].'</b>';
			}
			$pdf->ezTable($table_data,$headers,null,$tableOptions);	
			$pdf->ezLn();	
		}
		if (isset($this->_data['time'])) {
			$data = array();
			$headers = array();
			$headers[] = '';
			$row = array();
			$row[] = 'Ave No Of Days';
			foreach ($work_types as $id=>$title) {
				$query = "select sum(to_char(h.duration,'HH24')::float+(to_char(h.duration,'MI')::float/60)) from projects p join hours h on (h.project_id=p.id) where p.work_type_id=$id and p.usercompanyid=".EGS_COMPANY_ID;
				$total_duration = $db->GetOne($query);
				$query = "select count(*) from projects p where p.work_type_id=$id and p.usercompanyid=".EGS_COMPANY_ID;
				$count = $db->GetOne($query);
				$headers[] = '<b>'.$title.'</b>';
				$row[] = sprintf('%01.2f',($count==0)?0:($total_duration/$count)/SystemCompanySettings::DAY_LENGTH);
			}
			$data[] = $row;
			$pdf->ezTable($data,$headers,null,$tableOptions);	
			$pdf->ezLn();	
		}
		if (isset($this->_data['equipment'])) {
			$data = array();
			$headers = array();
			$headers[] = '<b>Techniques - Hours</b>';
			$totals = array();
			$totals[] = '<b>Total</b>';
			$query = 'select name from project_equipment where usercompanyid='.EGS_COMPANY_ID;
			foreach ($db->GetCol($query) as $equipment) {
				$data[$equipment] = array();
			}
			foreach ($work_types as $id=>$title) {
				$query = "select eq.name,sum(to_char(h.duration,'HH24')::float+(to_char(h.duration,'MI')::float/60)) from projects p left join tasks t on t.project_id = p.id join project_equipment eq on eq.id=t.equipment_id left join hours h on (t.id=h.task_id) where h.equipment and p.usercompanyid=".EGS_COMPANY_ID." and p.work_type_id=$id group by eq.name";
				$results = $db->GetAssoc($query);
				$headers[] = '<b>'.$title.'</b>';
				$total = 0;
				foreach ($results as $name=>$count) {
					$data[$name][$id] = sprintf('%01.2f',$count);
					$total += $count;
				}
				$totals[] = sprintf('<b>%01.2f</b>',$total);
				foreach ($data as $key=>$value) {
					if (!isset($data[$key][$id]))
						$data[$key][$id] = sprintf('%01.2f',0);
				}
			}
			$headers[] = '<b>Total</b>';
			$table_data = array();
			$total_count = 0;
			foreach ($data as $key=>$values) {
				$row = array();
				$row[] = $key;
				$count = 0;
				foreach ($values as $value) {
					$row[] = $value;
					$count += $value;
				}
				$total_count += $count;
				$row[] = sprintf('<b>%01.2f</b>',$count);
				$table_data[] = $row;
			}
			$totals[] = sprintf('<b>%01.2f</b>',$total_count);
			$table_data[] = $totals;
			$pdf->ezTable($table_data,$headers,null,$tableOptions);	
			$pdf->ezLn();					
		}
		if (isset($this->_data['staffing'])) {
			$data = array();
			$headers = array();
			$headers[] = '<b>Staffing - Hours</b>';
			$totals = array();
			$totals[] = '<b>Total</b>';
			$query = "select name from resource_types where usercompanyid=".EGS_COMPANY_ID;
 			$query = "select coalesce(rt.name,'Other') from projects p left join hours h on h.project_id=p.id left join users u on h.owner=u.username left join resources r on (u.person_id=r.person_id and r.project_id=p.id) left join resource_types rt on rt.id=r.resource_type_id where p.usercompanyid=".EGS_COMPANY_ID;				
			$types = $db->GetCol($query);
			foreach ($types as $type) {
				$data[$type] = array();
			}
			$data['Business Development'] = array();
			foreach ($work_types as $id=>$title) {
 				$query = "select coalesce(rt.name,'Other'),sum(to_char(h.duration,'HH24')::float+(to_char(h.duration,'MI')::float/60)) from projects p left join hours h on h.project_id=p.id left join users u on h.owner=u.username left join resources r on (u.person_id=r.person_id and r.project_id=p.id) left join resource_types rt on rt.id=r.resource_type_id where p.work_type_id=$id and p.usercompanyid=".EGS_COMPANY_ID." group by rt.name";				
				$results = $db->GetAssoc($query);
				$headers[] = '<b>'.$title.'</b>';
				$total = 0;
				foreach ($results as $name=>$count) {
					$data[$name][$id] = sprintf('%01.2f',$count);
					$total += $count;
				}
				$query = "select sum(to_char(h.duration,'HH24')::float+(to_char(h.duration,'MI')::float/60)) from projects p join opportunities o on o.id=p.opportunity_id left join hours h on h.opportunity_id=o.id left join users u on h.owner=u.username left join resources r on (u.person_id=r.person_id and r.project_id=p.id) left join resource_types rt on rt.id=r.resource_type_id where p.work_type_id=$id and p.usercompanyid=".EGS_COMPANY_ID." group by rt.name";
				$result = $db->GetOne($query);
				if (!empty($result)) {
					$data['Business Development'][$id] = sprintf('%01.2f',$result);
					$total += $result;
				}
				$totals[] = sprintf('<b>%01.2f</b>',$total);
				foreach ($data as $key=>$value) {
					if (!isset($data[$key][$id]))
						$data[$key][$id] = sprintf('%01.2f',0);
				}
			}
			$headers[] = '<b>Total</b>';
			$table_data = array();
			$total_count = 0;
			foreach ($data as $key=>$values) {
				$row = array();
				$row[] = $key;
				$count = 0;
				foreach ($values as $value) {
					$row[] = $value;
					$count += $value;
				}
				$total_count += $count;
				$row[] = sprintf('<b>%01.2f</b>',$count);
				$table_data[] = $row;
			}
			$totals[] = sprintf('<b>%01.2f</b>',$total_count);
			$table_data[] = $totals;
			$pdf->ezTable($table_data,$headers,null,$tableOptions);
			$pdf->ezLn();			
		}
		if (isset($this->_data['hour_type'])) {
			$data = array();
			$headers = array();
			$headers[] = '<b>Project Hours</b>';
			$totals = array();
			$totals[] = '<b>Total</b>';
			$query = "select name from hour_types where usercompanyid=".EGS_COMPANY_ID;
			$types = $db->GetCol($query);
			foreach ($types as $type) {
				$data[$type] = array();
			}
			foreach ($work_types as $id=>$title) {
				$query = "select ht.name,sum(to_char(h.duration,'HH24')::float+(to_char(h.duration,'MI')::float/60)) from projects p left join hours h on p.id=h.project_id join hour_types ht on h.type_id=ht.id where p.work_type_id=$id and p.usercompanyid=".EGS_COMPANY_ID." group by ht.name";
				$results = $db->GetAssoc($query);
				$headers[] = '<b>'.$title.'</b>';
				$total = 0;
				foreach ($results as $name=>$count) {
					$data[$name][$id] = sprintf('%01.2f',$count);
					$total += $count;
				}
				$totals[] = sprintf('<b>%01.2f</b>',$total);
				foreach ($data as $key=>$value) {
					if (!isset($data[$key][$id]))
						$data[$key][$id] = sprintf('%01.2f',0);
				}
			}
			$headers[] = '<b>Total</b>';
			$table_data = array();
			$total_count = 0;
			foreach ($data as $key=>$values) {
				$row = array();
				$row[] = $key;
				$count = 0;
				foreach ($values as $value) {
					$row[] = $value;
					$count += $value;
				}
				$total_count += $count;
				$row[] = sprintf('<b>%01.2f</b>',$count);
				$table_data[] = $row;
			}
			$totals[] = sprintf('<b>%01.2f</b>',$total_count);
			$table_data[] = $totals;
			$pdf->ezTable($table_data,$headers,null,$tableOptions);
			$pdf->ezLn();				
		}
		$pdf->ezStream();
		exit(0);
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

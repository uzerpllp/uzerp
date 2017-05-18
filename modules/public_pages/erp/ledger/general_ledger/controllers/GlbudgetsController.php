<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GlbudgetsController extends printController {

	protected $version='$Revision: 1.12 $';
	protected $_templateobject;
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new GLBudget();
		$this->uses($this->_templateobject);

	}

	public function index(){

		$errors=array();
	
		$this->setSearch('glbalancesSearch', 'useDefault');

		$this->view->set('clickaction', '#');
		parent::index(new GLBudgetCollection($this->_templateobject));		
		
		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['viewaccounts']= array(
					'tag'=>'View All Accounts',
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'glaccounts'
											 ,'action'=>'index'
											 )
									   )
					);
		
		$sidebarlist['viewcentres']= array(
					'tag'=>'View All Centres',
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'glcentres'
											 ,'action'=>'index'
											 )
									   )
					);
		
		$sidebarlist['budgetdatagrid']= array(
					'tag'=>'Enter Data Grid',
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'glbudgets'
											 ,'action'=>'inputdatagrid'
											 )
									   )
					);
		
		$sidebar->addList('Actions',$sidebarlist);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function inputDataGrid() {
		
	// THIS ISN'T GENERIC, BUT IT PROBABLY SHOULD BE :-)
	
		// set variables
		$flash=Flash::Instance();
		$errors=array();
		$basic_search=array();
		$rows = array();
		$cols = array();
		
		if(isset($_SESSION['di_search'])) {
			$this->_data['Search']=$_SESSION['di_search'];
			unset($_SESSION['di_search']);
		}
		
	// search
	
		// get years
		$years_collection=new GLBudgetYearCollection(new GLBudgetYear());
		$sh=new SearchHandler($years_collection,false);
		$sh->setOrderby('year');
		$years_collection->load($sh);
		
		// construct years array for search
		foreach($years_collection->getArray() as $key=>$value) {
			$years[$value['year']]=$value['year'];
		}
		
		// get / set current selected year
		if(isset($this->_data['Search']['year']) && !isset($this->_data['Search']['clear'])) {
			$current_year=$this->_data['Search']['year'];
		} else {
			$current_year=key($years);
		}
		$basic_search['year']=$current_year;
		if(empty($years)) {
			$errors[]="No years to base data on";
		}
		
		if(empty($errors)) {
			// get centres 
			$centres_collection=new GLCentreCollection(new GLCentre);
			$sh=new SearchHandler($centres_collection,false);
			$sh->setOrderby('cost_centre');
			$centres_collection->load($sh);
			// construct centres array for search
			$centres=array();
			foreach($centres_collection->getArray() as $key=>$value) {
				$centres[$value['id']]=$value['cost_centre']." - ".$value['description'];
			}
			// get / set current selected centre
			if(isset($this->_data['Search']['centres']) && !isset($this->_data['Search']['clear'])) {
				$current_centre=$this->_data['Search']['centres'];
			} else {
				$current_centre=key($centres);
			}
			$basic_search['centres']=$current_centre;
			if(empty($centres)) {
				$errors[]="No centres to base data on";
			}
		}
	
		if(empty($errors)) {
			// columns
			$column=new GLPeriodCollection(new GLPeriod());
			$sh=new SearchHandler($column,false);
			$sh->addConstraint(new Constraint('year', '=' , $current_year));
			$sh->setOrderby('period');
			$column->load($sh);
			$cols=$column->getArray();
			$glperiod_ids=array();
			if(!empty($cols)) {
				foreach($cols as $key=>$value) {
					$glperiod_ids[]=$value['id'];
				}
			}
			if(empty($glperiod_ids)) {
				$errors[]="No periods to base data on";
			}
		}
		
		if(empty($errors)) {
			// rows
			$row=new GLAccountCentreCollection(new GLAccountCentre());
			$sh=new SearchHandler($row,false);
			$sh->addConstraint(new Constraint('glcentre_id', '=' , $current_centre));
			$sh->setOrderby('glaccount');
			$row->load($sh);
			$rows=$row->getArray();
			$glaccount_ids=array();
			if(!empty($rows)) {
				foreach($rows as $key=>$value) {
					$glaccount_ids[]=$value['glaccount_id'];
				}
			}
			if(empty($glaccount_ids)) {
				$errors[]="No accounts to base data on";
			}
		}
		
		if(empty($errors)) {
			// grid data
			$data_collection=new GLBudgetCollection(new GLBudget());
			$sh=new SearchHandler($data_collection,false);
			$sh->addConstraint(new Constraint('glcentre_id', '=' , $current_centre));
			$sh->addConstraint(new Constraint('glaccount_id', 'IN' , '('.implode(',',$glaccount_ids).')'));
			$sh->addConstraint(new Constraint('glperiods_id', 'IN' , '('.implode(',',$glperiod_ids).')'));
			$data_collection->load($sh);
			$data_arr=$data_collection->getArray();
			$data=array();
			if(count($data_arr)>0) {
				foreach($data_arr as $key=>$value) {
					$data[$value['glperiods_id']][$value['glaccount_id']]=array('value'=>$value['value'],'id'=>$value['id']);
				}
			}
		}
		
		if(!empty($errors)) {
			$flash->addErrors($errors);
			$this->view->set('errors',true);
		}
	// search
		
		$search=new BaseSearch();
		$search->addSearchField('year', 'Year', 'select', $current_year, 'basic');
		$search->setOptions('year',$years);
		$search->addSearchField('centres', 'GL Centres', 'select', $current_centre, 'basic');
		$search->setOptions('centres',$centres);
		
	// output vars
	
		$this->view->set('basic_search',$basic_search);
		$this->view->set('search',$search);
		$this->view->set('columns',$cols);
		$this->view->set('rows',$rows);
		$this->view->set('data',$data);
		$this->view->set('do_name','GLBudgetCollection');
		$this->view->set('search_centre',$current_centre);
		
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'general_ledger_budgets':$base), $action);
	}
	
	public function save_data_input() {
		$flash=Flash::Instance();
		$errors=array();
		
		$_SESSION['di_search']=$this->_data['Search'];
		
		if(parent::save('GLBudgetCollection','',$errors)) {
			sendBack();
		} else {
			$flash->addErrors($errors);
			sendBack();
		}
		
	}

}
?>

<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MfcentresController extends ManufacturingController
{

	protected $version='$Revision: 1.12 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = new MFCentre();
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		if (!$this->CheckParams('mfdept_id'))
		{
			sendBack();
		}
		sendTo('mfdepts','view',$this->_modules
			  ,array('id'=>$this->_data['mfdept_id']));
	}

	public function _new()
	{
		$flash=Flash::Instance();
		
		parent::_new();
		$mfcentre=$this->_uses[$this->modeltype];
		
		$mfdept=new MFDept();
		
		if ($mfcentre->isLoaded())
		{
			$mfdept=$mfcentre->dept_detail;
		}
		elseif (!empty($this->_data['mfdept_id']))
		{
			$mfcentre->mfdept_id = $this->_data['mfdept_id'];
			
			$mfdept->load($this->_data['mfdept_id']);
			
			if (!$mfdept->isloaded())
			{
				$flash->addError('Cannot load Department');
				sendBack();
			}
		
		}
		
		if ($mfdept->isLoaded())
		{
			$this->view->set('page_title', $this->getPageName().' for Dept. '.$mfdept->getIdentifierValue());
		}
		
		$this->view->set('mfdept', $mfdept);
	
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		if (!$this->CheckParams($this->modeltype)) {
			sendBack();
		}
		$flash=Flash::Instance();
		$db = DB::Instance();
		$db->StartTrans();
		$errors = array();
		$update_cost = false;
		if (!empty($this->_data[$this->modeltype]['id'])) {
			$mfcentre = new MFCentre;
			$mfcentre->load($this->_data[$this->modeltype]['id']);
			$old_rate = $mfcentre->centre_rate;
			$new_rate = $this->_data[$this->modeltype]['centre_rate'];
			$update_cost = ($old_rate != $new_rate);
		}
		if (parent::save_model($this->modeltype, $this->_data[$this->modeltype])) {
			if ($update_cost) {
				$cc = new ConstraintChain;
				$cc->add(new Constraint('mfcentre_id', '=', $this->_data[$this->modeltype]['id']));
				$db = DB::Instance();
				$date = Constraint::TODAY;
				$between = $date.' BETWEEN '.$db->IfNull('start_date', $date).' AND '.$db->IfNull('end_date', $date);
				$cc->add(new Constraint('', '', '('.$between.')'));
				$mfoperation = new MFOperation;
				$mfoperation_ids = array_keys($mfoperation->getAll($cc));
				$stitem_ids = array();
				foreach ($mfoperation_ids as $mfoperation_id) {
					if (!$mfoperation->load($mfoperation_id)) {
						$errors[] = 'Could not save latest costs';
						$db->FailTrans();
						break;
					}
					if (in_array($mfoperation->stitem_id, $stitem_ids)) {
						continue;
					}
					$stitem_ids[] = $mfoperation->stitem_id;
				}
				if (count($stitem_ids) > 0) {
					$stitem = new STItem;
				}
				foreach ($stitem_ids as $stitem_id) {
					if (!$stitem->load($stitem_id)) {
						$errors[] = 'Could not save latest costs';
						$db->FailTrans();
						break;
					}
					$old_cost = $stitem->latest_ohd;
					$stitem->calcLatestCost();
					$new_cost = $stitem->latest_ohd;
					if (bccomp($old_cost, $new_cost, $stitem->cost_decimals) == 0) {
						continue;
					}
					if ((!$stitem->saveCosts()) || (!STCost::saveItemCost($stitem))) {
						$errors[] = 'Could not save latest costs';
						$db->FailTrans();
						break;
					}
					if (!$stitem->rollUp(STItem::ROLL_UP_MAX_LEVEL)) {
						$errors[] = 'Could not roll-up latest costs';
						$db->FailTrans();
						break;
					}
				}
			}
		} else {
			$errors[] = 'Could not save centre';
			$db->FailTrans();
		}
		$db->CompleteTrans();
		if (count($errors) == 0) {;
			sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? null);
		} else {
			$flash->addErrors($errors);
			$this->_data['mfdept_id']=$this->_data[$this->modeltype]['mfdept_id'];
			$this->refresh();
		}

	}
	
	public function view() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$transaction=$this->_uses[$this->modeltype];
		$id=$transaction->id;
		$this->view->set('transaction',$transaction);

		$elements = new MFOperationCollection(new MFOperation);
		if (!isset($this->_data['orderby'])
		&& !isset($this->_data['page'])) {
			$sh = new SearchHandler($elements, false);
			$sh->addConstraint(new Constraint('mfcentre_id','=', $id));
		} else {
			$sh = new SearchHandler($elements);
		}
		$sh->extract();

		parent::index($elements, $sh);
		
		$sidebar=new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['depts']= array(
						'tag' => 'All Departments',
						'link' => array('modules'=>$this->_modules
										,'controller'=>'MFDepts'
										,'action'=>'index'
										)
									);
		$sidebarlist['centres']= array(
						'tag' => 'Centres for Department '.$transaction->mfdept,
						'link' => array('modules'=>$this->_modules
										,'controller'=>'MFDepts'
										,'action'=>'view'
										,'id'=>$transaction->mfdept_id
										)
									  );
		$sidebar->addList('Show',$sidebarlist);
		
		$sidebarlist=array();
		$sidebarlist['edit']= array(
						'tag' => 'Edit',
						'link' => array('modules'=>$this->_modules
										,'controller'=>$this->name
										,'action'=>'edit'
										,'id'=>$id
										)
									);
		if ($elements->count()==0) {
			$sidebarlist['delete']= array(
							'tag' => 'Delete',
							'link' => array('modules'=>$this->_modules
											,'controller'=>$this->name
											,'action'=>'delete'
											,'id'=>$id
											,'mfdept_id'=>$transaction->mfdept_id
											)
										 );	
		}
		$sidebar->addList('This Centre',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName((empty($base)?'centres':$base), $action);
	}

/*
 * Ajax Functions
 */
	public function allow_production_recording ($_mfdept_id = '')
	{
		
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['mfdept_id'])) { $_mfdept_id=$this->_data['mfdept_id']; }
		}
		
		$mfdept=new MFDept();
		$mfdept->load($_mfdept_id);
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$mfdept->production_recording);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $mfdept->production_recording;
		}
		
	}
}

// End of MfcentresController

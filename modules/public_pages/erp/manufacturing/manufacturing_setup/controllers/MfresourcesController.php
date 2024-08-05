<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MfresourcesController extends ManufacturingController {

	protected $version='$Revision: 1.9 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new MFResource();
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'view');
		parent::index(new MFResourceCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'Add New Resource'
							,'link'=>array_merge($this->_modules
												,array('controller'=>$this->name
													  ,'action'=>'new'
													  )
												)
							)
				 )
			);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		$db = DB::Instance();
		$db->StartTrans();
		$errors = array();
		$update_cost = false;
		if (isset($this->_data[$this->modeltype]['id']) && $this->_data[$this->modeltype]['id']) {
			$mfresource = new MFResource;
			$mfresource->load($this->_data[$this->modeltype]['id']);
			$old_rate = $mfresource->resource_rate;
			$new_rate = $this->_data[$this->modeltype]['resource_rate'];
			$update_cost = ($old_rate != $new_rate);
		}
		if (parent::save_model($this->modeltype, null, $errors)) {
			if ($update_cost) {
				$cc = new ConstraintChain;
				$cc->add(new Constraint('mfresource_id', '=', $this->_data[$this->modeltype]['id']));
				$db = DB::Instance();
				$date = Constraint::TODAY;
				$between = $date.' BETWEEN '.$db->IfNull('start_date', $date).' AND '.$db->IfNull('end_date', $date);
				$cc->add(new Constraint('', '', '('.$between.')'));
				$mfoperation = new MFOperation;
				//$db->Debug();
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
					$old_cost = $stitem->latest_lab;
					$stitem->calcLatestCost();
					$new_cost = $stitem->latest_lab;
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
			$errors[] = 'Could not save resource';
			$db->FailTrans();
		}
		$db->CompleteTrans();
		if (count($errors) == 0) {;
			sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? null);
		} else {
			$flash->addErrors($errors);
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
			$sh->addConstraint(new Constraint('mfresource_id','=', $id));
		} else {
			$sh = new SearchHandler($elements);
		}
		$sh->extract();

		parent::index($elements, $sh);
		
		$sidebar=new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['view']= array(
						 'tag' => 'View all resources'
						,'link' => array('modules'=>$this->_modules
										,'controller'=>$this->name
										,'action'=>'index'
										)
									);
		$sidebarlist['edit']= array(
						 'tag' => 'Edit'
						,'link' => array('modules'=>$this->_modules
										,'controller'=>$this->name
										,'action'=>'edit'
										,'id'=>$id
										)
									);
		if ($elements->count()==0) {
			$sidebarlist['delete']= array(
							 'tag' => 'Delete'
							,'link' => array('modules'=>$this->_modules
											,'controller'=>$this->name
											,'action'=>'delete'
											,'id'=>$id
											)
								 		);
		}
		
		$sidebar->addList('This Resource',$sidebarlist);
			
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName('resources');
	}

}
?>
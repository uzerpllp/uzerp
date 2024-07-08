<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class MfoutsideoperationsController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new MFOutsideOperation();
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$id = $this->_data['stitem_id'];
		$transaction = new STItem;
		$transaction->load($id);
		$this->view->set('transaction',$transaction);

		$outside_ops = new MFOutsideOperationCollection($this->_templateobject);
		$sh = new SearchHandler($outside_ops, false);
		$cc = new ConstraintChain;
		$cc->add(new Constraint('stitem_id', '=', $id));
		$db = DB::Instance();
		$date = Constraint::TODAY;
		$between = $date.' BETWEEN '.$db->IfNull('start_date', $date).' AND '.$db->IfNull('end_date', $date);
		$cc->add(new Constraint('', '', '('.$between.')'));
		$sh->addConstraintChain($cc);
		$sh->setOrderby('op_no');
		$outside_ops->load($sh);
		$this->view->set('outside_ops',$outside_ops);

		//$this->view->set('linkfield','id');
		//$this->view->set('linkvaluefield','id');
		$this->view->set('clickaction','edit');
		//$this->view->set('clickcontroller','MFOutsideOperations');
		$this->view->set('no_ordering',true);

		$sidebar=new SidebarController($this->view);
		$sidebar->addList('Show',
			array(
				'allItems' => array('tag' => 'All Items'
								   ,'link' => array_merge($this->_modules
													     ,array('controller'=>'STItems'
															   ,'action'=>'index'
															   )
														 )
									),
				'thisItem' => array('tag' => 'Item Detail'
								   ,'link' => array_merge($this->_modules
													     ,array('controller'=>'STItems'
															   ,'action'=>'view'
															   ,'id'=>$id
															   )
														 )
									),
				'addoperation' => array('tag' => 'Add Outside Operation'
									   ,'link' => array_merge($this->_modules
															 ,array('controller'=>$this->name
 																   ,'action'=>'new'
																   ,'stitem_id'=>$id
																   )
															 )
                					)
				)
			);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}


    /**
     * Create/edit Outside Operation
     */
    public function _new()
    {
        parent::_new();
        $mfoperation = new MFOutsideOperation();
        $mfoperation->load($this->_data['id']);
        $stitem = new STItem();

        // Identify and load the associated stock item from attributes or the loaded operation
        if ($mfoperation->isLoaded()) {
            $this->_data['stitem_id'] = $mfoperation->stitem_id;
        }

        if (empty($this->_data['stitem_id'])) {
            $stitems = $stitem->getAll();
            $this->view->set('stitems', $stitems);
            $stitem_id = key($stitems);
        } else {
            $stitem_id = $this->_data['stitem_id'];
        }
        $stitem->load($stitem_id);

        if (! empty($this->_data['stitem_id'])) {
            $this->view->set('page_title', $this->getPageName() . ' for ' . $stitem->getIdentifierValue());
        }
        $this->view->set('stitem', $stitem);

        // Load the current operations and pass to the view for display
        $outside_ops = new MFOutsideOperationCollection();
        $outside_ops->orderby = 'op_no';
        $sh = $this->setSearchHandler($outside_ops);

        $cc = new ConstraintChain();
        $cc->add(new Constraint('stitem_id', '=', $stitem_id));
        $db = DB::Instance();
        $date = Constraint::TODAY;
        $between = $date . ' BETWEEN ' . $db->IfNull('start_date', $date) . ' AND ' . $db->IfNull('end_date', $date);
        $cc->add(new Constraint('', '', '(' . $between . ')'));
        $sh->addConstraintChain($cc);
        $outside_ops->load($sh);
        $this->view->set('mfoutsideoperations', $outside_ops);

        // Set the cancel link for the form
        $cancel_url = link_to(array_merge($this->_modules, [
            'controller' => $this->name,
            'action' => 'index',
            'stitem_id' => $this->_data['stitem_id']
        ]), false, false);
        $this->view->set('cancel_link', $cancel_url);
    }

	public function delete($modelName = null){
		$flash = Flash::Instance();
		$errors = array();
		$data = array(
			'id' => $this->_data['id'],
			'end_date' => date(DATE_FORMAT)
		);
		$outside_op = MFOutsideOperation::Factory($data, $errors, 'MFOutsideOperation');
		if ((count($errors) > 0) || (!$outside_op->save())) {
			$errors[] = 'Could not delete outside operation';
		}
		if (count($errors) == 0) {
			$stitem = new STItem;
			if ($stitem->load($outside_op->stitem_id)) {
				//$stitem->calcLatestCost();
				if (!$stitem->rollUp(STItem::ROLL_UP_MAX_LEVEL)) {
					$errors[] = 'Could not roll-up latest costs';
					$db->FailTrans();
				}
			} else {
				$errors[] = 'Could not roll-up latest costs';
				$db->FailTrans();
			}
		}
		if (count($errors) == 0) {
			$flash->addMessage('Outside operation deleted');
			sendTo('STItems'
					,'viewoutside_operations'
					,$this->_modules
					,array('id' => $this->_data['stitem_id']));
		} else {
			$flash->addErrors($errors);
			sendBack();
		}
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		$db = DB::Instance();
		$db->StartTrans();
		$errors = array();
		if (parent::save('MFOutsideOperation')) {
			$stitem = new STItem;
			if ($stitem->load($this->saved_model->stitem_id)) {
				$old_cost = $stitem->latest_osc;
				$stitem->calcLatestCost();
				$new_cost = $stitem->latest_osc;
				if (bccomp($old_cost, $new_cost, $stitem->cost_decimals) != 0) {
					if (($stitem->saveCosts()) && (STCost::saveItemCost($stitem))) {
						if (!$stitem->rollUp(STItem::ROLL_UP_MAX_LEVEL)) {
							$errors[] = 'Could not roll-up latest costs';
							$db->FailTrans();
						}
					} else {
						$errors[] = 'Could not save latest costs';
						$db->FailTrans();
					}
				}
			} else {
				$errors[] = 'Could not save latest costs';
				$db->FailTrans();
			}
		} else {
			$errors[] = 'Could not save outside operation';
			$db->FailTrans();
		}
		$db->CompleteTrans();
		if (count($errors) == 0 && isset($this->_data['saveadd'])) {
		    sendTo($this->name
		        ,'new'
		        ,$this->_modules
		        ,array('stitem_id' => $this->_data['MFOutsideOperation']['stitem_id']));
		} elseif (count($errors) == 0) {
		        sendTo($this->name
		            ,'index'
		            ,$this->_modules
		            ,array('stitem_id' => $this->_data[$this->modeltype]['stitem_id']));
		} else {
			$flash->addErrors($errors);
			$this->_data['stitem_id']= $this->_data['MFOutsideOperation']['stitem_id'];
			$this->refresh();
		}
	}

	public function view(){
		$id = $this->_data['id'];
		$transaction=&$this->_uses['MFOutsideOperation'];
		$transaction->load($id);
		$this->view->set('transaction',$transaction);

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'stores' => array('tag' => 'Show Item Detail'
								 ,'link' => array_merge($this->_modules
													   ,array('controller'=>'STItems'
															 ,'action'=>'viewoutside_operations'
															 ,'id'=>$transaction->stitem_id
															 )
														)
								 ),
				'new'=>array('tag'=>'New Outside Operation'
							,'link'=>array_merge($this->_modules
												,array('controller'=>$this->name
													  ,'action'=>'new'
													  ,'stitem_id'=>$transaction->stitem_id
													  )
												)
							),
				'edit'=>array('tag'=>'Edit Outside Operation'
							 ,'link'=>array_merge($this->_modules
												 ,array('controller'=>$this->name
													   ,'action'=>'edit'
													   ,'id'=>$id
													   ,'stitem_id'=>$transaction->stitem_id
													   )
												 )
							 ),
				'delete'=>array('tag'=>'Delete Outside Operation'
							   ,'link'=>array_merge($this->_modules
												   ,array('controller'=>$this->name
														 ,'action'=>'delete'
														 ,'id'=>$id
														 ,'stitem_id'=>$transaction->stitem_id
														 )
													)
								)
				)
			);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName('outsideoperations');
	}

}
?>

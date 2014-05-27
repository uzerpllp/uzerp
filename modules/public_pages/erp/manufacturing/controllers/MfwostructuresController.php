<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MfwostructuresController extends Controller {

	protected $version='$Revision: 1.13 $';
	
	protected $_templateobject;

	protected $related = array('_mfworkorder'=>array('clickaction'=>'edit'));
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new MFWOStructure();
		$this->uses($this->_templateobject);
		
	}

	public function _new() {
		
		parent::_new();
		
// work_order_id is set for adding a new structure
// otherwise id is set, which is the id of the structure element
		$uom_list=array();
		$wostructure=$this->_uses[$this->modeltype];
		if (isset($this->_data['work_order_id'])) {
			$work_order_id=$this->_data['work_order_id'];
			$stitem_id=key($items_list);
			$uom_id='';
		} elseif ($wostructure->isLoaded()) {
			$work_order_id=$wostructure->work_order_id;
			$stitem_id=$wostructure->ststructure_id;
			$uom_id=$wostructure->uom_id;
		}
		if (isset($this->_data['ststructure_id'])) {
			$stitem_id=$this->_data['ststructure_id'];
		}
		$stitem=new STItem();
		$stitem->load($stitem_id);
		
		$items_list = STItem::nonObsoleteItems();
		if (!isset($items_list[$stitem_id])) {
			$items_list+=array($stitem->id=>$stitem->getIdentifierValue().'(Obsolete)');
		}
		$this->view->set('ststructures',$items_list);
	
		$uom_id=(empty($uom_id))?$stitem->uom_id:$uom_id;
		$this->view->set('uom_id',$uom_id);
		$uom_list=$stitem->getUomList();
		
		if (isset($work_order_id)) {
			$this->view->set('elements',self::showParts($work_order_id));
			$this->view->set('no_ordering',true);
		}

		$this->view->set('uom_list',$uom_list);
	}
	
	public function save() {
		$flash=Flash::Instance();
		if(parent::save('MFWOStructure')) {
			sendTo('Mfworkorders'
					,'reviewMaterials'
					,$this->_modules
					,array('id' => $this->_data['MFWOStructure']['work_order_id']));
		}
		$this->_data['work_order_id']=$this->_data['MFWOStructure']['work_order_id'];
		$this->_data['ststructure_id']=$this->_data['MFWOStructure']['ststructure_id'];
		$this->refresh();

	}

	public function showParts($id) {
		$elements = new MFWOStructureCollection(new MFWOStructure);
		$sh = new SearchHandler($elements, false);
		$sh->addConstraint(new Constraint('work_order_id','=', $id));
		$sh->setOrderBy('line_no');
		$sh->extractOrdering();
		$elements->load($sh);
		return $elements;
	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName('workorders_product_structures');
	}

}
?>

<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DatamappingdetailsController extends EdiController {

	protected $_templateobject;
	protected $version='$Revision: 1.11 $';
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new DataMappingDetail();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){

		$mapdetails=new DataMappingDetailCollection($this->_templateobject);
		
		$sh=$this->setSearchHandler($mapdetails);
		
		$this->view->set('parentmodel', $this->_templateobject);
		if (isset($this->_data['parent_id'])) {
			$sh->addConstraint(new Constraint('parent_id', '=', $this->_data['parent_id']));
			$parent_detail=new DataMappingDetail();
			$parent_detail->load($this->_data['parent_id']);
			$this->view->set('parent', $parent_detail->displayValue());
			$this->view->set('parent_type', $parent_detail->data_map_rule->name);
		}
		if (isset($this->_data['data_mapping_rule_id'])) {
			$sh->addConstraint(new Constraint('data_mapping_rule_id', '=', $this->_data['data_mapping_rule_id']));
			$datamapping=new DataMappingRule();
			$datamapping->load($this->_data['data_mapping_rule_id']);
			$this->view->set('datamapping', $datamapping);
		}
		
		$this->view->set('clickaction', 'view');
		parent::index($mapdetails, $sh);

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'edi'
								 ,'controller'=>$this->name
								 ,'action'=>'new'
									   ),
					'tag'=>'new data mapping'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('page_title',$this->getPageName());
		
}	

	public function _new () {
		
		$datamapdetail=$this->_uses[$this->modeltype];
		
		if ($datamapdetail->isLoaded()) {
			$this->_data['data_mapping_rule_id']=$datamapdetail->data_mapping_rule_id;
			$this->_data['parent_id']=$datamapdetail->parent_id;
			$datamappingrule=$datamapdetail->data_map_rule;
		} elseif (isset($this->_data['data_mapping_rule_id'])) {
			$datamappingrule=new DataMappingRule();
			$datamappingrule->load($this->_data['data_mapping_rule_id']);
		}
		if (!$this->CheckParams('data_mapping_rule_id')) {
			sendBack();
		}
		
		parent::_new();

		$cc=new ConstraintChain();
		
		$datamap=$datamappingrule->data_map;
			
		$options=array();
		if (!empty($this->_data['parent_id'])) {
			// get the parent mapping detail
			$parent_detail=new DataMappingDetail();
			$parent_detail->load($this->_data['parent_id']);
			// get the parent mapping
			$parent_mapping=$parent_detail->data_map_rule->data_map;
			
			$parent_id=$parent_detail->internal_code;
			
// Rules need simplifying and/or explaining
// perhaps move these to the DataMapping model
			$x=$datamap->getModel();
			$hasmany=$x->getHasMany();
			if (isset($hasmany[$datamap->internal_attribute])) {
				$x->load($parent_detail->internal_code);
				foreach($x->{$datamap->internal_attribute} as $detail) {
					$options[$detail->{$detail->idField}] = $detail->{$detail->getIdentifier()};
				}
			} elseif ($parent_mapping->isHasOne()) {
				$hasone=$parent_mapping->getHasOne();
				$x=new $hasone['model'];
				$hasmany=$x->getHasMany();
				if (isset($hasmany[$datamap->internal_attribute])) {
					$x->load($parent_detail->internal_code);
					foreach($x->{$datamap->internal_attribute} as $detail) {
						$options[$detail->{$detail->idField}] = $detail->{$detail->getIdentifier()};
					}
				}
			} else {
				$belongsto=$x->belongsTo;
				foreach ($belongsto as $parent) {
					if ($parent['model']==$parent_mapping->internal_type) {
						$cc->add(new Constraint($parent['field'], '=', $parent_id));
						break;
					}
				}
			}
			// If no constraints found from fk definitions and the parent class is the
			// same as the child class, then the parent is the child i.e. data specific
			// to the parent
			if (empty($cc->constraints) && $parent_mapping->internal_type==get_class($x)) {
				$cc->add(new Constraint($x->idField, '=', $parent_id));
			}
		} 
//		elseif (!is_null($datamappingrule->parent_id)) {
			// Get the list of parent options
			// from the parent mapping details via the parent mapping rule
			$parentmappingrule= new datamappingrule();
			$parentmappingrule->load($datamappingrule->parent_id);
			$parent_options=array();
			foreach ($parentmappingrule->data_translations as $parent_detail) {
				$parent_options[$parent_detail->id]=$parent_detail->displayValue();
			}
			$this->view->set('parent_options', $parent_options);
			$this->view->set('parent_label', $datamappingrule->parent);
//		}
		if (is_null($datamap->internal_type)) {
			$errors[]='No Internal Type defined for Data Mapping '.$datamappingrule->name;
		} else {
			if (empty($options)) {
				$options=$datamap->getDataOptions($datamap->getModel(), $cc);
			}

			$this->view->set('data_mapping_rule_id', $this->_data['data_mapping_rule_id']);
		
			if (!$datamap->isLoaded()) {
				$errors[]='Invalid Data Type';
			} else {
				if (!is_null($datamap->internal_type)) {
					$model=$datamap->getModel();
					if (!$model) {
						$errors[]='Data Mapping for Internal Data Type is invalid';
					} else {
						$this->view->set('model', $model);
						$this->view->set('internal_codes', $options);
						$this->view->set('internal_name', $datamappingrule->name);
					}
				}
			}
		}
		
		$datatranslations=new DataMappingDetailCollection(new DataMappingDetail);
		$sh=$this->setSearchHandler($datatranslations);
		$sh->addConstraint(new Constraint('data_mapping_rule_id', '=', $this->_data['data_mapping_rule_id']));
		if (isset($this->_data['parent_id'])) {
			$sh->addConstraint(new Constraint('parent_id', '=', $this->_data['parent_id']));
		}
		parent::index($datatranslations, $sh);
		$this->view->set('clickaction', 'view');
	}
	
	public function view () {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$mapping_details = $this->_uses[$this->modeltype];
		
		$this->view->set('clickaction','view');
		$this->view->set('mapping_details',$mapping_details);

		$mapping_rule=$mapping_details->data_map_rule;
		$this->view->set('mapping_rule',$mapping_rule);
		
		$mapping=$mapping_rule->data_map;
		$this->view->set('mapping', $mapping);

		$parent_mapping_rule=$mapping_details->parent_detail->data_map_rule;
		$this->view->set('parent_mapping_rule',$parent_mapping_rule);
		
		$parent_mapping=$parent_mapping_rule->data_map;
		$this->view->set('parent_mapping', $parent_mapping);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist=array();
		
		$sidebarlist['all']=array('tag' => 'View all rules'
								  ,'link'=>array('modules'=>$this->_modules
												,'controller'=>'datamappingrules'
												,'action'=>'index'
												)
									);
		
		$sidebar->addList('actions', $sidebarlist);
									
		$sidebarlist=array();
		
		$sidebarlist['view']=array('tag' => 'View '.$mapping_rule->name.' translation'
								  ,'link'=>array('modules'=>$this->_modules
												,'controller'=>$this->name
												,'action'=>'view'
												,'id'=>$mapping_details->id
												)
									);

		$sidebarlist['edit']=array('tag' => 'Edit '.$mapping_rule->name.' translation'
								  ,'link'=>array('modules'=>$this->_modules
												,'controller'=>$this->name
												,'action'=>'edit'
												,'id'=>$mapping_details->id
												)
									);
									
		$sidebarlist['delete']=array('tag' => 'Delete '.$mapping_rule->name.' translation'
									,'link'=>array('modules'=>$this->_modules
												  ,'controller'=>$this->name
												  ,'action'=>'delete'
												  ,'id'=>$mapping_details->id
												  )
									);

		$sidebar->addList('actions for '.$mapping_details->external_code, $sidebarlist);
	
		$sidebarlist=array();
		
		foreach ($mapping_rule->getChildrenAsDOC() as $child_map) {
			$sidebarlist['view '.$child_map->name]=array(
					'tag'=>$child_map->name.' translation',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 ,'data_mapping_rule_id'=>$child_map->id
								 ,'parent_id'=>$mapping_details->id
								 ),
					'new'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 ,'data_mapping_rule_id'=>$child_map->id
								 ,'parent_id'=>$mapping_details->id
								 )
					);
			
		}
		
		if (!empty($sidebarlist)) {
			$sidebar->addList('related_items', $sidebarlist);
		}
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	

		$this->view->set('page_title',$this->getPageName());
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {

		if (!$this->CheckParams($this->modeltype)) {
			sendBack();
		}
		
		$flash=Flash::Instance();
		$errors=array();
		
		if(controller::save($this->modeltype, '', $errors)) {
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		
		$flash->addErrors($errors);
		$this->_data['data_mapping_rule_id']=$this->_data[$this->modeltype]['data_mapping_rule_id'];
		
		$this->refresh();
		
	}

	public function viewDataMappingRule () {
		self::index();
		$this->setTemplateName('index');

		$this->view->set('page_title',$this->getPageName('View Data Translations'));
	}
	

// Protected Functions
	protected function getPageName($base='',$action='') {
		return parent::getPageName((empty($base)?'Data Mapping Details':$base),$action);
	}

}
?>
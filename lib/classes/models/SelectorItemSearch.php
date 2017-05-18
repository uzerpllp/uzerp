<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SelectorItemSearch extends BaseSearch {

	protected $version='$Revision: 1.3 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null, $params=array()) {

		$search = new SelectorItemSearch($defaults);

// Search by Product
		$config=SelectorCollection::getTypeDetails($params['type']);
		$search->addSearchField(
			'parent_id',
			implode('/', $config['itemFields']),
			'treesearch',
			-1,
			'basic'
			);
		
		if (empty($search_data)) {
			$search_data=null;
		}
		$search->setSearchData($search_data,$errors,'selectProduct');

// Populate the parent_id field using the last selected value
// it will be -1 if no previous selected value
		$parent_id=$search->getValue('parent_id');
		$cc=new ConstraintChain();
		if($parent_id!='-1') {
			$cc->add(new Constraint('parent_id','=',$parent_id));
		} else {
			$cc->add(new Constraint('parent_id','IS','NULL'));
		}
		$model=new DataObject('so_product_selector');
		$options=array($parent_id=>'Select an option');
		$options+=$model->getAll($cc);
		$search->setOptions('parent_id',$options);
		if($parent_id!='-1') {
			$data=array();
			$search->setBreadcrumbs('parent_id',$model,'parent_id',$parent_id,'name','description',$data);
		}
		
		return $search;
		
	}
		
	public static function usedBy($search_data=null, &$errors=array(), $defaults=null, $params=array()) {

		$search = new SelectorItemSearch($defaults);

		$search->addSearchField(
			'target_id',
			'Target',
			'hidden',
			'',
			'hidden'
		);
		
// Search by Product
		$config=SelectorCollection::getTypeDetails($params['type']);
		$search->addSearchField(
			'parent_id',
			implode('/', $config['itemFields']),
			'treesearch',
			-1,
			'basic'
			);
		
		if (empty($search_data)) {
			$search_data=null;
		}
		$search->setSearchData($search_data,$errors,'selectProduct');

// Populate the parent_id field using the last selected value
// it will be -1 if no previous selected value
		$parent_id=$search->getValue('parent_id');
		$cc=new ConstraintChain();
		if($parent_id!='-1') {
			$cc->add(new Constraint('parent_id','=',$parent_id));
		} else {
			$cc->add(new Constraint('parent_id','IS','NULL'));
		}
		$model=new DataObject('so_product_selector');
		$options=array($parent_id=>'Select an option');
		$options+=$model->getAll($cc);
		$search->setOptions('parent_id',$options);
		if($parent_id!='-1') {
			$data=array('target_id'=>$search->getValue('target_id'));
			$search->setBreadcrumbs('parent_id',$model,'parent_id',$parent_id,'name','description',$data);
		}
		
		return $search;
	}
		
	public function toConstraintChain() {
		$cc = new ConstraintChain();
		if($this->cleared) {
			return $cc;
		}
		debug('BaseSearch::toConstraintChain Fields: '.print_r($this->fields, true));
		
		foreach($this->fields as $group) {
			foreach($group as $field=>$searchField) {
				if ($field=='slmaster_id') {
					$cc1=new ConstraintChain();
					if ($searchField->getValue()==-1 || $searchField->getValue()>0) {
						$cc1->add(new Constraint('slmaster_id', 'is', 'NULL'));
					}
					$c = $searchField->toConstraint();
					if($c!==false) {
						$cc1->add($c, 'OR');
					}
					$cc->add($cc1);
				} elseif ($field!='parent_id' && $field!='target_id' && $field!='search_id') {
					$c = $searchField->toConstraint();
					if($c!==false) {
						$cc->add($c);
					}
				}
			}
		}
		debug('BaseSearch::toConstraintChain Constraints: '.print_r($cc, true));
		return $cc;
	}
	
}
?>

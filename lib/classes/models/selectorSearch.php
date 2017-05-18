<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class selectorSearch extends BaseSearch {

	protected $version='$Revision: 1.7 $';
	
	public static function itemSearch($do, $search_data=null, &$errors=array(), $defaults=null)
	{
		
		$search = new selectorSearch($defaults);

		// Search by Parent Id
		$search->addSearchField(
			'parent_id',
			'',
			'hidden',
			-1,
			'hidden'
		);
		
		// Search by Name
		$search->addSearchField(
			'name',
			'Name Contains',
			'contains',
			'',
			'basic'
		);
		
		$search->setSearchData($search_data,$errors);
		
		// fire the search data away
		return $search;
	
	}

	public static function useDefault($do, $search_data=null, &$errors=array(), $defaults=null)
	{

		$search = new selectorSearch($defaults);

		// Search by Item
		$search->addSearchField(
			'parent_id',
			implode('/', $do->getDisplayFieldNames()),
			'treesearch',
			-1,
			'basic'
		);
		
		// Search by Name
		$search->addSearchField(
			'name',
			'Name Contains',
			'contains',
			'',
			'basic'
		);
		
		$search->setSearchData($search_data,$errors);
		
		// set the possible options
		$parent_id=$search->getValue('parent_id');
		$cc=new ConstraintChain();
		if($parent_id!='-1') {
			$cc->add(new Constraint('parent_id','=',$parent_id));
		} else {
			$cc->add(new Constraint('parent_id','IS','NULL'));
		}
		
		// set the default search options
		$options=array($parent_id=>'Select an option');
		$options+=$do->getAll($cc);
		$search->setOptions('parent_id',$options);
		
		if($parent_id!='-1') {
			$search->setBreadcrumbs('parent_id',$do,'parent_id',$parent_id,'name','description');
		}
		
		// fire the search data away
		return $search;
	
	}
	
	public static function Associations($do, $search_data=null, &$errors=array(), $defaults=null) 
	{

		$search = new selectorSearch($defaults);

		// Search by Item
		$search->addSearchField(
			'parent_id',
			implode('/', $do->getDisplayFieldNames()),
			'treesearch',
			-1,
			'basic'
		);
		
		// Search by Name
		$search->addSearchField(
			'description',
			'Description Contains',
			'contains',
			'',
			'basic'
		);
		
		$search->setSearchData($search_data,$errors);
		
		// set the possible options
		$parent_id=$search->getValue('parent_id');
		$cc=new ConstraintChain();
		if($parent_id!='-1') {
			$cc->add(new Constraint('parent_id','=',$parent_id));
		} else {
			$cc->add(new Constraint('parent_id','IS','NULL'));
		}
		
		// set the default search options
		$options=array($parent_id=>'Select an option');
		$options+=$do->getAll($cc);
		$search->setOptions('parent_id',$options);
		
		if($parent_id!='-1') {
			$search->setBreadcrumbs('parent_id',$do,'parent_id',$parent_id,'name','description');
		}
		
		// fire the search data away
		return $search;
	
	}
	
	public function toConstraintChain()
	{
		$cc = new ConstraintChain();
		if($this->cleared) {
			return $cc;
		}
		debug('BaseSearch::toConstraintChain Fields: '.print_r($this->fields, true));
		
		foreach($this->fields as $group) {
			foreach($group as $field=>$searchField) {
				if ($searchField->doConstraint()) {
					if ($field=='parent_id' && $searchField->getValue()==-1) {
						$cc->add(new Constraint('parent_id', 'is', 'NULL'));
					} else {
						$c = $searchField->toConstraint();
						if($c!==false) {
							$cc->add($c);
						}
					}
				}
			}
		}
		debug('BaseSearch::toConstraintChain Constraints: '.print_r($cc, true));

		return $cc;
	
	}

}
?>

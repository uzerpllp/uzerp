<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class poauthlimitsSearch extends BaseSearch
{

	protected $version = '$Revision: 1.8 $';
	
	protected $fields = array();
	
	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new poauthlimitsSearch($defaults);

// Search by Person
		$search->addSearchField(
			'username',
			'Person',
			'select',
			''
			);
		$people = DataObjectFactory::Factory('Usercompanyaccess');
		
		$people->idField		 = 'username';
		$people->identifierField = 'username';
		$people->orderby		 = 'username';
		
		$options = array('0'=>'All');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
		
		$peoplelist = $people->getAll($cc);
		$options += $peoplelist;
		$search->setOptions('username', $options);

// Search by GL Centre
		$search->addSearchField(
			'glcentre_id',
			'GL Centre',
			'select',
			0
			);
		$options = array('0'=>'All');
		
		$centres = DataObjectFactory::Factory('GLCentre');
		
		$centrelist = $centres->getAll();
		
		$options += $centrelist;
		$search->setOptions('glcentre_id',$options);
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}

// End of poauthlimitsSearch

<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class mfdeptsSearch extends BaseSearch
{

	protected $version='$Revision: 1.5 $';
	
	public static function useDefault(&$search_data=null, &$errors=array(), $defaults=null)
	{
		$search = new mfdeptsSearch($defaults);

// Search by Store Id
		$search->addSearchField(
			'id',
			'Dept',
			'select',
			'',
			'basic'
			);
		$dept = DataObjectFactory::Factory('MFDept');
		$depts = $dept->getAll();
		$search->setOptions('id', $depts);

		$search->setSearchData($search_data,$errors);
		return $search;
	}
		
}

// End of mfdeptsSearch

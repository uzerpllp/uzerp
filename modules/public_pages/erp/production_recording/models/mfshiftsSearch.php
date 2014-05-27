<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class mfshiftsSearch extends BaseSearch
{

	protected $version = '$Revision: 1.3 $';
	
	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		
		$search = new mfshiftsSearch($defaults);
		
		$mfshift = DataObjectFactory::Factory('MFShift');
		
// Search by Shift
		$search->addSearchField(
			'shift',
			'shift',
			'multi_select',
			array()
		);
		
		$search->setOptions('shift',$mfshift->getEnumOptions('shift'));
		
// Search by Shift Date
 		$search->addSearchField(
			'shift_date',
			'Required Between',
			'between',
			'',
			'advanced'
		);
		
// Search by Dept
		$search->addSearchField(
			'mf_dept_id',
			'Dept',
			'select',
			'',
			'advanced'
			);
		
		$depts = $mfshift->getAllDept();
		
		$options = array(''=>'All');
		
		$options = $options+$depts;
		
		$search->setOptions('mf_dept_id',$options);

// Search by Centre
/* This needs to be constrained by the selection of the dept code
 * 
		$search->addSearchField(
			'mf_centre_id',
			'Centre',
			'select',
			'',
			'advanced'
			);
		$centres=$mfshift->getAllCentres();
		$options=array(''=>'All');
		$options=$options+$centres;
		$search->setOptions('mf_dept_id',$options);
*/
				
		$search->setSearchData($search_data,$errors);
		
		return $search;
		
	}

			
}

// End of mfshiftsSearch

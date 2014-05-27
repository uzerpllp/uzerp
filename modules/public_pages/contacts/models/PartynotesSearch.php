<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PartynotesSearch extends BaseSearch
{

	protected $version = '$Revision: 1.4 $';
	
	protected $fields = array();
		
	public static function useDefault($search_data = null, &$errors, $defaults = null)
	{
		$search = new PartynotesSearch($defaults);
		
// Search by Title
		$search->addSearchField(
			'title',
			'title_contains',
			'contains'
		);
		
// Search by Note
		$search->addSearchField(
			'note',
			'note_contains',
			'contains'
		);
		
// Search by Note Type
		$search->addSearchField(
			'note_type',
			'note_type',
			'select'
		);
		$note		= DataObjectFactory::Factory('PartyNote');
		$options	= array_merge(array(''=>'All')
								 ,$note->getEnumOptions('note_type')
								 );
		$search->setOptions('note_type', $options);
		
// Search by owner
// Needs to get list of users who have created notes for this party_id
//		$search->addSearchField(
//			'owner',
//			'owner',
//			'select',
//			false,
//			'advanced'
//		);
		
// Search by alteredby
// Needs to get list of users who have altered notes for this party_id
//		$search->addSearchField(
//			'alteredby',
//			'altered_by',
//			'select',
//			false,
//			'advanced'
//		);
		
// Search by Created Date
		$search->addSearchField(
			'created',
			'created',
			'between',
			'',
			'advanced'
		);
		
// Search by Updated Date
		$search->addSearchField(
			'updated',
			'updated',
			'between',
			'',
			'advanced'
		);
		
// Party Id is hidden - set by context in defaults
		$search->addSearchField(
			'party_id',
			'party_id',
			'equal',
			'',
			'hidden'
		);

// Could set boolean - set for notes belonging to current user
//		$search->setOnValue('owner',EGS_USERNAME);

		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}

// End of PartynotesSearch

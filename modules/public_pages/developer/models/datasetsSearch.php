<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class datasetsSearch extends BaseSearch
{

	protected $version = '$Revision: 1.1 $';
	
	protected $fields = array();
	
	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new datasetsSearch($defaults);
		
		$search->setSearchData($search_data, $errors);

		return $search;
	
	}
	
	public function setSearchFields($dataset)
	{
		$field_types = array('character varying'	=> 'contains'
							,'boolean'				=> 'show'
							,'date'					=> 'betweenfields'
							,'datetime'				=> 'betweenfields'
							,'int4'					=> 'equal'
							,'int8'					=> 'equal'
							,'numeric'				=> 'equal');
		
		foreach ($dataset->fields as $field)
		{
			if ($field->searchable == 't')
			{
				if (!is_null($field->module_component_id))
				{
					$this->addSearchField($field->name, $field->name, 'select');
					$fk_model = DataObjectFactory::Factory($field->fk_link);
					$options = array(''=>'all');
					$options += $fk_model->getAll();
					$this->setOptions($field->name, $options);
				}
				else
				{
					$this->addSearchField($field->name, $field->name, $field_types[$field->type]);
				}
			}
		}
	}

}

// End of datasetsSearch

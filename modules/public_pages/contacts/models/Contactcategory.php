<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Contactcategory extends DataObject
{

	protected $version = '$Revision: 1.8 $';
	
	function __construct($tablename='contact_categories')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField='id';
		
// Define relationships
		$this->hasMany('CompanyInCategories', 'companies', 'category_id');
		
// Define field formats
		
// Define validation
		
// Define default values
		
// Define enumerated types
		
	}
	
	function getCompanyCategories()
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('company', 'IS', TRUE));
		
		return $this->getAll($cc);

	}
	
	function getPersonCategories()
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('person', 'IS', TRUE));
		
		return $this->getAll($cc);

	}
	
	function getCategoriesByName ($name='')
	{
		$db=DB::Instance();
		
		if (!is_array($name))
		{
			$name=array($name);	
		}
		
		foreach ($name as &$value)
		{
			$value=$db->qstr($value);
		}
		
		$cc = new ConstraintChain();
		
		if (!empty($name))
		{
			$cc->add(new Constraint('name','in','('.implode(',', $name).')'));
		}	
		
		return $this->getAll($cc);
		
	}

}

// End of Contactcategory

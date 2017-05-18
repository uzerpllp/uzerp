<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanyInCategoriesCollection extends DataObjectCollection
{
			
	protected $version = '$Revision: 1.6 $';
	
	function __construct($do = 'CompanyInCategories', $tablename = 'companies_in_categories_overview')
	{
		parent::__construct($do, $tablename);
			
	}
	
	function getCategories($company_id)
	{
		$sh = new SearchHandler($this, false);
		
		$sh->addConstraint(new Constraint('company_id', '=', $company_id));
		
		$sh->setOrderby('category');
		
		$this->load($sh);
						
	}
	
	function getCompanies($category_id)
	{
		$sh = new SearchHandler($this, false);
		
		if (is_array($category_id))
		{
			$sh->addConstraint(new Constraint('category_id', 'in', '('.implode(',', $category_id).')'));
		}
		else
		{
			$sh->addConstraint(new Constraint('category_id', '=', $category_id));
		}
		
		$sh->setOrderby('company');
		
		$this->load($sh);
					
	}	
		
}

// End of CompanyInCategoriesCollection

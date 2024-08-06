<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class LedgerCategory extends DataObject
{

	protected $version = '$Revision: 1.6 $';

	protected $defaultDisplayFields = array('ledger_type'
										   ,'category'
										   );

	private $types;
	private $in_types;

	function __construct($tablename = 'ledger_categories')
	{

// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->identifierField = 'category_id';
		$this->setTitle('Ledger Category');

// Define relationships
		$this->belongsTo('ContactCategory', 'category_id', 'category');
		$this->hasMany('CompanyInCategories', 'category_id', 'company_categories');

// Define field formats

// Define system defaults

// Define enumerated types
		$this->setEnum('ledger_type'
			,array('HR'=>'HR'
				  ,'PL'=>'Purchase Ledger'
				  ,'SL'=>'Sales Ledger'
				)
		);

// Registered interface types
		$this->types = array('employee'=>array('category_type'=>'HR'
												,'module'=>'hr'
												,'controller'=>'employees')
							,'plsupplier'=>array('category_type'=>'PL'
												,'module'=>'purchase_ledger'
												,'controller'=>'plsuppliers')
							,'slcustomer'=>array('category_type'=>'SL'
												,'module'=>'sales_ledger'
												,'controller'=>'slcustomers'));

		$this->in_types = array('company'=>'company_id'
							   ,'person'=>'person_id');

	}

	/*
	 * checkCompanyUsage
	 * 
	 * Checks if the company is linked to any of the models identified
	 * as 'Company' categories and returns the list of the categories
	 * as an array:-
	 * 
	 * array([ledger_type]
	 *             array([exists] (true if company linked to ledger type)
	 *                   array([categories])
	 *                  )
	 *      )
	 * 
	 * @param	$_company_id	integer	The unique identifier of the company
	 * 
	 */
	public function checkCompanyUsage($_company_id)
	{
		$cc = new ConstraintChain();

		$cc->add(new Constraint('company', 'IS', TRUE));

		return $this->checkUsage('company_id', $_company_id, $cc);

	}

	/*
	 * checkPersonUsage
	 * 
	 * Checks if the person is linked to any of the models identified
	 * as 'Person' categories and returns the list of the categories
	 * as an array:-
	 * 
	 * array([ledger_type]
	 *             array([exists] (true if person linked to ledger type)
	 *                   array([categories])
	 *                  )
	 *      )
	 * 
	 * @param	$_person_id	integer	The unique identifier of the person
	 * 
	 */
	public function checkPersonUsage($_person_id)
	{
		$cc = new ConstraintChain();

		$cc->add(new Constraint('person', 'IS', TRUE));

		return $this->checkUsage('person_id', $_person_id, $cc);

	}

	public function getCategoryByType($type = '')
	{

		$cc = new ConstraintChain();

		if (!empty($type))
		{
			$cc->add(new Constraint('ledger_type', '=', $type['category_type']));
		}

		return $this->getAll($cc);

	}

	public function getCategoriesByModel($model_name = '')
	{

		if (isset($this->types[strtolower((string) $model_name)]))
		{
			return $this->getCategoryByType($this->types[strtolower((string) $model_name)]);
		}

		return array();

	}

	public function getCompanyTypes ($categories)
	{
		$cc = new ConstraintChain();

		$cc->add(new Constraint('company', 'is', TRUE));

		return $this->getTypes($categories, $cc);

	}

	public function getPersonTypes ($categories)
	{
		$cc = new ConstraintChain();

		$cc->add(new Constraint('person', 'is', TRUE));

		return $this->getTypes($categories, $cc);

	}

	public function getTypes ($categories, $cc = null)
	{
		if (!($cc instanceOf ConstraintChain))
		{
			$cc = new ConstraintChain();
		}

		if (!empty($categories))
		{
			$cc->add(new Constraint('category_id', 'in', '(' . implode(',', $categories) . ')'));
		}

		$this->idField = 'ledger_type';

		$category_types = $this->getAll($cc, TRUE, TRUE);

		$model = array();

		foreach ($this->types as $model_name=>$model_detail)
		{
			if (isset($category_types[$model_detail['category_type']]))
			{
				$model[$model_name] = $model_detail;
			}
		}

		return $model;

	}

	public function getUnassignedCompanies(DataObject $do)
	{

		$cc=new ConstraintChain();
		$cc->add(new Constraint('is_lead', 'is', FALSE));
		$cc->add(new Constraint('date_inactive', 'is', 'NULL'));

		// return the list of companies in the Contact Categories list
		return $this->getUnassignedList($do, 'company', $cc);

	}

	public function getUnassignedLeads(DataObject $do)
	{

		$cc=new ConstraintChain();
		$cc->add(new Constraint('is_lead', 'is', TRUE));

		// return the list of companies in the Contact Categories list
		return $this->getUnassignedList($do, 'company', $cc);

	}

	public function getUnassignedPeople(DataObject $do)
	{

		// return the list of unassigned people
		return $this->getUnassignedList($do, 'person');

	}

	/*
	 * Private Functions
	 */

	/*
	 * checkUsage
	 * 
	 * Checks if the person is linked to any of the models identified
	 * as 'Person' categories and returns the list of the categories
	 * as an array:-
	 * 
	 * array([ledger_type]
	 *             array([exists] (true if person linked to ledger type)
	 *                   array([categories])
	 *                  )
	 *      )
	 * 
	 * @param	$_id_field	string	The name of the field in the model
	 * @param	$_id		string	The unique id to use to load the model
	 * @param	$cc	constraintChain	An optional constraint to use for getting the categories
	 * 
	 */
	private function checkUsage($_id_field, $_id, $cc = null)
	{

		if (!($cc instanceOf ConstraintChain))
		{
			$cc = new ConstraintChain();
		}

		$this->idField			= 'category_id';
		$this->identifierField	= 'ledger_type';

		$categories = $this->getAll($cc, TRUE, TRUE);

		$type = array();

		foreach ($categories as $category_id=>$ledger_type)
		{
			// create distinct list of category types
			$type[$ledger_type]['exists'] = FALSE;
			$type[$ledger_type]['categories'][$category_id] = $category_id;
		}

		if (!empty($type))
		{
			// One or more category types exist for type company
			// so find the matching category type in $this->types
			// and check if the associated model for the supplied company_id exists 
			foreach ($this->types as $model_name=>$model_details)
			{
				if (isset($type[$model_details['category_type']]))
				{
					$do = DataObjectFactory::Factory($model_name);

					if ($do->isField($_id_field))
					{
						$do->loadBy($_id_field, $_id);
						if ($do->isLoaded())
						{
							$type[$model_details['category_type']]['exists'] = TRUE;
						}
					}
				}
			}

		}

		return $type;

	}

	private function getUnassignedList(DataObject $do, $type, $cc = null)
	{
		// Is the DataObject registered?
		if (!(key_exists(strtolower(get_class($do)), $this->types)))
		{
			return false;			
		}

		// Is the category type registered?
		if (!(key_exists(strtolower((string) $type), $this->in_types)))
		{
			return false;			
		}

		$fieldname = $this->in_types[$type];

		// Is the category type fieldname valid for the DataObject?
		if (!$do->isField($fieldname))
		{
			return false;
		}

		$incategories	= DataObjectFactory::Factory($type.'InCategories');

// is there some way of attaching a sub-query object to the CompanyInCategories object?
// because the problem with defining the sub-query constraint here is that the correlated
// constraint needs to reference the parent table/view - would be better if this could be dynamic
		$subquery='select 1 from '.$do->getTableName().
		          " where $fieldname=".$incategories->getViewName().".$fieldname";

		if (!($cc instanceOf ConstraintChain))
		{
			$cc = new ConstraintChain();
		}

		$cc->add(new Constraint('','not exists','('.$subquery.')'));

		// Get the Contact Categories for this interface
		$contactcategories	= $this->getCategoryByType($this->types[strtolower(get_class($do))]);

		// return the list of unassigned items in the Contact Categories list
		return $incategories->getCompanyID($contactcategories, $cc);

	}

}

// End of LedgerCategory

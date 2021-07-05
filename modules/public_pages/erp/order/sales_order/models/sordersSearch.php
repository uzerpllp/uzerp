<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class sordersSearch extends BaseSearch
{

	protected $version = '$Revision: 1.29 $';
	
	protected $fields = array();
	
	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new sordersSearch($defaults);

		$sorder = DataObjectFactory::Factory('SOrder');
	
// Search by Customer
		$search->addSearchField(
			'slmaster_id',
			'Customer',
			'select',
			0,
			'basic'
			);
		$customer	= DataObjectFactory::Factory('SLCustomer');
		$options	= array('0'=>'All');
		$customers	= $customer->getAll(null, false, true, '', '');
		$options	+=$customers;
		$search->setOptions('slmaster_id', $options);

// Search by Person
		$search->addSearchField(
			'person',
			'person',
			'contains',
			'',
			'advanced'
		);

// Search by Order Number
		$search->addSearchField(
			'order_number',
			'order_number',
			'equal-integer',
			'',
			'basic'
		);

		// Search by Customer Reference Number
		$search->addSearchField(
			'ext_reference',
			'customer_reference',
			'contains',
			'',
			'advanced'
		);

// Search by Order Date
		$search->addSearchField(
			'order_date',
			'order_date between',
			'between',
			'',
			'advanced'
		);

// Search by Due Date
		$search->addSearchField(
			'due_date',
			'due_date between',
			'between',
			'',
			'advanced'
		);
			
// Search by Transaction Type
		$search->addSearchField(
			'type',
			'type',
			'select',
			'',
			'advanced'
			);
		$options = array_merge(array(''=>'All')
							  ,$sorder->getEnumOptions('type')
							  );
		$search->setOptions('type', $options);

// Search by Status
		$search->addSearchField(
			'status',
			'status',
			'multi_select',
			'',
			'advanced'
			);
		$options = array_merge(array(''=>'All')
							  ,$sorder->getEnumOptions('status')
							  );
		$search->setOptions('status', $options);
		
		$search->setSearchData($search_data, $errors);
		return $search;
	}

	public static function selectProduct ($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new sordersSearch($defaults);

// Search by Customer
		$customer	= DataObjectFactory::Factory('SLCustomer');
		$customers	= $customer->getAll(null, false, true, '', '');
		$options	= array('NULL'=>'None');
		$options	+=$customers;
		if (!isset($search->defaults['slmaster_id']))
		{
			$search->defaults['slmaster_id'] = key($options);
		}
		$search->addSearchField(
			'slmaster_id',
			'Customer',
			'select',
			$search->defaults['slmaster_id'],
			'basic'
			);
		$search->setOptions('slmaster_id',$options);

// Search by Product Group
		$prod_group			= DataObjectFactory::Factory('STProductgroup');
		$prod_group_list	= $prod_group->getAll();
		$search->addSearchField(
			'prod_group_id',
			'Product Group',
			'select',
			'',
			'basic'
			);
		$options = array(''=>'None');
		$options +=$prod_group_list;
		$search->setOptions('prod_group_id', $options);

// Search by Description
		$search->addSearchField(
			'description',
			'description contains',
			'contains',
			'',
			'basic'
			);

// Search by Product
		$config = SelectorCollection::getTypeDetails('sales_order');
		$search->addSearchField(
			'parent_id',
			implode('/', $config['itemFields']),
			'treesearch',
			-1,
			'basic'
			);
		
		if (empty($search_data))
		{
			$search_data = null;
		}
		$search->setSearchData($search_data, $errors, 'selectProduct');

// Populate the parent_id field using the last selected value
// it will be -1 if no previous selected value
		$parent_id = $search->getValue('parent_id');
		
		$cc = new ConstraintChain();
		
		if($parent_id!='-1')
		{
			$cc->add(new Constraint('parent_id','=',$parent_id));
		}
		else
		{
			$cc->add(new Constraint('parent_id','IS','NULL'));
		}
		
		$model = new DataObject('so_product_selector');
		
		$options = array($parent_id=>'Select an option');
		$options +=$model->getAll($cc);
		$search->setOptions('parent_id', $options);
		
		if($parent_id!='-1')
		{
			$data = array('slmaster_id'=>$search->getValue('slmaster_id'));
			$search->setBreadcrumbs('parent_id', $model, 'parent_id', $parent_id, 'name', 'description', $data);
		}
		
		return $search;

	}

	public function toConstraintChain()
	{
		$cc = new ConstraintChain();
		
		if($this->cleared)
		{
			return $cc;
		}
		
		debug('BaseSearch::toConstraintChain Fields: '.print_r($this->fields, true));
		
		foreach($this->fields as $group)
		{
			foreach($group as $field=>$searchField)
			{
				if ($field=='slmaster_id')
				{
					$cc1 = new ConstraintChain();
					
					if ($searchField->getValue()==-1 || $searchField->getValue()>0)
					{
						$cc1->add(new Constraint('slmaster_id', 'is', 'NULL'));
					}
					
					$c = $searchField->toConstraint();
					
					if($c!==false)
					{
						$cc1->add($c, 'OR');
					}
					$cc->add($cc1);
				}
				elseif ($field!='parent_id' && $field!='search_id')
				{
					$c = $searchField->toConstraint();
					
					if($c!==false)
					{
						$cc->add($c);
					}
				}
			}
		}
		debug('BaseSearch::toConstraintChain Constraints: '.print_r($cc, true));
		
		return $cc;
	}
	
}

// End of sordersSearch

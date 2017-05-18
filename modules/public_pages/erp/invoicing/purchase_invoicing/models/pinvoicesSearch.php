<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class pinvoicesSearch extends BaseSearch
{

	protected $version = '$Revision: 1.13 $';
	
	protected $fields = array();
	
	public static function useDefault($search_data = null, &$errors = array(), $defaults = null)
	{
		$search = new pinvoicesSearch($defaults);
		
		$invoice = DataObjectFactory::Factory('PInvoice');
		
// Search by Customer
		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select',
			0,
			'advanced'
			);
		$supplier = DataObjectFactory::Factory('PLSupplier');
		$options = array('0'=>'All');
		$suppliers = $supplier->getAll(null, false, true, '', '');
		$options += $suppliers;
		$search->setOptions('plmaster_id',$options);
		
// Search by Invoice Number
		$search->addSearchField(
			'invoice_number',
			'invoice_number',
			'equal',
			'',
			'advanced'
		);

// Search by Invoice Number
		$search->addSearchField(
			'ext_reference',
			'supplier_reference begins',
			'begins',
			'',
			'advanced'
		);

// Search by Purchase Order Number
		$search->addSearchField(
			'purchase_order_number',
			'PO Number',
			'equal',
			'',
			'advanced'
		);

// Search by Invoice Date
		$search->addSearchField(
			'invoice_date',
			'invoice_date_between',
			'between',
			'',
			'basic'
		);
		
// Search by Transaction Type
		$search->addSearchField(
			'transaction_type',
			'transaction_type',
			'select',
			'',
			'advanced'
			);
		$options = array_merge(array(''=>'All')
					  		  ,$invoice->getEnumOptions('transaction_type'));
		$search->setOptions('transaction_type',$options);
		
// Search by Status
		$search->addSearchField(
			'status',
			'status',
			'select',
			'',
			'advanced'
			);
		$options = array_merge(array(''=>'All')
					  		  ,$invoice->getEnumOptions('status'));
		$search->setOptions('status',$options);
		
		$search->setSearchData($search_data,$errors);
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
		
		// Certain hidden fields need to be excluded from the constraint
		foreach($this->fields as $group=>$group_data)
		{
			foreach($group_data as $field=>$searchField)
			{
				if ($field=='purchase_order_number')
				{
                    $search_value = $searchField->getValue();
                    
                    if(!empty($search_value))
                    {
                        $invoices = PInvoice::getInvoices($searchField->getValue());
                        
                        if(!empty($invoices))
                        {
                            $cc->add(new Constraint('id', 'in', '('.implode(',', array_keys($invoices)).')'));
                        }
                    }
				}
				elseif ($searchField->doConstraint())
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

// End of pinvoicesSearch

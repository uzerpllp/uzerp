<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SLCustomerSearch extends BaseSearch {

	protected $version='$Revision: 1.13 $';
	protected $fields=array();

	public static function useDefault($search_data=null,&$errors=array(), $defaults=null) {

		$search = new SLCustomerSearch($defaults);
		
		$search->default_fields(&$search_data);
		
// Execute Search
		$search->setSearchData($search_data,$errors);
		return $search;
	}	

	public static function statements($search_data=null,&$errors=array(), $defaults=null) {

		$search = new SLCustomerSearch($defaults);
		
		$search->default_fields($search_data);

// Name
		$search->removeSearchField('name');
		
		$search->addSearchField(
			'name',
			'name_starts_with',
			'begins'
		);

// Balance
		$search->addSearchField(
			'outstanding_balance',
			'outstanding_balance',
			'select',
			'<>'
		);
		$options=array('all'=>'All'
					  ,'<>'=>'Not zero'
					  ,'>'=>'Greater than zero'
					  ,'<'=>'Less than zero'
					  ,'='=>'Equals zero');
		
		$search->setOptions('outstanding_balance',$options);
		
// Type
		$search->addSearchField(
			'type',
			'type',
			'hidden',
			'',
			'hidden',
			false
		);
		
// Execute Search
		$search->setSearchData($search_data, $errors, 'statements');
		return $search;
		
	}
	
	private function default_fields ($search_data) {
		
		$slcustomer=new SLCustomer();
		
// Name
		$this->addSearchField(
			'name',
			'name_contains',
			'contains'
		);
		
// Search by Active/Inactive Status
		$this->addSearchField(
			'date_inactive',
			'Show Customers',
			'null',
			'null',
			'advanced'
		);
		$options = array(''			=> 'All'
						,'not null'	=> 'Inactive'
						,'null'		=> 'Active');
		$this->setOptions('date_inactive', $options);
		
// Currency
		$this->addSearchField(
			'currency_id',
			'currency',
			'select',
			'',
			'advanced'
		);
		$currency=new Currency();
		$currency_list=$currency->getAll();
		$options=array(''=>'All');
		$options+=$currency_list;
		$this->SetOptions('currency_id',$options);
		
// Remittance
		$this->addSearchField(
			'statement',
			'statement',
			'select',
			'',
			'advanced'
		);
		$options=array(''=>'All'
					  ,'TRUE'=>'Yes'
					  ,'FALSE'=>'No');
		$this->setOptions('statement',$options);
		
// Invoice Method
		$this->addSearchField(
			'invoice_method',
			'invoice_method',
			'select',
			'',
			'advanced'
		);
		$options=array_merge(array(''=>'All')
						  	,$slcustomer->getEnumOptions('invoice_method'));
		$this->setOptions('invoice_method',$options);

// Payment Type
		$this->addSearchField(
			'payment_type_id',
			'payment_type',
			'select',
			'',
			'advanced'
		);
		$payment_type = new PaymentType();
		$options=array(''=>'All');
		$options+=$payment_type->getAll();
		$this->setOptions('payment_type_id',$options);
		
// Payment Terms
		$this->addSearchField(
			'payment_term_id',
			'payment_term',
			'select',
			'',
			'advanced'
		);
		$payment_term = new PaymentTerm();
		$options=array(''=>'All');
		$options=$payment_term->getAll();
		asort($options);
		$options=array(''=>'All')+$options;
		$this->setOptions('payment_term_id',$options);
		
// SL Anaylsis
		$this->addSearchField(
			'sl_analysis_id',
			'sl_analysis',
			'select',
			'',
			'advanced'
		);
		$sl_analysis = new SLAnalysis();
		$options=array(''=>'All');
		$options+=$sl_analysis->getAll();
		$this->setOptions('sl_analysis_id',$options);

// Account Status
		$this->addSearchField(
			'account_status',
			'account_status',
			'select',
			'',
			'advanced'
		);
		$options=array_merge(array(''=>'All')
						  	,$slcustomer->getEnumOptions('account_status'));
		$this->setOptions('account_status',$options);
		
	}

	public function toConstraintChain() {
		$cc = new ConstraintChain();
		if($this->cleared) {
			return $cc;
		}
		debug('SLCustomerSearch::toConstraintChain Fields: '.print_r($this->fields, true));
		
		foreach($this->fields as $group) {
			foreach($group as $field=>$searchField) {
				if ($field=='outstanding_balance') {
					$value=$searchField->getValue();
					if ($value<>'all') {
						$cc->add(new Constraint('outstanding_balance', $value, '0'));
					}
				} elseif ($searchField->doConstraint()) {
					$c = $searchField->toConstraint();
					if($c!==false) {
						$cc->add($c);
					}
				}
			}
		}
		debug('SLCustomerSearch::toConstraintChain Constraints: '.print_r($cc, true));
		return $cc;
				
	}
	
}
?>
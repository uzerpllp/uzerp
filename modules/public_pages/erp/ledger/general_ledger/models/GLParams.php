<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GLParams extends DataObject {

	protected $version = '$Revision: 1.13 $';
	
//	protected $defaultDisplayFields=array('paramdesc'=>'Description'
//										 ,'display_value'=>'Value'
//										 );
	
// Array containing currently loaded parameter values
	protected $_params = array(); 
	
// List of parameters currently supported
	public $accruals_control_account = "Accruals Control Account";
	public $ar_pl_suspense_account = "Asset Purchases Suspense GL Account";
	public $ar_pl_suspense_centre = "Asset Purchases Suspense GL Cost Centre";
	public $ar_disposals_proceeds_account = "Asset Disposal Proceeds GL Account";
	public $ar_disposals_proceeds_centre = "Asset Disposal Proceeds Cost Centre";
	public $balance_sheet_cost_centre = "Balance Sheet Cost Centre";
	public $base_currency = "Base Currency";
	public $contras_control_account = "Contras Control Account";
	public $expenses_control_account = "Expenses Control Account";
	public $number_of_periods_in_year = "Number of periods in year";
	public $number_of_weeks_in_year = "Number of weeks in year";
	public $pl_account_centre = "PandL Account Centre";
	public $product_account = "Default Product Account Code";
	public $product_centre = "Default Product Centre Code";
	public $purchase_ledger_control_account = "Purchase Ledger Control Account";
	public $retained_profits_account = "Retained Profits Account";
	public $sales_ledger_control_account = "Sales Ledger Control Account";
	public $twin_currency = "Twin Currency";
	public $vat_input ="VAT Input";
	public $vat_output = "VAT Output";
	public $vat_control_account = "VAT Control Account";
	public $vat_payee_company = "VAT Payee";
	public $vat_eu_acquisitions = "VAT EU Acquisitions";
	public $intrastat_net_mass = "UoM for Intrastat Net Mass";
	
// Array containing 'soft' fk references
	protected $_dataSources = array();
	
	function __construct($tablename = 'gl_params')
	{
		parent::__construct($tablename);
		
		$this->idField			= 'id';
		$this->identifierField	= 'paramdesc || \' - \' || paramvalue';	
		$this->orderby			= 'paramdesc';
		
		$this->validateUniquenessOf(array('paramdesc'));

		$this->setEnum('paramdesc',array($this->accruals_control_account=>$this->accruals_control_account
										,$this->ar_pl_suspense_account=>$this->ar_pl_suspense_account
										,$this->ar_pl_suspense_centre=>$this->ar_pl_suspense_centre
										,$this->ar_disposals_proceeds_account=>$this->ar_disposals_proceeds_account
										,$this->ar_disposals_proceeds_centre=>$this->ar_disposals_proceeds_centre
										,$this->balance_sheet_cost_centre=>$this->balance_sheet_cost_centre
										,$this->base_currency=>$this->base_currency
										,$this->contras_control_account=>$this->contras_control_account
										,$this->expenses_control_account=>$this->expenses_control_account
										,$this->number_of_periods_in_year=>$this->number_of_periods_in_year
										,$this->number_of_weeks_in_year=>$this->number_of_weeks_in_year
										,$this->pl_account_centre=>$this->pl_account_centre
										,$this->product_account=>$this->product_account
										,$this->product_centre=>$this->product_centre
										,$this->purchase_ledger_control_account=>$this->purchase_ledger_control_account
										,$this->retained_profits_account=>$this->retained_profits_account
										,$this->sales_ledger_control_account=>$this->sales_ledger_control_account
										,$this->twin_currency=>$this->twin_currency
										,$this->vat_input=>$this->vat_input
										,$this->vat_output=>$this->vat_output
										,$this->vat_control_account=>$this->vat_control_account
										,$this->vat_eu_acquisitions=>$this->vat_eu_acquisitions
										,$this->vat_payee_company=>$this->vat_payee_company
										,$this->intrastat_net_mass=>$this->intrastat_net_mass
										)
						);
		
// This defines the 'soft' foreign key links for the parameter description
// Overrides DataObject::hasMany
		$control_account = new ConstraintChain();
		$control_account->add(new Constraint('control', 'is', TRUE));
		
		$this->hasMany('Currency', $this->base_currency, 'currency');
		$this->hasMany('Currency', $this->twin_currency, 'currency');
		$this->hasMany('GLCentre', $this->balance_sheet_cost_centre, 'cost_centre');
		$this->hasMany('GLAccount', $this->contras_control_account, 'account', $control_account);
		$this->hasMany('GLCentre', $this->pl_account_centre, 'cost_centre');
		$this->hasMany('GLAccount', $this->accruals_control_account, 'account', $control_account);
		$this->hasMany('GLAccount', $this->purchase_ledger_control_account, 'account', $control_account);
		$this->hasMany('GLAccount', $this->retained_profits_account, 'account');
		$this->hasMany('GLAccount', $this->sales_ledger_control_account, 'account', $control_account);
		$this->hasMany('GLAccount', $this->vat_input, 'account', $control_account);
		$this->hasMany('GLAccount', $this->vat_output, 'account', $control_account);
		$this->hasMany('GLAccount', $this->vat_control_account, 'account', $control_account);
		$this->hasMany('GLAccount', $this->vat_eu_acquisitions, 'account', $control_account);
		$this->hasMany('Company', $this->vat_payee_company, 'name');
		$this->hasMany('GLAccount', $this->product_account, 'account');
		$this->hasMany('GLCentre', $this->product_centre, 'cost_centre');
		$this->hasMany('GLAccount', $this->ar_pl_suspense_account, 'account');
		$this->hasMany('GLCentre', $this->ar_pl_suspense_centre, 'cost_centre');
		$this->hasMany('GLAccount', $this->ar_disposals_proceeds_account, 'account');
		$this->hasMany('GLCentre', $this->ar_disposals_proceeds_centre, 'cost_centre');
		$this->hasMany('GLAccount', $this->expenses_control_account, 'account', $control_account);
		$this->hasMany('STuom', $this->intrastat_net_mass, 'uom_name');
		
		if(!defined('EGS_COMPANY_ID'))
			return false;
 	}

  	public function hasMany($do, $name, $fkfield, $cc = null)
  	{
// Overrides the DataObject::hasMany to support 'soft' fk links for GL parameters
		$this->_dataSources[$name]['model'] = $do;
		$this->_dataSources[$name]['field'] = $fkfield;
		
		if (!empty($cc))
		{
			$this->_dataSources[$name]['cc'] = $cc;
		}
		
   	}
  	
	public function getParam($name)
	{
// If the value has not already been retrieved
// or if a value has been retrieved but not for the required paramdesc
// then get it from the database
		if(!isset($this->_params[$name]))
		{
			if ($this->paramdesc<>$name)
			{
				$this->loadBy('paramdesc', $name);
				
				if ($this->isLoaded())
				{
					if (isset($this->_dataSources[$name]))
					{
						$this->_params[$name]=$this->paramvalue_id;
					}
					else
					{
						$this->_params[$name]=$this->paramvalue;
					}
				}
			}
		}
		
		if(isset($this->_params[$name]))
		{
			return $this->_params[$name];
		}
		else
		{
			return false;
		}
	}

	public function getSelectList($name = '')
	{
// Gets the select list for the specified FK table
// returns empty array if no FK for the parameter specified
		if (empty($name))
		{
			$name = $this->paramdesc;
		}
		
		if (isset($this->_dataSources[$name]['model']))
		{
			$do = $this->_dataSources[$name]['model'];
			
			$select = DataObjectFactory::Factory($do);
			
			if (isset($this->_dataSources[$name]['cc']) && ($this->_dataSources[$name]['cc'] instanceof ConstraintChain))
			{
				return $select->getAll($this->_dataSources[$name]['cc']);
			}
			else
			{
				return $select->getAll();
			}
		}
		else
		{
			return array();
		}
	}
	
	public function getValue($name)
	{
// Gets the value for the specified FK table/field
// where the supplied value is the unique id of the fk table
		if (!isset($this->_dataSources[$name]))
		{
			return $this->paramvalue;
		}
		else
		{
			$do = $this->_dataSources[$name]['model'];
			
			$field = $this->_dataSources[$name]['field'];
			
			if (!is_null($this->paramvalue_id) || !empty($this->paramvalue_id))
			{
				$result = DataObjectFactory::Factory($do);
				
				$result->load($this->paramvalue_id);
				
				if($result) {
					return $result->$field;
				}
			}
		}
		
		return false;
	}
	
	public function load($clause,$override=false)
	{
		parent::load($clause,$override=false);
		
		if ($this->isLoaded())
		{
			$value = $this->getValue($this->paramdesc);
			
			$this->addField('display_value', new DataField('display_value', $value));
			$this->getField('display_value')->ignoreField=true;
		}
		
		return $this;
	}
	
	public function valueType($name)
	{
		if (isset($this->_dataSources[$name]))
		{
			return 'paramvalue_id';
		}
		
		return 'paramvalue';
	}
	
	public function validValue($name, $value)
	{
// Checks that the supplied value exists in the FK table/field
		if (!isset($this->_dataSources[$name]))
		{
			return true;
		}
		else
		{
			$do = $this->_dataSources[$name]['model'];
			
			$field = $this->_dataSources[$name]['field'];
			
			if ($value!==false)
			{
				$result = DataObjectFactory::Factory($do);
				
				$result =$result->loadBy($field, $value);
				
				if($result) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	public function accruals_control_account (){
		return $this->getParam($this->accruals_control_account);
	}
	
	public function base_currency (){
		return $this->getParam($this->base_currency);
	}
	
	public function base_currency_symbol () {
		$currency = DataObjectFactory::Factory('Currency');
		
		$currency->load($this->base_currency());
		
		return $currency->symbol;
	}
	
	public function twin_currency () {
		return $this->getParam($this->twin_currency);
	}
	
	public function balance_sheet_cost_centre () {
		return $this->getParam($this->balance_sheet_cost_centre);
	}
	
	public function contras_control_account () {
		return $this->getParam($this->contras_control_account);
	}
	
	public function number_of_weeks_in_year () {
		return $this->getParam($this->number_of_weeks_in_year);
	}
	
	public function number_of_periods_in_year () {
		return $this->getParam($this->number_of_periods_in_year);
	}

	public function pl_account_centre () {
		return $this->getParam($this->pl_account_centre);
	}
	
	public function purchase_ledger_control_account () {
		return $this->getParam($this->purchase_ledger_control_account);
	}
	
	public function retained_profits_account () {
		return $this->getParam($this->retained_profits_account);
	}
	
	public function sales_ledger_control_account () {
		return $this->getParam($this->sales_ledger_control_account);
	}
	
	public function vat_input () {
		return $this->getParam($this->vat_input);
	}
	
	public function vat_output () {
		return $this->getParam($this->vat_output);
	}

	public function vat_control_account () {
		return $this->getParam($this->vat_control_account);
	}
	
	public function eu_acquisitions () {
		return $this->getParam($this->vat_eu_acquisitions);
	}

	public function vat_payee_company () {
		return $this->getParam($this->vat_payee_company);
	}

	public function product_account () {
		return $this->getParam($this->product_account);
	}
	
	public function product_centre () {
		return $this->getParam($this->product_centre);
	}
	
	public function ar_pl_suspense_account () {
		return $this->getParam($this->ar_pl_suspense_account);
	}
	
	public function ar_pl_suspense_centre () {
		return $this->getParam($this->ar_pl_suspense_centre);
	}
	
	public function ar_disposals_proceeds_account () {
		return $this->getParam($this->ar_disposals_proceeds_account);
	}
	
	public function ar_disposals_proceeds_centre () {
		return $this->getParam($this->ar_disposals_proceeds_centre);
	}
	
	public function expenses_control_account () {
		return $this->getParam($this->expenses_control_account);
	}
	
	public function intrastat_net_mass () {
		return $this->getParam($this->intrastat_net_mass);
	}
	
	public function unassignedParams ()
	{
		$this->idField = 'paramdesc';
		
		$rows = $this->GetAll();

		$unassigned = array();
		
		foreach ($this->getEnumOptions('paramdesc') as $option)
		{
			if (!isset($rows[$option]))
			{
				$unassigned[$option] = $option;
			}
		}
		
		return $unassigned;
		
	}
}

// End of GLParams

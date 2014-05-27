<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SLTransactionCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.7 $';
	public $field;
	public $agedBalances=array();
	public $agedMonths=6;
		
	function __construct($do='SLTransaction', $tablename='sltransactionsoverview') {
		parent::__construct($do, $tablename);
			
	}
	
	function agedSummary() {
		$this->_tablename='sl_aged_debtors_summary';
		$sh = new SearchHandler($this, false);
		$sh->setOrderby('');
		$this->load($sh);
		$this->agedBalances['Total']=0;
		for ($i=0; $i<=$this->agedMonths; $i++) {
			$this->agedBalances[$i]=0;
		}
		$total=0;
		foreach ($this as $agedcreditors) {
			$total=bcadd($total, $agedcreditors->value);
			if ($agedcreditors->id>$this->agedMonths) {
				$this->agedBalances[$this->agedMonths]+=$agedcreditors->value;
			} else {
				$this->agedBalances[$agedcreditors->id]=$agedcreditors->value;
			}
		}
		$this->agedBalances['Total']=$total;
		return $this->agedBalances;
	}

	function agedDebtor ($_slmaster_id='', $_aged_months='') {
		
		if (!empty($_aged_months)) {
			$this->agedMonths=$_aged_months;
		}
		$this->_tablename='sl_aged_debtors_overview';
		$this->orderby=array('slmaster_id','age');
		$sh = new SearchHandler($this, false);
		$cc=new ConstraintChain();
		if (!empty($_slmaster_id)) {
			if (!is_array($_slmaster_id)) {
				$_slmaster_id=array($_slmaster_id);
			}
			$cc=new Constraint('slmaster_id', 'in', '('.implode(',', $_slmaster_id).')');
			$sh->addConstraintChain($cc);
		}
		$this->load($sh);
		for ($i=-1; $i<=$this->agedMonths; $i++) {
			$this->agedBalances[$i]['title']='Month+'.$i;
			$this->agedBalances[$i]['value']='0.00';
		}
		$this->agedBalances[-1]['title']='Future';
		$this->agedBalances[0]['title']='Current Month';
		$this->agedBalances[$this->agedMonths+1]['title']='Older';
		$this->agedBalances[$this->agedMonths+1]['value']='0.00';
		$total=0;
		foreach ($this as $agedcreditors) {
			$total=bcadd($total, $agedcreditors->value);
			if ($agedcreditors->age>$this->agedMonths) {
				$this->agedBalances[$this->agedMonths]['value']=BCADD($this->agedBalances[$this->agedMonths]['value'], $agedcreditors->value, 2);
			} elseif ($agedcreditors->age<0) {
				$this->agedBalances[-1]['value']=BCADD($this->agedBalances[-1]['value'], $agedcreditors->value, 2);
			} else {
				$this->agedBalances[$agedcreditors->age]['value']=BCADD($this->agedBalances[$agedcreditors->age]['value'], $agedcreditors->value, 2);
			}
		}
		$this->agedBalances[$this->agedMonths+2]['title']='Total';
		$this->agedBalances[$this->agedMonths+2]['value']=$total;
		return $this->agedBalances;
		
	}

}
?>
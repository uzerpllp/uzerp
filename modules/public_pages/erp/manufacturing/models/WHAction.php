<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class WHAction extends DataObject
{

	protected $version='$Revision: 1.10 $';
	
	protected $defaultDisplayFields = array('position'
											,'action_name'
											,'description'
											,'type');

	function __construct($tablename='wh_actions')
	{
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField="action_name ||'-'|| description";		
		$this->orderby='action_name';		
		
 		$this->validateUniquenessOf('action_name');

 		$this->setEnum('type'
							,array('B'=>'Backflush'
								  ,'C'=>'Completion'
								  ,'D'=>'Despatch'
								  ,'I'=>'Issue'
								  ,'M'=>'Manual'
								  ,'R'=>'Receive'
								  ,'X'=>'Return'
								  ,'T'=>'Warehouse Transfer'
								)
 						);
 		$rules=array('0'=>'All'
					,'1'=>'true'
					,'2'=>'false'
 					);
 		$this->setEnum('from_has_balance', $rules);
 		$this->setEnum('from_bin_controlled', $rules);
 		$this->setEnum('from_saleable', $rules);
 		$this->setEnum('to_has_balance', $rules);
 		$this->setEnum('to_bin_controlled', $rules);
 		$this->setEnum('to_saleable', $rules);
 		
 		$this->hasMany('WHTransferrule','rules','whaction_id');
	}

	function getActions($type='')
	{
		
		$cc=new ConstraintChain();
		
		if (!empty($type))
		{
			$cc->add(new Constraint('type', '=', $type));
		}
		
		$cc->add(new Constraint('defined_rules', '>', 0));
		
		return $this->getAll($cc, true, true);
	}

	public function rules_list($field)
	{
		$rules=array();
		foreach ($this->rules as $rule)
		{
			$rules[$rule->id]=$rule->{$field};		
		}
		return $rules;
	}
	
	static function getDespatchActions()
	{
		$whaction = DataObjectFactory::Factory('WHAction');
		return $whaction->getActions('D');
	}
	
	static function getReceiveActions()
	{
		$whaction = DataObjectFactory::Factory('WHAction');
		return $whaction->getActions('R');
	}
	
}

// end of WHAction.php

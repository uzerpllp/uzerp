<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GLUnpostedTransaction extends DataObject
{
	
	protected $version = '$Revision: 1.2 $';
	
	protected $defaultDisplayFields = array('docref'		=> 'Doc.Ref'
											,'account'		=> 'Account'
											,'cost_centre'	=> 'Cost Centre'
//											,'value'		=> 'Value'
											,'debit'		=> 'Debit'
											,'credit'		=> 'Credit'
											,'comment'		=> 'Comment'
											,'reference'	=> 'Reference'
											,'glaccount_id'
											,'glcentre_id'
											);
											
	
	function __construct($tablename='gl_unposted_transactions')
	{
// Register non-persistent attributes
		$this->setAdditional('credit', 'numeric');
		$this->setAdditional('debit', 'numeric');
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'docref';		
		$this->orderby			= array('created', 'account', 'cost_centre');
		$this->orderdir			= 'desc';
		
// Define relationships
		$this->belongsTo('GLAccount', 'glaccount_id', 'account');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'cost_centre');
 		
// Define field formats		
 		$this->getField('credit')->setFormatter(new NumericFormatter());
 		$this->getField('debit')->setFormatter(new NumericFormatter());
 		
// Define validation
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre',array('glaccount_id'=>'glaccount_id','glcentre_id'=>'glcentre_id')));

// Define default values
		
// Define enumerated types
	}

	public function load ($clause, $override = FALSE, $return = FALSE)
	{
		parent::load($clause, $override, $return);
		
		if ($this->isLoaded() && !is_null($this->value))
		{
			if ($this->value<0)
			{
				$this->credit	= bcmul($this->value, -1);
			}
			else
			{
				$this->debit	= bcadd($this->value, 0);
			}
		}
		
		return $this;
	}
	
	public static function Factory($data, &$errors = array(), $do_name = null)
	{
		if($data['debit'] > 0)
		{
			$data['value'] = BCADD($data['debit'], 0);
		}
		elseif($data['credit'] > 0)
		{
			$data['value'] = BCMUL($data['credit'], -1);
		}
			
		return parent::Factory($data, $errors, $do_name);
	}
}

// End of GLUnpostedTransaction

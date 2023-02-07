<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Address extends DataObject
{
	
	protected $version = '$Revision: 1.11 $';
	
	protected $defaultDisplayFields = array('street1'=>'Address'
										   ,'street2'=>''
										   ,'street3'=>''
										   ,'town'
										   ,'county'
										   ,'postcode'
										   ,'countrycode'
										   );
	
	function __construct($tablename='address')
	{
		parent::__construct($tablename);
		$this->idField='id';

		$fulladdress = array('street1','street2','street3','town','county','postcode','country');
		
		$this->identifierField = $fulladdress;
		$this->identifierFieldJoin = ', ';
		
 		$this->belongsTo('Country', 'countrycode', 'country');
 		$this->hasMany('PartyAddress', 'parties', 'address_id');
 		$this->setConcatenation('fulladdress', $fulladdress, ', ');
	}

	function check_exists ($data = array())
	{
		$cc = new ConstraintChain();
		
		foreach (array('street1','street2','street3','town','county','postcode','countrycode') as $field)
		{
			if (!empty($data[$field]))
			{
				//escape special characters on varchar fields, especially parentheses, to avoid invalid SQL in the contraint chain.
				$fields = $this->_fields;
				if ($fields[$field]->type = 'varchar') {
					$cc->add(new Constraint($field, '=', preg_quote($data[$field])));
				} else {
					$cc->add(new Constraint($field, '=', $data[$field]));
				}
		    }
			else
			{
				$cc->add(new Constraint($field, 'is' , 'NULL'));
			}
		}
		
		$this->loadBy($cc);
		
	}
	
	function delete($id = '', &$errors = array(), $archive = FALSE, $archive_table = null, $archive_schema = null)
	{
		if (!$this->isLoaded())
		{
			if (empty($id))
			{
				$errors[] = 'No Address identifier';
				return false;
			}
			else
			{
				$this->load($id);
				if (!$this->isLoaded())
				{
					$errors[] = 'Cannot find Address record';
					return false;
				}
			}
		}
		
		$partyaddress = DataObjectFactory::Factory('PartyAddress');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('address_id', '=', $this->id));		
		
		if ($partyaddress->getCount($cc) == 0 && !parent::delete($this->id, $errors))
		{
			return false;
		}
		
		return true;
		
	}
	
	function getAll(ConstraintChain $cc = null, $ignore_tree = FALSE, $use_collection = FALSE, $limit = '')
	{
		return parent::getAll($cc, $ignore_tree, TRUE, $limit);
	}
	
	static function Factory($data, &$errors = array(), $do_name = null)
	{
	
		$address = DataObjectFactory::Factory($do_name);
		$address->check_exists($data);
	
		if ($address->isLoaded())
		{
			return $address;
		}
	
		return parent::factory($data, $errors, $address);
	
	}
	
}

// End of Address

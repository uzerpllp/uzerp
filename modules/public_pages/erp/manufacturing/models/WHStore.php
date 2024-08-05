<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WHStore extends DataObject
{

	protected $version='$Revision: 1.10 $';

	protected $defaultDisplayFields = array('store_code'
											,'description'
											);

	function __construct($tablename = 'wh_stores')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField			= 'id';
		$this->orderby			= 'store_code';
		$this->identifierField	= "store_code ||'-'|| description";		

 		$this->validateUniquenessOf('store_code');

		// The description forms part of the identifier,
		// make sure we get a value for it.
		$this->getField('description')->not_null = true;

// Define relationships
 		$this->hasOne('Companyaddress', 'address_id', 'address');
		$this->hasMany('WHLocation', 'locations', 'whstore_id');

// Define field formats

// Define enumerated types

	}

	public function getAddress()
	{
		$address = DataObjectFactory::Factory('Companyaddress');

		$address->load($this->address_id);

		if ($address)
		{
			$pc = new printController();
			return $pc->formatAddress($address);
		}
		else
		{
			return '';
		}
	}

	public static function getAddresses()
	{
		$address = DataObjectFactory::Factory('Companyaddress');

		$cc = new ConstraintChain();

		$cc->add(new Constraint('shipping', 'is', 'true'));

		return $address->getAddresses(EGS_COMPANY_ID, $cc);

	}

	public function getLocationList()
	{
		$WHLocation = DataObjectFactory::Factory('WHLocation');

		$cc = new ConstraintChain();

		$cc->add(new Constraint('whstore_id','=',$this->{$this->idField}));

		return $WHLocation->getAll($cc);		
	}

	public function getBinLocationList($source)
	{
		$WHLocation = DataObjectFactory::Factory('WHLocation');

		$cc = new ConstraintChain();

		$cc->add(new Constraint('whstore_id','=',$source));
		$cc->add(new Constraint('bin_controlled','=','true'));

		return $WHLocation->getAll($cc);		
	}

}

// End of WHStore

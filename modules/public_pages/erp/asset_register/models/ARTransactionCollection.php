<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ARTransactionCollection extends DataObjectCollection
{

	protected $version='$Revision: 1.7 $';

	public $field;

	function __construct($do = 'ARTransaction', $tablename = 'ar_transactions_overview')
	{
		parent::__construct($do, $tablename);

	}

// TODO: There is now a DataObjectCollection delete function
	function deleteAll ($cc = null)
	{

		$db = DB::Instance();

		$result = false;

		if (is_null($cc))
		{
			$cc = new ConstraintChain();
		}

		if($cc instanceof ConstraintChain)
		{
			if ($this->_templateobject->isAccessControlled())
			{

 				if(!isModuleAdmin())
 				{
					$cc->add(new Constraint('usernameaccess', '=', EGS_USERNAME));
					$cc->add(new Constraint('owner','=',EGS_USERNAME),'OR');
				}
			}
			else
			{
				$cc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
			}

			$do = DataObjectFactory::Factory($this->_doname);

			$query='DELETE FROM '.$do->getTableName().' where '.$cc->__toString();

			$result=$db->Execute($query);

		}

		return ($result!==false);

	}

}

// End of ARTransactionCollection

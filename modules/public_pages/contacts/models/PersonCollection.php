<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PersonCollection extends PartyCollection
{
	
	protected $version = '$Revision: 1.8 $';
	
	public $field;
	
	function __construct($do = 'Person', $tablename = 'personoverview')
	{
		parent::__construct($do, $tablename);
		
		$this->identifier		= 'surname';
		$this->identifierField	= 'name';
	}
	
	function load($sh,$c_query=null)
	{
		$db=DB::Instance();
		
		$qb=new QueryBuilder($db, $this->_doname);
		
		if($sh instanceof SearchHandler)
		{
			if ($this->_templateobject->isAccessControlled())
			{
				if(isModuleAdmin())
				{
					$cc = new ConstraintChain();
					$cc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
					$cc->add(new Constraint('id', '=', EGS_COMPANY_ID), 'OR');
					
					$sh->addConstraintChain($cc);
					
					$qb->setDistinct();
				}
				else
				{
					$cc = new ConstraintChain();
					$cc->add(new Constraint('usernameaccess', '=', EGS_USERNAME));
					$cc->add(new Constraint('owner', '=', EGS_USERNAME), 'OR');
					
					$cc2 = new ConstraintChain();
					$cc2->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
					
					$sh->addConstraintChain($cc);
					
					$sh->addConstraintChain($cc2);
					
					$qb->setDistinct();						
				}
			}
			$this->sh = $sh;
		}
		$this->_load($sh,$qb,$c_query);
	}	
		
}

// End of PersonCollection

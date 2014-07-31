<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Resourcetemplate extends DataObject {
	
	protected $version='$Revision: 1.5 $';
	
	protected $defaultDisplayFields=array('person'
										, 'name'
										, 'resource_type'
										, 'standard_rate'
										, 'overtime_rate'
										, 'quantity'
										, 'cost'
										);

	public function __construct($tablename='resource_templates') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField = 'person';
		
		$this->orderby = 'person,name';
		$this->orderdir = 'asc';
		
		$cc=new ConstraintChain();
		$cc->add(new Constraint('company_id', '=', EGS_COMPANY_ID));
 		$this->belongsTo('Person', 'person_id', 'person',  $cc, "surname || ', ' || firstname");
		$this->belongsTo('Resourcetype', 'resource_type_id', 'resource_type');
		
		$this->validateUniquenessOf(array('name','person_id','usercompanyid'), 'There is already a template of that name.');
		$this->getField('name')->setDefault('Standard');
	}

}
?>

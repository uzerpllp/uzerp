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
										, 'resource'
										, 'resource_type'
										, 'standard_rate'
										, 'overtime_rate'
										);

	public function __construct($tablename='resource_templates') {
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->identifierField = 'person';
		
		$this->orderby = 'person,name';
		$this->orderdir = 'asc';
		
		$cc=new ConstraintChain();
		$cc->add(new Constraint('company_id', '=', EGS_COMPANY_ID));
		
// Define relationships
 		$this->belongsTo('Person', 'person_id', 'person',  $cc, "surname || ', ' || firstname");
		$this->belongsTo('Resourcetype', 'resource_type_id', 'resource_type');
		$this->belongsTo('MFResource', 'mfresource_id', 'resource');

// Define validation
		$this->validateUniquenessOf(array('person_id','usercompanyid','mfresource_id'), 'There is already a template for this Person/Resource.');
		$this->getField('name')->setDefault('Standard');
	}

}
?>

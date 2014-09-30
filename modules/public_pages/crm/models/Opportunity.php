<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Opportunity extends DataObject
{
	
	protected $version = '$Revision: 1.9 $';
	
	protected $defaultDisplayFields = array('name'		=> 'Name'
										   ,'company'	=> 'Company'
										   ,'person'	=> 'Person'
										   ,'status'	=> 'Status'
										   ,'value'		=> 'Value'
										   ,'cost'		=> 'Cost'
										   ,'enddate'	=> 'End'
										   ,'assigned'	=> 'Assigned To'
										   );
	
	protected $linkRules;
										   
	function __construct($tablename = 'opportunities')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= array('name','company');
		$this->orderby			= 'lower(name)';
		
		$this->view='';
		
// Define relationships
		$this->belongsTo('Opportunitystatus', 'status_id', 'status');
 		$this->belongsTo('Campaign', 'campaign_id', 'campaign');
 		$this->belongsTo('Company', 'company_id', 'company');
 		$this->belongsTo('Person', 'person_id', 'person', null, 'surname || \', \' || firstname');
 		$this->belongsTo('User', 'owner', 'opportunity_owner');
 		$this->belongsTo('User', 'assigned', 'opportunity_assigned');
 		$this->belongsTo('User', 'alteredby', 'opportunity_alteredby');
 		$this->belongsTo('Opportunitysource', 'source_id', 'source');
 		$this->belongsTo('Opportunitytype','type_id','type');
  		$this->hasMany('Project','projects');
 		$this->hasMany('Activity','activities');
		$this->hasMany('Hour','hours');
		$this->hasMany('opportunitynote','notes');
		$this->hasMany('opportunityattachment','attachments');
  		
// Define field formats
		$this->getField('cost')->setFormatter(new PriceFormatter());
		$this->getField('value')->setFormatter(new PriceFormatter());

// Define enumerated types
		$numbers = array();
		
		for($i=0; $i<=100; $i+=5)
			$numbers[$i] = $i;
		
		$this->setEnum('probability', $numbers);
		
// Define link rules for sidebar related view
		$this->linkRules = array('projects' => array('actions' => array('link')
													,'rules'=>array())
							); 		
	}
	
	public function getAll(ConstraintChain $cc=null, $ignore_tree=false, $use_collection=false, $limit='')
	{
		return parent::getAll($cc, $ignore_tree, true, $limit);
	}

}

// End of Opportunity

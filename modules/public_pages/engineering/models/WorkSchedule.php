<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WorkSchedule extends DataObject
{
	
	protected $version = '$Revision: 1.2 $';
	
	protected $defaultDisplayFields = array('job_no'
										   ,'description'
										   ,'centre'
										   ,'start_date'
										   ,'end_date'
										   ,'status'
										   ,'downtime_code'
										   );
	
	private $_status_active		= 'A';
	private $_status_cancelled	= 'X';
	private $_status_complete	= 'C';
	private $_status_new		= 'N';
	
	private $_transaction_type	= 'EN';
	
//	protected $defaultDisplayFields=array();
	
//	protected $linkRules;

	function __construct($tablename = 'eng_work_schedules')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->idField	= 'id';
		
		// See Factory - identifierField is used to generate next number
		$this->identifierField	= array('job_no', 'description');
		$this->orderby			= 'job_no';
		
		$this->setTitle('work_schedules');
		
		// Define relationships
		$this->BelongsTo('MFCentre','centre_id', 'centre');
		$this->BelongsTo('MFDowntimeCode','mf_downtime_code_id', 'downtime_code');
		
		$this->hasMany('EngineeringResource','resources', 'work_schedule_id');
		$this->hasMany('WorkScheduleNote','notes', 'work_schedule_id');
//		$this->hasMany('WorkSchedulePart','parts', 'work_schedule_id');
		$this->hasMany('STTransaction', 'transactions', 'process_id');
		
		// Define field formats
		
		// Define field defaults
		$this->getField('status')->setDefault('N');
		
		// Define validation
		$this->validateUniquenessOf('job_no');
		
		// Define enumerated types
		$this->setEnum('status'
							,array($this->_status_new=>'New'
								  ,$this->_status_active=>'Active'
								  ,$this->_status_complete=>'Complete'
								  ,$this->_status_cancelled=>'Cancelled'
								)
						);
		
		// Define link rules for sidebar related view
		// disallow adding new transactions
		$this->linkRules=array('transactions'=>array('newtab'=>array('new'=>true)
													,'actions'=>array('link')
													,'rules'=>array()
												 )
							);
		
	}
	
	public function activeStatus()
	{
		return $this->_status_active;
	}
	
	public function cancelledStatus()
	{
		return $this->_status_cancelled;
	}
	
	public function completedStatus()
	{
		return $this->_status_complete;
	}
	
	public function newStatus()
	{
		return $this->_status_new;
	}
	
	public function isActive()
	{
		return ($this->status == $this->_status_active);
	}
	
	public function isCancelled()
	{
		return ($this->status == $this->_status_cancelled);
	}
	
	public function isCompleted()
	{
		return ($this->status == $this->_status_complete);
	}
	
	public function isNew()
	{
		return ($this->status == $this->_status_new);
	}
	
	static function Factory($data, &$errors, $do)
	{
		
		if (!isset($data['id']) || $data['id']=='') {
		
			$generator						= new UniqueNumberHandler();
			$model							= new $do;
			$model->identifierField			= 'job_no';
			$data[$model->identifierField]	= $generator->handle($model);
		
		}

		return parent::Factory($data, $errors, $do);

	}

	public function saveResources($resource_ids = array(), &$errors = array())
	{

		if (!is_array($resource_ids) || empty($resource_ids))
		{
			$errors[] = 'Invalid Resource Id';
			return false;
		}
		
		$eng_resource	= new EngineeringResource();
		$eng_resources	= new EngineeringResourceCollection($eng_resource);
		
		$sh = new SearchHandler($eng_resources, FALSE);
		$sh->addConstraint(new Constraint('work_schedule_id', '=', $this->id));
		
		if ($eng_resources->delete($sh) === FALSE)
		{
			$errors[] = 'Error deleting existing resources';
			return false;
		}
		
		$data = array('work_schedule_id'=>$this->id);
		
		foreach ($resource_ids as $resource_id)
		{
			$data['id']			 = '';
			$data['resource_id'] = $resource_id;
			$eng_resource = DataObject::Factory($data, $errors, 'EngineeringResource');
			
			if (!$eng_resource || !$eng_resource->save())
			{
				$errors[] = 'Error saving resources';
				return false;
			}
		}
		
		return true;
		
	}
	
	public function transaction_type()
	{
		return $this->_transaction_type;
	}
	
}

// End of WorkSchedule

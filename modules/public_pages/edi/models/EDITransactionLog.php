<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EDITransactionLog extends DataObject {

	protected $version='$Revision: 1.8 $';
	protected $defaultDisplayFields = array('created'=>'date'
											,'name'
											,'data_definition'
											,'external_system'
											,'status'
											,'action'
											,'message'
											,'external_id'
											,'internal_id'
											,'internal_identifier_field'
											,'internal_identifier_value');
	
	function __construct($tablename='edi_transactions_log') {
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->identifierField='name';
		$this->orderby=array('created', 'name');
		$this->orderdir=array('DESC', 'ASC');

// Define relationships
		$this->belongsTo('ExternalSystem', 'external_system_id', 'external_system');
		$this->belongsTo('DataDefinition', 'data_definition_id', 'data_definition');
		$this->hasOne('DataDefinition', 'data_definition_id', 'data_definition_data');

// Define enumerated types
		$this->setEnum('action',array('AD'=>'Awaiting Download'
									 ,'D'=>'Download'
									 ,'AE'=>'Awaiting Export'
									 ,'AI'=>'Awaiting Import'
									 ,'E'=>'Export'
									 ,'I'=>'Import'
									 ,'S'=>'Send'));

		$this->setEnum('status',array('N'=>'Not Started'
									 ,'V'=>'Validated'
									 ,'X'=>'Cancelled'
									 ,'E'=>'Failed'
									 ,'C'=>'Complete'));

// Define system defaults
		$this->getField('status')->setDefault('N');

// Define field formats		
	
	}

	static function getStatusByAction ($_action)
	{
		switch ($_action)
		{
			case 'AD':
			case 'AE':
			case 'AI':
				return 'N';	
			default :
				return 'C';
		}
	}
	
	/*
	 * 
	 * Copies the current log entry to the log history table
	 *
	 */
	function archive($id = null, &$errors = [], $archive_table = null, $archive_schema = null)
	{
		return parent::archive($this->id, $errors, 'edi_transactions_log_history', 'current');
	}
	
}
?>
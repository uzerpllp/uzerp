<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataDefinition extends DataObject {

	protected $version='$Revision: 1.15 $';
	protected $defaultDisplayFields = array('name'
											,'type'
											,'description'
											,'direction'
											,'external_system'
											,'implementation_class');
	
	protected $linkRules;
	private $log_errors=array();
	
	function __construct($tablename='data_definitions')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField='id';
		$this->identifierField='name';
		
// Define relationships
		$this->belongsTo('ExternalSystem', 'external_system_id', 'external_system');
		$this->hasMany('DataDefinitionDetail', 'data_definition_details', 'data_definition_id');
		$this->hasMany('EDITransactionLog', 'edi_log', 'data_definition_id');
		
// Define enumerated types
		$this->setEnum('direction',array('IN'=>'IN'
										,'OUT'=>'OUT'));
		
		$this->setEnum('type',array('CSV'=>'CSV'
								   ,'HTTP'=>'HTTP'
								   ,'HTTPS'=>'HTTPS'
								   ,'XML'=>'XML'));
		
		$this->setEnum('transfer_type',array('FTP'=>'FTP'
											,'HTTP'=>'HTTP'
											,'HTTPS'=>'HTTPS'
											,'LOCAL'=>'LOCAL'));
		
		$this->setEnum('field_separator',array('&' => 'Ampersand(&)'
											  ,'*' => 'Asterisk/Star(*)'
											  ,':' => 'Colon(:)'
											  ,',' => 'Comma(,)'
											  ,'$' => 'Dollar Sign($)'
											  ,'|' => 'Pipe(|)'
											  ,';' => 'Semi-Colon(;)'));
								   
		$this->setEnum('text_delimiter',array('"' => 'Double Quote(")'
											 ,"'" => "Single Quote(')"
											 ,'*' => 'Asterisk/Star(*)'
											 ,'!' => 'Exclamation(!)'
											 ,'%' => 'Percent(%)'
											 ,'|' => 'Pipe(|)'));
		
		$this->setEnum('abort_action',array('A' => 'All'
											,'S' => 'Skip'
											,'X' => 'Stop'));
		
 		$this->setEnum('duplicates_action',array('I' => 'Ignore'
												,'R' => 'Reject'
												,'U' => 'Replace'));
		
// Define system defaults
		
// Define field formats		
	
// Define View Related Link Rules		
		$this->linkRules=array('edi_log'=>array('actions'=>array('link')
											 ,'rules'=>array()));
	}

	function setEdiInterface()
	{
		$implements=(is_null($this->implementation_class))?'EdiInterface':$this->implementation_class;

		return new $implements($this);

	}
	
}
?>
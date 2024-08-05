<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFWasteType extends DataObject
{

	protected $version = '$Revision: 1.6 $';
	
	protected $defaultDisplayFields = array(
		'description',
		'uom_name',
		'uom_id',
		'cost'
	);
	
	protected $linkRules;
											
	function __construct($tablename = 'mf_waste_types')
	{
		
		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'description';
		$this->orderby			= array('description');
		
 		$this->validateUniquenessOf('description');

		// Define relationships
 		$this->belongsTo('STUom', 'uom_id', 'uom_name');
 		$this->hasMany('MFCentreWasteType', 'mf_centres', 'mf_waste_type_id');

		// Define enumerated types
 		
		// Define related links (empty actions/rules prevent display of related links)
 		$this->linkRules = array(
 			'mf_centres' => array(
 				'actions'	=> array(),
 				'rules'		=> array()
			)
		);
	
	}
	
	function cb_loaded()
	{
		
		// then set these formatters here because they depend on the loaded currency_id
		$this->getField('cost')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		
	}
	
}

// end of MFWasteType.php
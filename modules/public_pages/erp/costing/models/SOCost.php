<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class SOCost extends DataObject
{

	protected $version = '$Revision: 1.9 $';

	protected $defaultDisplayFields = array(
		'soproduct' => 'SO Product'
    ,'cost' => 'Total Cost'
    ,'mat' => 'Material'
    ,'lab' => 'Labour'
    ,'osc' => 'Outside Ops'
    ,'ohd' => 'Overhead'
    ,'time'
    ,'time_period'
  //  ,'lastupdated'
  //  ,'alteredby'
	);

	function __construct($tablename = 'so_costs')	{
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField = 'id';
		$this->orderby = 'soproduct';
		$this->orderdir= 'ASC';

//		$this->validateUniquenessOf('soproduct');

// Define relationships
		$this->belongsTo('SOProductlineHeader', 'product_header_id', 'soproduct');


// Define field formats
		$this->getField('cost')->setFormatter(new NumericFormatter());
		$this->getField('mat')->setFormatter(new NumericFormatter());
		$this->getField('lab')->setFormatter(new NumericFormatter());
		$this->getField('osc')->setFormatter(new NumericFormatter());
		$this->getField('ohd')->setFormatter(new NumericFormatter());

// Define validation

// Define enumerated types
    $this->setEnum('time_period',array( 'M'=>'Minutes'
                                ,'H'=>'Hours'));

// Define system defaults


	}



}

?>

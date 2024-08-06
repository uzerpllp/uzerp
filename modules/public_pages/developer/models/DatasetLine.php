<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DatasetLine extends DataObject
{

//	protected $defaultDisplayFields = array();

	protected $version = '$Revision: 1.5 $';

	function __construct($tablename = 'datasetlines')
	{
		parent::__construct($tablename);

		$this->idField = 'id';

		$this->setEnum('type', array('1' => 'varchar'
									,'2' => 'numeric'
									,'3' => 'date'
									,'4' => 'int4'));	

	}

}

// End of DatasetLine

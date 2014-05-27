<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class OutputHeader extends DataObject {

	protected $version = '$Revision: 1.6 $';
	
	protected $defaultDisplayFields = array('type'
											,'created'
											,'createdby'
											,'processed'
											);
	
	function __construct($tablename = 'output_header')
	{
		parent::__construct($tablename);

		$this->idField='id';
		$this->orderby='created';
		$this->orderdir='DESC';
		
		$this->hasMany('OutputDetail', 'output_details', 'output_header_id');
		
	}

	public function detail_counts ()
	{
		$counts=array();
		
		foreach ($this->output_details as $output_detail)
		{
			if (isset($counts[$output_detail->printaction]))
			{
				$counts[$output_detail->printaction] +=1;
			} else {
				$counts[$output_detail->printaction] = 1;
			}
		}
		return $counts;
	}
}

// End of OutputHeader

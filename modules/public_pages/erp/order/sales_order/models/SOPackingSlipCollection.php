<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SOPackingSlipCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.5 $';
	
	function __construct($do = 'SOPackingSlip', $tablename = 'so_packing_slips_overview')
	{
		parent::__construct($do, $tablename);
		
	}

	function getPackedTotals ()
	{
		$packed = array();
		
		foreach ($this as $packinglist)
		{
			$contents = unserialize(base64_decode($packinglist->contents));
			
			foreach ($contents as $key=>$qty)
			{
				if (isset($packed[$key]))
				{
					$packed[$key] += $qty;
				}
				else
				{
					$packed[$key] = $qty;
				}
			}
		}
		
		return $packed;
	} 
	
}

// End of SOPackingSlipCollection

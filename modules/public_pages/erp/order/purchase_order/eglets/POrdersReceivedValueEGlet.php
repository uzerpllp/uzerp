<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POrdersReceivedValueEGlet extends SimpleEGlet
{

	protected $version = '$Revision: 1.8 $';

	protected $template = 'po_received_value.tpl';

	function populate()
	{
		$poreceived = new POReceivedLineCollection();

		$receivedSum = $poreceived->getReceivedSum(3);

		$this->contents = $receivedSum;
	}

}

// End of POrdersReceivedValueEGlet

<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * Created on 30 Apr 2007 by Tim Ebenezer
 *
 * CalendareventattachmentsController.php
 */

class CalendareventattachmentsController extends AttachmentsController
{
	
	protected $version = '$Revision: 1.4 $';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		// Set up some variables
		$this->setModule('calendar');
		$this->setController('calendareventattachments');
		$this->setModel('calendarevent');
	}
}

// End of CalendareventattachmentsController

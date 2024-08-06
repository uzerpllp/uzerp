<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ActivityattachmentsController extends AttachmentsController
{

	protected $version = '$Revision: 1.5 $';

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);

		// Set up some variables
		$this->setModule('crm');
		$this->setController('activityattachments');
		$this->setModel('activity');
		$this->setIdField('activity_id');
	}

	public function view_crm_activity ()
	{
		parent::index();
	}

}

// End of ActivityattachmentsController

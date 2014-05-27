<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SetupController extends MasterSetupController
{

	protected $version = '$Revision: 1.6 $';
	
	protected $setup_options = array('project_categories'	=> 'Projectcategory'
									,'project_work_types'	=> 'Projectworktype'
									,'project_phases'		=> 'Projectphase'
									,'project_note_types'	=> 'ProjectNoteType'
									,'resource_types'		=> 'Resourcetype'
									);
	
}

// end of Projects:SetupController.php

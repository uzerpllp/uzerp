<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CurrentProjectsEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.8 $';
	
	function populate()
	{
		$project = DataObjectFactory::Factory('Project');
		
		$project->setDefaultDisplayFields(array('name', 'status'));
		
		$projects_do = new ProjectCollection($project);		
		
		$projects_do->setParams();
		
		$db = DB::Instance();
		
		$query = 'SELECT p.id, p.name, p.status ';
		
		$c_query = 'SELECT count(*)';
		
		$rest='	FROM projects p 
				LEFT JOIN project_resources r ON (p.id=r.project_id) 
				LEFT JOIN users u ON (r.person_id=u.person_id) 
				WHERE p.usercompanyid='.$db->qstr(EGS_COMPANY_ID).' AND (
					u.username = '.$db->qstr(EGS_USERNAME).
					' OR p.owner='.$db->qstr(EGS_USERNAME).
				") AND p.status IN ('N', 'A')";
			
		$query .= $rest;
		
		$c_query .= $rest;
		
		$projects_do->load($query, $c_query);
		
		$projects_do->clickcontroller = 'projects';
		$projects_do->editclickaction = 'view';
		
		$this->contents = $projects_do;
	
	}

}

// End of CurrentProjectsEGlet

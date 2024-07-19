<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MyCurrentIssuesEGlet extends SimpleListUZlet
{
	
	protected $version = '$Revision: 1.7 $';
	
	function populate()
	{
		
		$my_issues = new ProjectIssueLineCollection();
		
		$my_issues->setParams();
		
		$sh = new SearchHandler($my_issues, false);
		
		$sh->addConstraint(new Constraint('completed', 'is', 'NULL'));
		$sh->addConstraint(new Constraint('assigned_to', '=', EGS_USERNAME));
		
		$this->setSearchLimit($sh);
		
		$sh->setFields(array('id', 'title', 'description'));
		
		$sh->setOrderBy('created', 'DESC');
		
		$my_issues->load($sh);
		
		//$tasks_do->clickcontroller = 'projectissues';
		//$tasks_do->editclickaction = 'view';
		
		$this->contents = $my_issues;
		
	}
	
}

// End of MyCurrentIssuesEGlet

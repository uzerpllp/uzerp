<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class NewIssuesEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.7 $';

	function populate()
	{

		$new_issues = new ProjectIssueLineCollection();

		$new_issues->setParams();

		$sh = new SearchHandler($new_issues, false);

		$sh->addConstraint(new Constraint('completed', 'is', 'NULL'));
		$sh->addConstraint(new Constraint('assigned_to', 'is', 'NULL'));

		$this->setSearchLimit($sh);

		$sh->setFields(array('id', 'title', 'description'));

		$sh->setOrderBy('created', 'DESC');

		$new_issues->load($sh);

		//$tasks_do->clickcontroller = 'projectissues';
		//$tasks_do->editclickaction = 'view';

		$this->contents = $new_issues;

	}

}

// End of NewIssuesEGlet

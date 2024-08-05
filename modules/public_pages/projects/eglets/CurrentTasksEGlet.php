<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CurrentTasksEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.7 $';

	function populate()
	{

		$tasks_do = new TaskCollection();

		$tasks_do->setParams();

		$sh = new SearchHandler($tasks_do,false);

		$sh->setFields(array('id', 'name'));

		$this->setSearchLimit($sh);

		$sh->setOrderBy('created', 'ASC');

		$sh->addConstraint(new Constraint('progress', '<', 100));
		$sh->addConstraint(new Constraint('owner', '=', EGS_USERNAME));

		$tasks_do->load($sh);

		$tasks_do->clickcontroller = 'tasks';
		$tasks_do->editclickaction = 'view';

		$this->contents = $tasks_do;

	}

}

// End of CurrentTasksEGlet

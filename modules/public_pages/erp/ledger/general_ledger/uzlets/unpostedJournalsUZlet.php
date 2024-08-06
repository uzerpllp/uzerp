<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class unpostedJournalsUZlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.2 $';

	function getClassName()
	{
		return 'eglet double_eglet';
	}

	function populate()
	{
		$header = DataObjectFactory::Factory('GLTransactionHeader');

		$journals = new GLTransactionHeaderCollection($header);

		$journals->setParams();

		$sh = new SearchHandler($journals, false);

 		$sh->addConstraint(new Constraint('status', '=', $header->newStatus()));
 		$sh->addConstraint(new Constraint('type', '=', $header->standardJournal()));

 		$sh->setFields(array('id', 'docref', 'transaction_date', 'glperiod', 'reference', 'comment'));

		$this->setSearchLimit($sh);

		$journals->load($sh);

		$journals->clickcontroller = 'gltransactionheaders';
		$journals->editclickaction = 'view';

		$this->contents = $journals;
	}
}

// End of unpostedJournalsUZlet

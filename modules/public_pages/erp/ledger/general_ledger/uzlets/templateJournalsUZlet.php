<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class templateJournalsUZlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.2 $';
	
	protected $template = 'list_uzlet.tpl';
	
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
 		$sh->addConstraint(new Constraint('type', '=', $header->templateJournal()));
 		
 		$sh->setFields(array('id', 'docref', 'reference', 'comment'));
 		
		$this->setSearchLimit($sh);
		
		$journals->load($sh);
		
		$journals->clickcontroller = 'gltransactionheaders';
		$journals->editclickaction = 'view';
		
		$this->contents = $journals;
	}
}

// End of templateJournalsUZlet

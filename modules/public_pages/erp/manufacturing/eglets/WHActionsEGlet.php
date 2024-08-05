<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WHActionsEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.8 $';

	protected $template = 'whactions.tpl';

	function populate()
	{
		$collection = new WHActionCollection();

		$collection->setParams();

		$sh = new SearchHandler($collection);

		$sh->addConstraint(new Constraint('type', '=', 'M'));

		$this->setSearchLimit($sh);

		$sh->setOrderBy('position');

		$collection->load($sh);

		$this->contents = $collection;
	}

}

// End of WHActionsEGlet

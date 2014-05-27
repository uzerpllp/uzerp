<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PartyNote extends DataObject
{
	
	protected $version = '$Revision: 1.8 $';
	
	protected $defaultDisplayFields = array('title'=>'Title'
										   ,'note'=>'Note'
										   ,'note_type'=>'Type'
//										   ,'party'=>'Party'
										   ,'owner'=>'Owner'
										   ,'alteredby'=>'Altered By'
										   ,'created'=>'Created'
										   ,'lastupdated'=>'Updated');
	
	function __construct($tablename = 'party_notes')
	{
		parent::__construct($tablename);
		
		$this->orderby	= $this->idField;
		$this->orderdir	= 'DESC';
		
		$this->belongsTo('Party','party_id','party');
		
 		$this->setEnum('note_type'
							,array('contacts'=>'Contacts'
								  ,'purchase_invoicing'=>'Purchase Invoice'
								  ,'purchase_order'=>'Purchase Order'
								  ,'sales_invoicing'=>'Sales Invoice'
								  ,'sales_order'=>'Sales Order'
								  )
 						);
 		
		$this->getField('note_type')->setDefault('contacts');
	
	}

}	

// End of PartyNote

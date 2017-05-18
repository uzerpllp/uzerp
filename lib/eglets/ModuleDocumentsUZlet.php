<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModuleDocumentsUZlet extends SimpleListUZlet
{

	protected $version='$Revision: 1.3 $';
	
	protected $template = 'module_documents.tpl';
	
	function getClassName()
	{
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		
		$module = DataObjectFactory::Factory('ModuleObject');
		
		$module->loadBy('name', $_GET['module']);
		
		$files = new EntityAttachmentCollection();
		
		$files->setParams();
		
		$pl = new PageList(' Documents');
		
		$sh = new SearchHandler($files, FALSE);
		
		$fields = array('id', 'file as document', 'revision', 'note', 'file_id');
		
		$sh->setOrderBy('file');
		
		$sh->setFields($fields);
		
		$sh->addConstraint(new Constraint('entity_id', '=', $module->id));
		$sh->addConstraint(new Constraint('data_model', '=', 'moduleobject'));
		
		$this->setSearchLimit($sh);
				
		$files->load($sh);
		
		$this->contents = $files;
		
		$ao = AccessObject::Instance();
		
		$this->contents->can_upload = $ao->hasPermission($_GET['module'], 'attachments', 'new');
		
	}
	
}

// End of ModuleDocumentsUZlet

<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EntityAttachment extends DataObject {
	
	protected $version = '$Revision: 1.6 $';
	
	protected $defaultDisplayFields = array(
		'file' => 'Name',
		'type' => 'Type',
		'size' => 'Size',
		'note' => 'Note'
	);
	
	function __construct($tablename = 'entity_attachments')
	{
		
		parent::__construct($tablename);
		
		$this->idField = 'id';
		
		$this->belongsTo('Entity', 'entity_id');
		$this->belongsTo('File', 'file_id');
		
		$this->setAdditional('type', 'varchar');
		$this->setAdditional('size', 'bigint');
		$this->setAdditional('note', 'varchar');
		
	}

	public function delete($id = null, &$errors = array(), $archive = FALSE, $archive_table = null, $archive_schema = null)
	{
		if ($id==null && $this->isLoaded())
		{
			$id = $this->{$this->idField};
		}
		
		if (!$this->isLoaded() && !empty($id) && !is_null($id))
		{
			$this->load($id);
		}
				
		$file = DataObjectFactory::Factory('File');
		$outputs = new EntityAttachmentOutputCollection;
		$sh = new SearchHandler($outputs, false);
		$sh->addConstraint(new Constraint('entity_attachment_id', '=', $this->id));
		
		$db = DB::Instance();
		
		$db->StartTrans();

		$outputs->delete($sh);
		
		if (!parent::delete(null, $errors, $archive, $archive_table, $archive_schema)
			|| !$file->delete($this->file_id, $errors, $archive, $archive_table, $archive_schema) )
		{
			$result = FALSE;
			$db->FailTrans();
		}
		else
		{
			$result = TRUE;
		}
		
		$db->CompleteTrans();
		
		return $result;
	}
	
}

// end of EntityAttachment.php
<?php

class EntityAttachmentOutput extends DataObject {
	
	protected $defaultDisplayFields = [
		'name' => 'filename',
		'tag' => 'output_with'
	];
	
	function __construct($tablename = 'entity_attachment_outputs')
	{
		parent::__construct($tablename);
		
		$this->idField = 'id';

		$this->setEnum(
			'tag', [
				MFWorkorder::getAttachmentOutputsDefinition()['tag'] => MFWorkorder::getAttachmentOutputsDefinition()['name'],
			]
		);
	}
}
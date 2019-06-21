<?php

class EntityAttachmentOutputCollection extends DataObjectCollection {
	
	public $field;
	
	function __construct($do='EntityAttachmentOutput', $tablename='entity_attachment_outputs_overview') {
		parent::__construct($do, $tablename);
			
	}
}
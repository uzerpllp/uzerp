<?php
 
class POPlannedCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='POPlanned', $tablename='po_plannedoverview') {
		parent::__construct($do, $tablename);
			
	}

}
?>
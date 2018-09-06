<?php
 
class MFPlannedCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='MFPlanned', $tablename='mf_plannedoverview') {
		parent::__construct($do, $tablename);
			
	}

}
?>
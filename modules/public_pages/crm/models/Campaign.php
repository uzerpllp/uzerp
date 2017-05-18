<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Campaign extends DataObject {
	
	protected $version='$Revision: 1.5 $';
	
	protected $defaultDisplayFields=array('name'=>'Name'
										 ,'campaign_type'=>'Type'
										 ,'campaign_status'=>'Status'
										 ,'startdate'=>'Start Date'
										 ,'enddate'=>'End Date');
	
	function __construct($tablename='campaigns') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->view='';
		
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Campaigntype', 'campaign_type_id', 'campaign_type');
 		$this->belongsTo('Campaignstatus', 'campaign_status_id', 'campaign_status');

	}

}
?>
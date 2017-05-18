<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SetupController extends MasterSetupController {
	
	protected $version='$Revision: 1.3 $';
	
	protected $setup_options = array();
		
	protected $extra_fields = array();
	
	function __construct($module=null,$action=null) {
		parent::__construct($module,$action);
		$this->setup_options = array(
			'ticket_severities'=>'TicketSeverity',
			'ticket_priorities'=>'TicketPriority',
			'ticket_statuses'=>'TicketStatus',
			'ticket_categories'=>'TicketCategory',
			'ticket_defaults'=>array('model'=>'TicketConfiguration',
										   'module'=>$this->setup_module,
										   'controller'=>'ticketconfigurations',
										   'action'=>'index')
			);
		
	}
	
	function view () {
		if (isset($this->_data['option'])) {
			switch ($this->_data['option']) {
				case 'ticket_statuses':
					$model=new $this->setup_options['ticket_statuses'];
					$this->extra_fields['ticket_statuses']=array('status_code'=>array('type'=>'select'
																					 ,'options'=>$model->getEnumOptions('status_code')));
					break;
			}
			
		}
		
		parent::view();
		
	}
	
}
?>
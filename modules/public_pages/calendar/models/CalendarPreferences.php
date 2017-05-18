<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CalendarPreferences extends ModulePreferences {
	function __construct($getCurrentValues=true) {
		parent::__construct();
		
		$userPreferences = UserPreferences::instance();
		
		$this->setModuleName('calendar');
		
		$view = 'day';

		if ($getCurrentValues) {
			$view = $userPreferences->getPreferenceValue('default-calendar-view','calendar');
		}
		
		$this->registerPreference(
			array(
				'name' => 'default-calendar-view',
				'display_name' => 'Default View',
				'type' => 'select',
				'value'=>$view,
				'data' => array(
					array('label'=>'Day','value'=>'day'),
					array('label'=>'Week','value'=>'week'),
					array('label'=>'Month','value'=>'month')
				),
				'default' => 'week'
			)
		);
	}
}

?>

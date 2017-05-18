<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/**
 * To be used for 'Recently Viewed' (potentially for each model as well as globally)
 * and favourites and such nonsense.
 */
class PreferencePageList extends PageList {
	
	protected $version='$Revision: 1.2 $';
	
	function __construct($name,$length=10) {
		$userPreferences = UserPreferences::instance();
		$this->name=$name;
		$preferencePageList = $userPreferences->getPreferenceValue($this->name, '_pagelists');
		if($preferencePageList !== null)
			$this->queue = $preferencePageList;
		else
			$this->queue = new Queue($length);
	}	
	
	function save() {
		$userPreferences = UserPreferences::instance();
		$userPreferences->setPreferenceValue($this->name, '_pagelists', $this->queue);
	}
}
?>
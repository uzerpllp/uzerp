<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class IndexController extends DashboardController {

	public function Index() {
		$userPreferences = UserPreferences::instance(EGS_USERNAME);
		if($userPreferences->userCanSetPreferences()&&!$userPreferences->userHasPreferences()) {
			$message = "<strong>Welcome to EGS. It looks like this is your first time using the system, or you have not yet got round to setting your preferences.</strong><br />\n        You can <a href=\"/?module=dashboard&controller=preferences\">setup your preferences now</a>, or use the 'Preferences' link that is available in the top right of your screen when using the system.";
			$this->view->set("info_message",$message);
		}
		parent::index();
		$this->view->set('usealternative',false);
		$this->view->set('page_title',$this->getPageName());
	}

	protected function getPageName($base=null,$type=null) {
//		return parent::getPageName((empty($base)?'Home':$base),$type);
		return 'Home';
	}

}
?>

<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class IndexController extends Controller {


	public function Index(\DataObjectCollection $collection, $sh = '', &$c_query = \null) {
		$dashboard = new Dashboard();
		$quick_links=new StaticContentEGlet(new SimpleRenderer());
		$quick_links->setTemplate('eglets/admin_quick_links.tpl');
		$dashboard->addEGlet('Quick Links',$quick_links);
		
		$this->view->register('dashboard',$dashboard);
	}




}
?>

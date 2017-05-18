<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class FlashGraphRenderer implements Renderer {

	public function render(EGlet &$eglet, &$smarty) {
		if($eglet->should_render) {
			$smarty->assign('source',$eglet->getSource());
			$smarty->display('eglets/xml_swf_chart.tpl');
		}
	}
}
?>
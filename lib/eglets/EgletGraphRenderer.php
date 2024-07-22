<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EgletGraphRenderer implements Renderer {

	protected $version='$Revision: 1.1 $';

	public function render(EGlet &$eglet, &$smarty) {

		$json_options = $eglet->getContents();

		$options = json_decode((string) $json_options);

		$smarty->assign('identifier', $options->identifier);
		$smarty->assign('options', $json_options);

		switch (strtolower($options->type)) {

			case 'pie':
				$smarty->display('eglets/eglet_pie_chart.tpl');
				break;

			case 'bar':
				$smarty->display('eglets/eglet_bar_chart.tpl');
				break;

			case 'line':
				$smarty->display('eglets/eglet_line_chart.tpl');
				break;

		}

	}
}

// end of EgletGraphRenderer.php
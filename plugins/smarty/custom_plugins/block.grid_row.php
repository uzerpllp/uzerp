<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.6 $ */

function smarty_block_grid_row($params, $content, &$smarty, $repeat)
{
	
	$data_attr = build_data_attributes($params);
	
	// build the attribute string based on the attribute array
	$attr_string = build_attribute_string($data_attr);
	
	if (!empty($content))
	{
		return  '<tr ' . $attr_string . '>' . $content . '</tr>' . "\n";
	}
	else
	{
		$model = $params['model'];
		$smarty->assign('gridrow_id',$model->{$model->idField});
	}
	
}

// end of block.grid_row.php
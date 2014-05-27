<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.4 $ */

function smarty_block_data_table($params, $content, &$smarty, $repeat)
{
	
	if (!empty($content))
	{
		
		// set initial vars
		$data = array(
			'content'	=> $content,
			'attrs'		=> array()
		);
		
		// set id
		$data['attrs']['id'] = 'datagrid1';
		
		// set classes
		$data['attrs']['class'][] = 'datagrid';
		
		if (isset($params['class']))
		{
			$data['attrs']['class'][] = $params['class'];
		}
		
		
		/*
		 * Generate and output final HTML
		 ***********************************************************/
		
		// convert attrs array to a string
		$data['attrs'] = build_attribute_string($data['attrs']);
		
		// fetch smarty plugin template
		return smarty_plugin_template($smarty, $data, 'block.data_table');
		
	}

}

// end of block.data_table.php
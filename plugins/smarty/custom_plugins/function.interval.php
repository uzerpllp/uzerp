<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.8 $ */

function smarty_function_interval($params, &$smarty)
{
	
	$with = &$smarty->getTemplateVars('with');
	
	$data = array(
		'class' => array()
	);
	
	if (!empty($params['model']))
	{
		$model = &$params['model'];
	}
	else
	{
		$model = $with['model'];
	}
	
	if (!empty($params['readonly']) && $params['readonly'])
	{
		$data['attrs']['readonly']=$params['readonly'];
	}
	
	$controller_data	= &$smarty->getTemplateVars('controller_data');
	$modelname			= get_class($model);
	$basename			= $params['attribute'];
	$unitname			= $basename . '_unit';
	$name				= $modelname . '[' . $basename . ']';
	$field				= $model->getField($basename);
	
	if (isset($params['postfix']))
	{
		$name .= $params['postfix'];
	}	
	
	$data['select_name'] = $modelname . '[' . $unitname . ']';
	
	if (isset($params['postfix']))
	{
		$data['select_name'] .= $params['postfix'];
	}
	
	$data['attrs']['id']			= strtolower($modelname) . '_' . $basename;
	$data['attrs']['name']			= $name;
	$data['label']['attrs']['for']	= strtolower($modelname) . '_' . $basename;
	$data['label']['value']			= $field->tag;
	$data['hidden']					= FALSE;
	$data['hours_selected']			= '';
	$data['days_selected']			= '';

	if (isset($params['value']))
	{
		$data['attrs']['value'] = $params['value'];
	}
	elseif(isset($controller_data[$basename]))
	{
		$data['attrs']['value']	= $controller_data[$basename];
		$data['hidden']	= TRUE;
	}
	elseif (isset($_POST[$modelname][$basename]))
	{
		$data['attrs']['value'] = $_POST[$modelname][$basename];
	}
	elseif (!empty($params['group']) && isset($_POST[$params['group']][$modelname][$basename]))
	{
		$data['attrs']['value'] = $_POST[$params['group']][$modelname][$basename];
	}
	elseif (!empty($params['number']) && isset($_POST[$modelname][$params['number']][$basename]))
	{
		$data['attrs']['value'] = $_POST[$modelname][$params['number']][$basename];
	}
	elseif (isset($_SESSION['_controller_data'][$modelname][$basename]))
	{
		$data['attrs']['value'] = $_SESSION['_controller_data'][$modelname][$basename];
	}
	else
	{
		$data['attrs']['value'] = $field->value;
	}
	
	if (isset($_POST[$modelname][$unitname]))
	{
		$units = $_POST[$modelname][$unitname];
	}
	elseif (!empty($params['group']) && isset($_POST[$params['group']][$modelname][$unitname]))
	{
		$units = $_POST[$params['group']][$modelname][$unitname];
	}
	elseif (!empty($params['number']) && isset($_POST[$modelname][$params['number']][$unitname]))
	{
		$units = $_POST[$modelname][$params['number']][$unitname];
	}
	elseif (isset($_SESSION['_controller_data'][$modelname][$unitname]))
	{
		$units = $_SESSION['_controller_data'][$modelname][$unitname];
	}
	else
	{
		$units = 'hours';
	}
			
	$data['days_label']		= prettify('days');
	$data['hours_label']	= prettify('hours');
	$data['minutes_label']	= prettify('minutes');
	
	if (!isset($data['attrs']['value']) && $field->has_default == 1)
	{
		$data['attrs']['value'] = $field->default_value;
	}
	
	if (!empty($data['attrs']['value']))
	{
		
		if (is_array($data['attrs']['value']))
		{
			$units = $data['attrs']['value'][1];
			$data['attrs']['value'] = $data['attrs']['value'][0];
		}
		else
		{
			// ATTN: setting units twice?
			$data['attrs']['value'] = to_working_days($data['attrs']['value'],false);
			$data['attrs']['value'] = $data['attrs']['value'] * SystemCompanySettings::DAY_LENGTH;
		}
		
		$data[$units . '_selected'] = 'selected="selected"';
		
	}
	
	// processing over, collect vars
	// ATTN: should the above be $value, then converted to $data['attrs']['value']?
	
	if ($data['hidden'])
	{
		
		if (isset($days_selected))
		{
			$data['unit_value']	= 'days';
		}
		else
		{
			$data['unit_value']	= 'hours';
		}
	
	}

	$data['class'] = implode(' ', $data['class']);
	
	// convert attrs array to a string
	$data['attrs']			= build_attribute_string($data['attrs']);
	$data['label']['attrs']	= build_attribute_string($data['label']['attrs']);
	
	// fetch smarty plugin template
	return smarty_plugin_template($smarty, $data, 'function.interval');
	
}

// end of function.interval.php
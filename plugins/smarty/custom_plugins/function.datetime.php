<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/* $Revision: 1.8 $ */

function smarty_function_datetime($params, &$smarty) {

	$with = &$smarty->getTemplateVars('with');
	$data['display_tags'] = FALSE;
		
	$data['display_tags'] = (!isset($params['notags']));
	
	$data['label']['value'] = prettify((isset($params['attribute'])?$params['attribute']:''));

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
		$data['date']['attrs']['readonly']=$params['readonly'];
		$data['hour']['attrs']['readonly']=$params['readonly'];
		$data['minute']['attrs']['readonly']=$params['readonly'];
		$data['date']['additional_class']='';
	} else {
		$data['date']['additional_class']='datefield';
	}
	
	$controller_data = &$smarty->getTemplateVars('controller_data');
	

#	$html = <<<EOT
#<input type="text" name="%s" id="%s" class="icon date slim datefield%s" value="%s"/>&nbsp;
#<input type="text" name="%s" id="%s" class="timefield" value="%s" />:<input type="text" name="%s" id="%s" class="timefield" value="%s" />
#EOT;

	$basename	= $params['attribute'];
	$model_name	= get_class($model);
	$field		= $model->getField($basename);
	
	$data['date']['attrs']['type']		= 'text';
	$data['hour']['attrs']['type']		= 'text';
	$data['minute']['attrs']['type']	= 'text';
	
	$data['date']['attrs']['name']		= $model_name . '[' . $basename . ']';
	$data['hour']['attrs']['name']		= $model_name . '[' . $basename . '_hours]';
	$data['minute']['attrs']['name']	= $model_name . '[' . $basename . '_minutes]';
	
	$data['date']['attrs']['id']	= strtolower($model_name . '_' . $basename);
	$data['hour']['attrs']['id']	= strtolower($model_name . '_' . $basename . '_hours');
	$data['minute']['attrs']['id']	= strtolower($model_name . '_' . $basename . '_minutes');
	
	$data['label']['for'] = $data['date']['attrs']['id'];
	
	$hidden = FALSE;
	
	require_once __DIR__ . '/function.sessionvalue.php';

	$start_date			= smarty_function_sessionvalue($params, $model_name, $basename);
	$start_date_hour	= smarty_function_sessionvalue($params, $model_name, $basename.'_hours');
	$start_date_minute	= smarty_function_sessionvalue($params, $model_name, $basename.'_minutes');
	if (!empty($start_date)) {
		$value=$start_date;
		$value.=' '.(empty($start_date_hour)?'00':$start_date_hour);
		$value.=':'.(empty($start_date_minute)?'00':$start_date_minute);
	}
	
	if (isset($controller_data[$basename])) 
	{
		$hidden	= TRUE;
		$value	= $controller_data[$basename];
	}
	elseif (empty($value))
	{
		
		$value = $field->value;
		
		if (empty($value) && !empty($params['value'])) {
			$value = $params['value'];
		}
		
		if (empty($value) && $field->has_default == 1)
		{
			$value = date(DATE_TIME_FORMAT, $field->default_value);
		}
		
		$data['label']['value']				= $field->tag;
		
		if ($field->not_null == 1)
		{
			$data['label']['value']				.= '*';
			$data['date']['additional_class']	.= ' required';
		}
		
		
	}
	
	$data['label']['value'] = (isset($params['label'])?$params['label']:$data['label']['value']);
	
	if (!empty($value)) 
	{
		
		$format = format_for_strptime(DATE_TIME_FORMAT);
	
		if (strptime($value, $format) !== FALSE)
		{
			$date_value		= array_shift(explode(' ', $value));
			$hour_value		= array_shift(explode(':', array_pop(explode(' ', $value))));
			$minute_value	= array_pop(explode(':', array_pop(explode(' ', $value))));
		}
		else
		{
			list($date_value, $rest) = explode(' ', $value,2);
			$date_value = date(DATE_FORMAT, strtotime($date_value));
			list($hour_value, $minute_value) = explode(':', $rest);
		}
		
		$data['date']['attrs']['value']		= $date_value;
		$data['hour']['attrs']['value']		= $hour_value;
		$data['minute']['attrs']['value']	= $minute_value;
		
	}
	
	if ($hidden) 
	{
		$data['date']['attrs']['type']		= 'hidden';
		$data['hour']['attrs']['type']		= 'hidden';
		$data['minute']['attrs']['type']	= 'hidden';
	}
	
	foreach (array('date', 'hour', 'minute') as $type)
	{
		$data[$type]['attrs'] = build_attribute_string($data[$type]['attrs']);
	}
	
	return smarty_plugin_template($smarty, $data, 'function.datetime');
	
}

// end of function.datetime.php
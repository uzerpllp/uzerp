<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.11 $ */

function smarty_function_textarea($params, &$smarty) {
	
	$attribute			= $params['attribute'];
	$row_id				= '';
	$params['alias']	= '';
	
	// set default data array
	$data = array(
		'label' => array(
			'position'	=> 'left',
			'attrs'		=> array(),
			'value'		=> ''
		),
		'textarea' => array(
			'value' => '',
			'attrs'	=> array(
				'name' => $attribute
			)
		),
		'dt' => array(
			'attrs' => array()
		),
		'dd' => array(
			'attrs' => array()
		)
	);
	
	
	if (!empty($params['model']))
	{
		$model = &$params['model'];
	}
	else
	{
		$with	= &$smarty->getTemplateVars('with');
		$model	= $with['model'];
	}
	
	if (empty($params['alias']))
	{
		
		if (isset($with['alias']))
		{
			$params['alias'] = $with['alias'];
		}
		
	}
	
	if (isset($params['rowid']))
	{
		$row_id = $params['rowid'];
	}
	
	if (isset($model))
	
	{
		if (!empty($params['alias']))
		{
			
			$alias			= $model->getAlias($params['alias']); 
			$aliasModelName	= $alias['modelName']; 
			$newmodel		= new $aliasModelName; 
			$field			= $newmodel->getField($attribute);
			
			$data['textarea']['attrs']['name']	= get_class($model).'['.$params['alias'].']['.$attribute.']';
			$data['textarea']['attrs']['id']	= get_class($model).'_'.$params['alias'].'_'.$attribute;
			
			if (!empty($_POST[get_class($model)][$params['alias']][$attribute]))
			{
				$data['textarea']['value'] = $_POST[get_class($model)][$params['alias']][$attribute];
			}
			elseif($model->isLoaded())
			{
				$newmodel = $model->$params['alias'];
				$data['textarea']['value'] = $newmodel->$attribute;
			}
		}
		else
		{
			
			if (!empty($_POST[get_class($model)][$attribute]))
			{
				$data['textarea']['value'] = $_POST[get_class($model)][$attribute];
			}
			elseif (!empty($params['group']) && !empty($_POST[$params['group']][$model->get_name()][$attribute]))
			{
				$data['textarea']['value'] = $_POST[$params['group']][$model->get_name()][$attribute];
			}
			elseif (!empty($params['number']) && !empty($_POST[$model->get_name()][$params['number']][$attribute]))
			{
				$data['textarea']['value'] = $_POST[$model->get_name()][$params['number']][$attribute];
			}
			elseif (!empty($_SESSION['_controller_data'][get_class($model)][$attribute]))
			{
				$data['textarea']['value'] = $_SESSION['_controller_data'][get_class($model)][$attribute];
			}
			
			$field = $model->getField($attribute);
			
			if (!empty($params['group']))
			{
				$data['textarea']['attrs']['name'] = $params['group'] . '[' . $model->get_name() . '][' . $attribute . ']';
			}
			elseif (isset($params['number']))
			{
				$data['textarea']['attrs']['name'] = $model->get_name() . '[' . $params['number'] . '][' . $attribute . ']';
			} 
			else
			{
				$data['textarea']['attrs']['name'] = get_class($model) . '[' . $attribute . ']';
			}
			
			$data['textarea']['attrs']['id'] = strtolower(get_class($model) . '_' . $attribute . $row_id);
			
			if ($model->loaded)
			{
				$data['textarea']['value'] = $model->$attribute;
			}
			
		}
		
		//check whether required field
		if ($field->not_null == 1)
		{
			$data['label']['value'] .= '*';
		}
		
	}
	
	
	if (isset($params['editor']) && $params['editor'] == 'tinymce')
	{
		$data['textarea']['attrs']['class'][] = "tinymce";
	}
	
	if (isset($params['class']) && $params['class'] != '')
	{
		$data['textarea']['attrs']['class'][] = $params['class'];
	}
	
	if (!empty($params['label']))
	{
		$data['label']['value'] = $params['label'];
	}
	else
	{
		$data['label']['value'] = $field->tag;
	}
	
	if (!empty($params['value']))
	{
		$data['textarea']['value'] = $params['value'];
	}
	
	$data['display_tags'] = !(isset($params['tags']) && $params['tags'] == 'none');
	
	if (!empty($params['label_position']))
	{
		$data['label']['position'] = $params['label_position'];
	}
	
	// NOTE: these are inline styles... I know it's now ideal :-(
	if (in_array($data['label']['position'], array('top', 'above')))
	{
		$data['dt']['attrs']['style'][]	= 'text-align: left;';
		$data['dt']['attrs']['style'][]	= 'border-bottom: 0;';
		$data['dd']['attrs']['style'][]	= 'clear: both;';
		$data['dd']['attrs']['style'][]	= 'width: 100%;';
		$data['dd']['attrs']['style'][]	= 'padding: 0;';
	}
	
	// convert attrs array to a string
	$data['textarea']['attrs']	= build_attribute_string($data['textarea']['attrs']);
	$data['dt']['attrs']		= build_attribute_string($data['dt']['attrs']);
	$data['dd']['attrs']		= build_attribute_string($data['dd']['attrs']);
	
	// fetch smarty plugin template
	return smarty_plugin_template($smarty, $data, 'function.textarea');
	
}

// end of function.textarea.php
<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.44 $ */

function smarty_function_select($params, &$smarty) {

	// set a few variables
	$attribute			= $params['attribute'];
	$fallback			= TRUE;
	$use_autocomplete	= FALSE;
	$rowid				= '';
	$selected			= '';
	$opt_counter		= 0;
	
	$data = array(
		'select' => array(
			'attrs' => array()
		),
		'display_tags' => !(isset($params['tags']) && $params['tags'] == 'none')
	);
	
	// append any data attributes passed in through params with the attrs array
	$data['select']['attrs'] += build_data_attributes($params);
	
	$controller_data = &$smarty->getTemplateVars('controller_data');
	
	if (empty($params['force']) && (isset($controller_data[$attribute]) || !empty($params['hidden'])))
	{
		if (isset($controller_data['dialog']))
		{
			// force select to be hidden on a dialog
			$params['hidden'] = '';
		}
		return smarty_function_input($params, $smarty);
	}
	
	$with = &$smarty->getTemplateVars('with');
	
	// not empty = TRUE, empty = FALSE
	$use_collection = !empty($params['use_collection']);
	
	if (!empty($params['model']))
	{
		$model = &$params['model'];
	}
	else
	{
		$model = $with['model'];
	}
	
	$field	= $model->getField($attribute);
	$cc		= new ConstraintChain();
	
	if (!empty($params['constraint']))
	{
		
		$constraint = $params['constraint'];
		
		if (class_exists($constraint . 'Constraint'))
		{
			
			$cname		= $constraint . 'Constraint';
			$constraint	= new $cname($attribute);
			
			if (!($constraint instanceof Constraint))
			{
				throw new Exception($cname . ' is not a valid Constraint');
			}
			
		}
		else
		{
			$exp		= explode(',', $constraint);
			$constraint	= new Constraint($exp[0], $exp[1], $exp[2]);
		}
		
		$cc->add($constraint);
		
	}
	
	if (empty($params['alias']))
	{
		$params['alias'] = isset($with['alias']) ? $with['alias'] : '';
	}
	
	if (!empty($params['alias']))
	{
		
		$alias			= $model->getAlias($params['alias']); 
		$aliasModelName	= $alias['modelName']; 
		$newmodel		= new $aliasModelName; 
		$name			= $model->get_name() . '[' . $params['alias'] . '][' . $attribute . ']';
		$id				= $model->get_name() . '_' . $params['alias'] . '_' . $attribute;
		
		if (isset($_POST[$model->get_name()][$params['alias']][$attribute]))
		{
			$value = $_POST[$model->get_name()][$params['alias']][$attribute];
		}
		elseif ($model->isLoaded())
		{
			$newmodel	= $model->$params['alias'];			
			$value		= $newmodel->$attribute;
		}
		
		$model = $newmodel;
		
	}
	
	if (empty($params['composite']))
	{
		$params['composite'] = isset($with['composite']) ? $with['composite'] : ''; // DEFAULT
	}
	
	if (!empty($params['composite']))
	{
		
		$alias			= $model->getComposite($params['composite']); 
		$aliasModelName	= $alias['modelName']; 
		$newmodel		= new $aliasModelName; 
		$name			= $model->get_name() . '[' . $aliasModelName . '][' . $attribute . ']';
		$id				= $model->get_name() . '_' . $aliasModelName . '_' . $attribute;
		
		if (isset($_POST[$model->get_name()][$aliasModelName][$attribute]))
		{
			$value = $_POST[$model->get_name()][$aliasModelName][$attribute];
		}
		elseif($model->isLoaded())
		{
			$newmodel	= $model->$params['composite'];			
			$value		= $newmodel->$attribute;
		}
		
		$model = $newmodel;
		
	}
	
	if (isset($params['options']))
	{
		$get_options = $params['options'];
	}
	else
	{
		$get_options = '';
	}
	
	if  (!isset($params['autocomplete']) || empty($get_options)) {
		$use_autocomplete=false;
	} else {
		$use_autocomplete=true;
	}

	if (!is_null($field->options))
	{
		$get_options = $field->options->_data;
		$use_autocomplete=$field->options->_autocomplete;
		$text_value=$model->{$model->belongsToField[$attribute]};
		if (trim($text_value) == '' && count($get_options)>0) {
			$text_value=current($get_options);
		}
		$selected=key($get_options);
	}
	
	if (!empty($params['label']))
	{
		$data['select']['label'] = $params['label'];
	}
	else
	{
		$data['select']['label'] = $field->tag;
	}
	

	if (isset($params['number']))
	{
		$name = $model->get_name() . '[' . $params['number'] . '][' . $attribute . ']';
	}
	elseif(!isset($name))
	{
		$name = $model->get_name() . '[' . $attribute . ']';
	}
	
	if (isset($params['rowid']) && !empty($params['rowid']))
	{
		$rowid = $params['rowid'];
		$data['select']['attrs']['data-row-number'] = $params['rowid'];		
	}
	
	$id = $model->get_name() . '_' . $attribute . $rowid;
	
	if (!isset($name)) 
	{
		$name	= $model->get_name() . '[' . $attribute . ']';
		$id		= $model->get_name() . '_' . $attribute . $rowid;
	}
	
	if (isset($params['postfix']))
	{
		$name .= $params['postfix'];
	}
	
	// set field (data) attribute
	if (isset($field) && !empty($field))
	{
		$data['select']['attrs']['data-field'] = $field->name;
	}
	else
	{
		$data['select']['attrs']['data-field'] = $attribute;
	}
	
	
	if (!empty($params['depends'])) {
		$depends=explode(',', $params['depends']);
	} elseif (!is_null($field->options->_depends)) {
		$depends=array_keys($field->options->_depends);
	} else {
		$depends='';
	}
//    echo 'Smarty function.select depends='.$depends.'<br>';
	if (!empty($params['constrains'])) {
		$affects=explode(',', $params['constrains']);
		$constrains=true;
	} elseif (!is_null($field->options->_affects)) {
		$affects=array_keys($field->options->_affects);
		$constrains=true;
	} else {
		$affects='';
		$constrains=false;
	}
	
	// set the selected value from the value of the field, if present
	if ($model->isLoaded())
	{
		$selected = $model->$attribute;
	} 
	elseif (isset($controller_data[$attribute])) 
	{
		$selected = $controller_data[$attribute];
	}
	
	// set the selected value from the params value
	// can override the value of the field, if present
	
	if (isset($params['value']))
	{
		$selected = $params['value'];
	}
	
	$autocomplete_select_limit = get_config('AUTOCOMPLETE_SELECT_LIMIT');
	
	if (!empty($get_options) && !isset($params['forceselect']) && count($get_options) > $autocomplete_select_limit) {
		$use_autocomplete=true;
	}

	if (isset($model->belongsToField[$attribute]))
	{
		
		$x = $model->belongsTo[$model->belongsToField[$attribute]]["model"];
		
		$controllername = strtolower($x) . 's';
		
		if (isset($_SESSION['cache']['select'][$controllername]))
		{
			$data['select']['fk_link']['module'] = $_SESSION['cache']['select'][$controllername];
		}
		else
		{
			$component = new ModuleComponent();
			$component->loadBy(array('name', 'type'), array($controllername.'controller', 'C'));
			if ($component->isLoaded())
			{
				$data['select']['fk_link']['module'] = $_SESSION['cache']['select'][$controllername] = $component->module_name;
			}
		}
		
		if (isset($data['select']['fk_link']['module']))
		{
			$data['select']['fk_link']['controller'] = $controllername;
			$data['select']['fk_link']['action'] = 'new';
		}

		if (isset($params['data']))
		{
			
			if ($params['data'] instanceof DataObjectCollection)
			{
				$options = $params['data']->getAssoc();
			} elseif(is_array($params['data']))
			{
				$options = $params['data'];
			} else {
				throw new Exception('"data" paramater should be an associative array, or a DataObjectCollection');
			}
			
		}
		elseif (empty($get_options))
		{
			
			if ($model->belongsTo[$model->belongsToField[$attribute]]["cc"] instanceof ConstraintChain)
			{
				$cc->add($model->belongsTo[$model->belongsToField[$attribute]]["cc"]);
			}
			
			if (!empty($depends)) {
				foreach ($depends as $depends_field) {
					if (!is_null($model->$depends_field)) {
						$cc->add(new Constraint($depends_field, '=', $model->$depends_field));
					}
				}
			}
			$model->belongsTo[$model->belongsToField[$attribute]]["cc"]=$cc;
			if  (!isset($params['forceselect'])
				&& $model->getOptionsCount($attribute) > $autocomplete_select_limit)
			{
				
				$use_autocomplete	= TRUE;
				$text_value			= $model->{$model->belongsToField[$attribute]};
				
				if (trim($text_value) == '')
				{
					
					if (empty($selected) && !$model->isLoaded() && $field->has_default) {
						$selected=$field->default_value;
					}
					
					$temp = new $x;
					$temp->load($selected);
					$text_value=$temp->getIdentifierValue();
					
				}
									
			}
			else
			{
				
				$x = DataObjectFactory::Factory($x);
				
				if ($model->checkUniqueness($attribute))
				{
					// TODO: this can be inefficient in large data sets
					//		 needs to be 'not exists' correlated subquery
					$cc->add(new Constraint($x->idField, 'NOT IN', '(SELECT ' . $attribute . ' FROM ' . $model->getTableName() . ')'));
					$options	= $x->getAll($cc, TRUE, $use_collection);
					$fallback	= FALSE;
				}
				elseif($attribute == 'assigned')
				{
					$c_user	= $smarty->getTemplateVars('current_user');
					$cc		= new ConstraintChain();
					$db		= DB::Instance();
					if (!is_null($c_user->person_id)) {
						$cc->add(new Constraint('person_id','IN','(SELECT px.id FROM person px JOIN company cx ON (px.company_id=cx.id)  JOIN person pz ON (pz.company_id=cx.id) WHERE pz.id='.$db->qstr($c_user->person_id).')'));
					}
					$options = $x->getAll($cc, TRUE, $use_collection);
				}
				elseif(get_class($x) == 'User')
				{
					$options = $x->getActive($cc, FALSE);
				}
				else
				{
					$options = $x->getAll($cc, FALSE, $use_collection);
				}
				
			}
			
		}
		
	}
	elseif ($model->hasParentRelationship($attribute) && !isset($params['ignore_parent_rel']))
	{
		
		$db	= DB::Instance();
		$x	= clone $model;
		
		if ($model->isLoaded())
		{
			$cc->add(new Constraint($model->idField, '<>', $model->{$model->idField}));
		}
		
		$options = $x->getAll($cc,FALSE,$use_collection);

	}
	elseif($model->isEnum($attribute))
	{
		//enumeration
		$options = $model->getEnumOptions($attribute);
		
		foreach ($options as $key=>$option)
		{
			
			if ($selected == $option)
			{
				$selected = $key;
				break;
			}
			
		}
		
	}
	
	if ($field->not_null == 1)
	{
		$data['select']['label']	.= ' *';
		$data['select']['class'][]	 = 'required';
	}
	
	if (isset($_POST[$model->get_name()][$attribute]))
	{
		$selected = $_POST[$model->get_name()][$attribute];
	}
	elseif (isset($_SESSION['_controller_data'][get_class($model)][$attribute]))
	{
		$selected = $_SESSION['_controller_data'][get_class($model)][$attribute];
	}
	elseif (isset($_POST[$model->get_name()][$params['number']]))
	{
		$selected = $_POST[$model->get_name()][$params['number']][$attribute];
	}
	
	if (empty($selected) && $field->has_default && !$model->isLoaded()) 
	{
		$selected = $field->default_value;
	}
	
	if((isset($params['nonone']) && $params['nonone']=='true')
		|| (!is_null($field->options->_nonone) && $field->options->_nonone))
	{
		$data['select']['class'][] = "nonone";
	}
	
	if (!empty($depends)) {
		$data['select']['attrs']['data-depends'] = htmlspecialchars(json_encode($depends));
	}
	if (!empty($affects)) {
		$data['select']['attrs']['data-constrains'] = htmlspecialchars(json_encode($affects));
	}
	
	 //***************************
	//  COMPILE SELECT ATTRIBUTES
	
	$data['select']['attrs']['name']	= $name;
	$data['select']['attrs']['id']		= $id;

	if (isset($data['select']['fk_link']))
	{
		$data['select']['id']=$id;
	}
	
	// join the select class items with a space and pop them in the attrs class item
	if (!empty($data['select']['class']))
	{
		$data['select']['attrs']['class'] = implode(' ', $data['select']['class']);
	}
	
	// append any class passed in from params
	if (!empty($params['class']))
	{
		$data['select']['attrs']['class'] .= ' '.$params['class'];
	}
	
    // For new records, initialise the model attribute with the selected value
	if (!empty($selected)) {
    	$model->$attribute=$selected;
    }
    
	if(!$use_autocomplete || (isset($params['multiple']) && $params['multiple']))
	{
		if ($constrains) {
			$data['select']['attrs']['class'].=' uz-constrains';
		}
		if ((isset($params['multiple']) && $params['multiple']))
		{
			$data['dd']['attrs']['class']			= 'for_multiple';
			$data['select']['id']					= $id;
			$data['select']['attrs']['name']		= $data['select']['attrs']['name'].'[]';
			$data['select']['attrs']['multiple']	= 'multiple';
		}
		
		if (isset($params['onchange']))
		{
			$data['select']['attrs']['onchange'] = $params['onchange'];
		}
	
		if (isset($params['size'])) 
		{
			$data['select']['attrs']['size'] = $params['size'];
		}
	
		if (isset($params['disabled']))
		{
			$data['select']['attrs']['disabled'] = 'disabled';
		}
		
		// for the sake of it, trim the class string
		$data['select']['attrs']['class'] = trim($data['select']['attrs']['class']);
		
		// build the attribute string
		// $data['select']['attrs'] is no longer an array!!!
		
		$data['select']['attrs'] = build_attribute_string($data['select']['attrs']);
		
	
		 //*****************
		//  COMPILE OPTIONS
		
		// check whether required field
		if (!$field->not_null == 1 && (!isset($params['nonone']) || $params['nonone'] != 'true')
			&& (is_null($field->options->_nonone) || !$field->options->_nonone)) {
		
			$opt_counter++;
		
			$option_attrs = array('value' => '');
			
			if (empty($selected)) {
				$option_attrs['selected'] = 'selected';
				$selected = 'None';
			}
			
			$data['select']['options'][$opt_counter]['attrs'] = build_attribute_string($option_attrs);
			$data['select']['options'][$opt_counter]['value'] = 'None';
		
		}
	
		if (isset($params['start'])) 
		{
		
			$opt_counter++;
		
			$option_attrs = array('value' => '');
			
			if (empty($selected)) {
				$option_attrs['selected'] = 'selected';
				$selected = $params['start'];
			}
			
			$data['select']['options'][$opt_counter]['attrs'] = build_attribute_string($option_attrs);
			$data['select']['options'][$opt_counter]['value'] = $params['start'];
		
		}
		
		// fallback is a horrible hack for now (for uniqueness constraints on dropdowns)
		if ($fallback && is_array($get_options))
		{
			$options = $get_options;
		}
	
		if (!empty($options))
		{

			foreach ($options as $key=>$value)
			{
			
				$option_attrs = array();
			
				$opt_counter++;
		
				$option_attrs['value'] = h($key, ENT_COMPAT);
			
				if ((is_array($selected) && in_array($key, $selected)) || ($selected==$key))
				{
					$option_attrs['selected'] = 'selected';
				}
			
				$data['select']['options'][$opt_counter]['attrs'] = build_attribute_string($option_attrs);
				$data['select']['options'][$opt_counter]['value'] = h($value);
		
				$data['autocomplete'] = false;
			
			}
		}
		
		$data['dt']['attrs'] = empty($data['dt']['attrs'])?'':build_attribute_string($data['dt']['attrs']);
		$data['dd']['attrs'] = empty($data['dd']['attrs'])?'':build_attribute_string($data['dd']['attrs']);
		
	} elseif (is_null($field->options) && !empty($get_options)) {
		
		if (empty($selected)) {
			$selected=key($get_options);
		}
		$data['autocomplete'] = true;
		$data['data_inline'] = true;
		$data['select']['selected'] = $selected;
		$data['select']['value'] = $get_options[$selected];
		$data['select']['options'] = json_encode(dataObject::toJSONArray($get_options));
		$data['select']['attrs']['class'] = 'uz-autocomplete ui-autocomplete-input icon '.$data['select']['attrs']['class'];
		
	} else {
	
		$data['autocomplete'] = true;
		$data['data_inline'] = false;

		if (isset($params['action'])) {
			$data['select']['attrs']['data-action']=$params['action'];
		} elseif (!is_null($field->options)) {
			$data['select']['attrs']['data-action']=$field->options->_action;
		} else {
			$data['select']['attrs']['data-action']='getOptions';
		}
		if (!empty($params['identifierfield'])) {
			$data['select']['attrs']['data-identifierfield']=json_encode(explode(',', $params['identifierfield']));
		} elseif (!is_null($field->options->_identifierfield)) {
			$data['select']['attrs']['data-identifierfield']=json_encode(array_keys($field->options->_identifierfield));
		} else {
			$data['select']['attrs']['data-identifierfield']='""';
		}
		if (!empty($params['use_collection'])) {
			$data['select']['attrs']['data-use_collection']=($params['use_collection']?'true':'false');
		} elseif (!is_null($field->options->_use_collection)) {
			$data['select']['attrs']['data-use_collection']=($field->options->_use_collection?'true':'false');
		} else {
			$data['select']['attrs']['data-use_collection']='""';
		}
		if (empty($text_value) && !empty($selected)) {
			$text_value=$selected;
		}
		$text_attribute=$attribute.'_text';
		$text_name=str_replace($attribute, $text_attribute, $name);
		if(isset($_POST[$model->get_name()][$text_attribute])) {
			$text_value = $_POST[$model->get_name()][$text_attribute];
		} elseif(isset($_SESSION['_controller_data'][get_class($model)][$text_attribute])) {
			$text_value = $_SESSION['_controller_data'][get_class($model)][$text_attribute];
		} elseif (isset($_POST[$model->get_name()][$params['number']])) {
			$text_value = $_POST[$model->get_name()][$params['number']][$text_attribute];
		}
		$data['select']['attrs']['data-attribute']=$attribute;

		$data['select']['attrs']['class'] = 'uz-autocomplete ui-autocomplete-input icon '.$data['select']['attrs']['class'];
		
		$attrs=array('name'=>$data['select']['attrs']['name']
					,'id'=>$data['select']['attrs']['id']
					,'value'=>h($selected, ENT_COMPAT)
					,'data-text_id'=>$data['select']['attrs']['id'].'_text');

		if (isset($data['select']['attrs']['data-depends'])) {
			$attrs['data-depends']=$data['select']['attrs']['data-depends'];
		}
		if (isset($data['select']['attrs']['data-constrains'])) {
			$attrs['data-constrains']=$data['select']['attrs']['data-constrains'];
			unset($data['select']['attrs']['data-constrains']);
		}
		if ($constrains) {
			$attrs['class']='"uz-constrains"';
		}
					
		$data['select']['attrs']['data-id']=$data['select']['attrs']['id'];
		$data['select']['attrs']['value']=$text_value;
		$data['select']['attrs']['id']=$data['select']['attrs']['id'].'_text';
		$data['select']['attrs']['name']=$text_name;
		
		$data['select']['attrs_text'] = build_attribute_string($data['select']['attrs']);
		$data['select']['attrs'] = build_attribute_string($attrs);
		
	}
	
// this should be further up?
	if (prettify($params['attribute']) == 'EGS_HIDDEN_FIELD')
	{
		return '';
	}
	// fetch smarty plugin template
	return smarty_plugin_template($smarty, $data, 'function.select');
	
}

// end of function.select.php

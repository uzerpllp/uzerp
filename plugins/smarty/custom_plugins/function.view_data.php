<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.24 $ */

function smarty_function_view_data($params, &$smarty) {

	$attribute = $params['attribute'];
	$dtclass = $params['class'];
	
	// no point in continuing now
	if (prettify($attribute) == 'EGS_HIDDEN_FIELD')
	{
		return '';
	}
	
	// aobve + top
	if (isset($params['label_position']) && in_array($params['label_position'], array('above', 'top')))
	{
		$template_html = '<!-- %s --><dd id=%s class="wide">%s</dd>';
	}
	else 
	{
		if ($dtclass) {
			$template_html = '<dt>%s</dt><dd id=%s class=%4$s>%s</dd>';
		} else {
			$template_html = '<dt>%s</dt><dd id=%s>%s</dd>';
		}
	}
	
	$with = &$smarty->getTemplateVars('with');
	
	if (!empty($params['model']))
	{
		$model = &$params['model'];
	}
	else
	{
		$model = $with['model']; // default?
	}
	
	if (!empty($params['modifier']))
	{
		$modifier = $params['modifier'];
	}
	else
	{
		if (isset($with['modifier']))
		{
			$modifier = $with['modifier'];
		}
	}
	
	if (isset($params['value']) || (empty($attribute) && $params['value'] == NULL))
	{
		$value = $params['value'];
	}
	else
	{
		if ($model->isField($attribute))
		{
			$field = $model->getField($attribute);
			$value = $model->getFormatted($attribute);
		}
		
		if (empty($value))
		{
			$value = $model->$attribute;
		}
		
		if (substr($attribute, -2) == '()')
		{
			$attribute		= substr($attribute, 0, -2);
			$field->is_safe	= TRUE;
			$value			= call_user_func(array($model, $attribute));
		}
		
		if (method_exists($value, '__toString'))
		{
			$value = $value->__toString();
		}
				
	}
	
	// use the value as the css class instead of the class string from the view
	if ($dtclass == 'show_value')
		$dtclass = strtolower($value);
	{
		
	}
	
	if ($attribute == 'rag_status()')
	{
		var_dump($value);
	}
	
	if (empty($value) && $model->isEnum($attribute))
	{
		$values	= $model->getEnumOptions($attribute);
		$value	= $values[$value];
	}
	
	if ($model->isField($attribute))
	{
		$field	= $model->getField($attribute);
		$tag	= $field->tag;
	}
	
	if (empty($tag))
	{
		$tag = prettify($attribute);
	}
	
	if (isset($params['label']))
	{
		$tag = prettify($params['label']);
	}
		
	if (isset($params['type']) && $params['type'] == "percentage")
	{
		$value .= "&#37;";
	}
	
	// TODO: Could this be data driven?
	//		 Should this be pre-populated with standard stuff, or removed
	//		 and handled dynamically via 'belongs_to'; see below?
	// Probably should create this as a structure array that builds up the
	// link to be used below.
	$temp_lookups = array(
		'employee'				=> 'hr',
		'company'				=> 'contacts',
		'person'				=> 'contacts',
		'project'				=> 'projects',
		'originator_person'		=> 'contacts',
		'originator_company'	=> 'contacts',
		'opportunity'			=> 'crm'
		);

	if (str_replace(' ', '', $value) == '') 
	{
		$value = '<span class="blank">-</span>';
	}
	elseif (isset($_SESSION['cache'][get_class($model)][$attribute]) && !empty($model->{$_SESSION['cache'][get_class($model)][$attribute]['fk_field']}))
	{
			$value = link_to(
						array(
							'pid'			=> $_SESSION['cache'][get_class($model)][$attribute]['pid'],
							'module'		=> $_SESSION['cache'][get_class($model)][$attribute]['module'],
							'controller'	=> $_SESSION['cache'][get_class($model)][$attribute]['controller'],
							'action'		=> 'view',
							$_SESSION['cache'][get_class($model)][$attribute]['id_field']	=> $model->{$_SESSION['cache'][get_class($model)][$attribute]['fk_field']},
							'value'			=> h($value)
						)
					);
			
	}
	elseif (isset($params['link_to'])) 
	{
	
		$link			= $params['link_to'];
		$id_candidate	= $attribute . '_id';
		
		if ($model->isField($id_candidate))
		{
			$id		= $model->$id_candidate;
			$link	= str_replace('__ID__', $id, $link);
		}
		
		if (!is_array($link))
		{
			$link			= str_replace(array('{', '}'), '', $link);
			$link			= json_decode('{' . $link . '}', TRUE);
		}
		
		$link['value']	= $value;
		$value			= link_to($link);
		
	}
	elseif ($attribute == 'email')
	{
		/* This auto links to emails */
		$link	= '<a class="mailto" href="mailto:' . $value . '">%s</a>';
		$value	= sprintf($link, $value);
	}
	elseif ($attribute == 'postcode')
	{
		// This auto links to google maps for postcodes
		$link	= '<a class="maps_link" href="http://maps.google.co.uk/maps?f=q&hl=en&q=%s">%s</a>';
		$value	= sprintf($link, $value, $value);
	}
	else
	{
		if (isset($model->belongsToField[$attribute]))
		{
			// This is probably a fk id field so need to translate the id value
			// to the fk identifier value via the belongsTo link
			$belongs_field = strtolower($model->belongsToField[$attribute]);
			$belongs_model = strtolower($model->belongsTo[$belongs_field]['model']);
			$fk_field		= $model->belongsTo[$belongs_field]['field'];
			// Should already have the value from above; if not, try getting it again
			if (empty($value))
			{
				$value		= $model->$belongs_field;
			}
		}
		
		if (isset($model->belongsTo[$attribute]))
		{
			// This is a fk field name via a belongsTo link
			$belongs_model	= strtolower($model->belongsTo[$attribute]['model']);
			$fk_field		= $model->belongsTo[$attribute]['field'];
		}
		
		if (!empty($belongs_model))
		{
			if (!isset($temp_lookups[$belongs_model]))
			{
				$modulecomponent = DataObjectFactory::Factory('ModuleComponent');
				$modulecomponent->loadBy(array('name', 'type'), array($belongs_model.'scontroller', 'C'));
				if ($modulecomponent->isLoaded())
				{
					$temp_lookups[$attribute] = $modulecomponent->module->name;
				}
			}
		}
		else
		{
			$belongs_model = $attribute;
		}
		if (isset($temp_lookups[$attribute]) || isset($temp_lookups[$params['fk']]))
		{
		
			if (isset($params['fk_field']))
			{
				$fk_field = $params['fk_field'];
			}
			elseif (empty($fk_field))
			{
				$fk_field = $attribute . '_id';
			}

			if (isset($params['fk']))
			{
				$belongs_model	= $params['fk'];
				$module	= $temp_lookups[$params['fk']];
			}
			else
			{
				$module	= $temp_lookups[$attribute];
			}
			
			if (method_exists($belongs_model . 'scontroller', 'view') && !is_null($model->{$fk_field}))
			{
				
				// check if the user is allowed to view the related link
				$ao			= AccessObject::Instance();
				$pid		= $ao->getPermission($module, $belongs_model . 's', 'view');
//				$allowed	= $ao->hasPermission($module, $belongs_model . 's', 'view', $pid);
			
				// if the user is allowed to view the link, append a double right arrow
//				$suffix = ($allowed === TRUE ? ' &raquo;' : '');
				
				$link_model = DataObjectFactory::Factory($belongs_model);
				$value		= link_to(
					array(
						'module'				=> $module,
						'controller'			=> $belongs_model . 's',
						'action'				=> 'view',
						$link_model->idField	=> $model->{$fk_field},
						'value'					=> h($value)
						)
				);
				$_SESSION['cache'][get_class($model)][$attribute] = array('pid'=> $pid
																		 ,'module'=> $module
																		 ,'controller'=> $belongs_model . 's'
																		 ,'id_field'=> $link_model->idField
																		 ,'fk_field'=> $fk_field);
			
			}
		}
		elseif (isset($modifier))
		{
			$value = call_user_func($modifier, $value);
		}
		else
		{
			if (!$field->is_safe)
			{
				$value = h($value, ENT_QUOTES);
			}
		}
	}
	if (prettify($attribute) == 'EGS_HIDDEN_FIELD')
	{
		return '';
	}
		
	$template_id=(isset($params['id']))?$params['id']:$attribute;
	$template_id=(empty($template_id))?'':get_class($model).'_'.$template_id;
	return sprintf($template_html, $tag, $template_id, $value, $dtclass);

}

// end of function.view_data.php
<?php

function smarty_function_input($params, &$smarty)
{

	$controller_data 	= &$smarty->getTemplateVars('controller_data');
	$attribute			= $params['attribute'];
	$with				= &$smarty->getTemplateVars('with');

	// no point in continuing if we're only going to return false anyway
	if (prettify($attribute) == 'EGS_HIDDEN_FIELD')
	{
		return '';
	}

	// continue setting vars

	$rowid	= '';

	$data = array(
		'attrs' => array(
			'value'	=> '',
			'type'	=> 'text'
		),
		'display_tags'	=> !(isset($params['tags']) && $params['tags'] == 'none' || isset($with['tags']) && $with['tags'] == 'none'),
		'display_label'	=> (!isset($params['nolabel']) || $params['nolabel'] === FALSE)
	);

	// If there was en error or failed validation, add a class and message to the input.
	$flash = Flash::Instance();
	$errors = $flash->getMessages('errors');
	if (array_key_exists($attribute, $errors) || (!empty($with) && isset($with) && $with->model !== null && array_key_exists(strtolower(get_class($with['model'])).'_'.$attribute, $errors))) {
		$data['attrs']['class'][]	= 'field-error';
		$data['attrs']['data-field-error'] = $errors[$attribute];
	}

	// append any data attributes passed in through params with the attrs array
	$data['attrs'] += build_data_attributes($params);

	if (empty($params['group']))
	{
		$params['group'] = $with['group'] ?? '';
	}

	if (empty($params['alias']))
	{
		$params['alias'] = $with['alias'] ?? '';
	}

	if (empty($params['composite']))
	{
		$params['composite'] = $with['composite'] ?? '';
	}

	if (!empty($params['model']))
	{
		$model=&$params['model'];
	}
	else
	{
		$model=$with['model'];
	}

	if (!empty($params['hidden']))
	{
		$params['type'] = 'hidden';
	}

	// ATTN: used anymore?
	if (isset($params['onchange']))
	{
		$data['attrs']['onchange'] = $params['onchange'];
	}

	if (isset($params['onclick']))
	{
		$data['attrs']['onclick'] = $params['onclick'];
	}


	if (isset($params['label']))
	{

		$params['label'] = trim((string) $params['label']);

		if (strtoupper($params['label']) === 'FALSE' || empty($params['label']))
		{
			$data['display_label'] = FALSE;
		}
		else
		{
			$data['label']['value'] = prettify($params['label']);
		}

	}

	if (isset($params['rowid']))
	{
		$rowid = $params['rowid'];
	}

	// ATTN: above set params

	if (empty($data['attrs']['value']))
	{
		$data['attrs']['value'] = $params['value'];
	}

	if (isset($model))
	{

		if (!empty($params['alias']))
		{

			$alias			= $model->getAlias($params['alias']);
			$aliasModelName	= $alias['modelName'];
			$newmodel		= new $aliasModelName;
			$field			= $newmodel->getField($attribute);

			if (empty($params['label']))
			{
				$data['label']['value'] = $field->tag;
			}

			$data['attrs']['name']	= $model->get_name() . '[' . $params['alias'] . '][' . $attribute . ']';
			$data['attrs']['id']	= $model->get_name() . '_' . $params['alias'] . '_' . $attribute;

			if (isset($_POST[$model->get_name()][$params['alias']][$attribute]))
			{
				$data['attrs']['value'] = $_POST[$model->get_name()][$params['alias']][$attribute];
			}
			elseif(isset($_SESSION['_controller_data'][$model->get_name()][$attribute]))
			{
				$data['attrs']['value'] = $_SESSION['_controller_data'][$model->get_name()][$attribute];
			}
			elseif($model->isLoaded())
			{
				$newmodel = $model->$params['alias'];
				$data['attrs']['value'] = $newmodel->$attribute;
			}

			if ((!isset($data['attrs']['value']) || empty($data['attrs']['value'])) && isset($params['value']))
			{
				$data['attrs']['value'] = $params['value'];
			}
		}
		elseif(!empty($params['composite']))
		{

			$alias			= $model->getComposite($params['composite']);
			$aliasModelName	= $alias['modelName'];
			$newmodel		= new $aliasModelName;
			$field			= $newmodel->getField($attribute);

			if (empty($params['label']))
			{
				$data['label']['value'] = $field->tag;
			}

			$data['attrs']['name']	= $model->get_name() . '[' . $aliasModelName . '][' . $attribute . ']';
			$data['attrs']['id']	= $model->get_name() . '_' . $aliasModelName . '_' . $attribute;

			if (isset($_POST[$model->get_name()][$aliasModelName][$attribute]))
			{
				$data['attrs']['value'] = $_POST[$model->get_name()][$aliasModelName][$attribute];
			}
			elseif(isset($_SESSION['_controller_data'][$model->get_name()][$attribute]))
			{
				$data['attrs']['value']=$_SESSION['_controller_data'][$model->get_name()][$attribute];
			}
			elseif($model->isLoaded())
			{
				$newmodel = $model->$params['composite'];
				$data['attrs']['value'] = $newmodel->$attribute;
			}

			if ((!isset($data['attrs']['value']) || empty($data['attrs']['value'])) && isset($params['value']))
			{
				$data['attrs']['value'] = $params['value'];
			}

		}
		else
		{

			$field = $model->getField($attribute);

			if ($field !== FALSE)
			{
				// If data for the field is available and it is not the idfield, make it hidden
				// unless this is overridden (force=true)
				// or it is pop-up dialog form field that is not to be hidden
				if ((!isset($controller_data['dialog']) || isset($params['hidden']) || $params['type']=='hidden')
					&& isset($controller_data[$attribute])
					&& $model->idField != $attribute
					&& $params['force']!=true)
				{
					// If the value has been set above, don't clobber it!
					if (empty($data['attrs']['value'])) {
						$data['attrs']['value'] = $controller_data[$attribute];
					}
					
					$params['type'] = 'hidden';
				}

				// If force=true then use the avaiable data
				if ($params['force']) {
				    $data['attrs']['value'] = $controller_data[$attribute];
				}

				//Set "not editable" fields to DISABLED, but only if not already hidden
				if ($model->isNotEditable($field->name) && $model->isDisplayedField($field->name))
				{
					$params['disabled'] = 'disabled';
				}

				if (empty($params['label']))
				{
					$data['label']['value'] = $field->tag;
				}

				$data['attrs']['name'] = $model->get_name() . '[' . $attribute . ']';

				if (isset($params['postfix']))
				{
					$data['attrs']['name'] .= $params['postfix'];
				}

				if (!empty($params['group']))
				{
					$data['attrs']['name'] = $params['group'] . '[' . $model->get_name() . '][' . $attribute . ']';
				}
				elseif (isset($params['number']))
				{
					$data['attrs']['name'] = $model->get_name() . '[' . $params['number'] . '][' . $attribute . ']';
				}
				else
				{
					$data['attrs']['name'] = $model->get_name() . '[' . $attribute . ']';
				}

				if (isset($params['postfix']))
				{
					$data['attrs']['name'] .= $params['postfix'];
				}

				$data['attrs']['id'] = $model->get_name() . '_' . $attribute . $rowid;

				// ATTN: wow, big condition
				if ($params['type'] == 'checkbox' && (
						(!isset($_POST[$model->get_name()][$attribute]) && isset($_POST[$model->get_name()]['_checkbox_exists_'.$attribute]))
					 || (!isset($_POST[$params['group']][$model->get_name()][$attribute]) && isset($_POST[$params['group']][$model->get_name()]['_checkbox_exists_'.$attribute]))
					 || (!isset($_POST[$model->get_name()][$params['number']][$attribute]) && isset($_POST[$model->get_name()][$params['number']]['_checkbox_exists_'.$attribute]))
					 || (!isset($_SESSION['_controller_data'][$model->get_name()][$attribute]) && isset($_SESSION['_controller_data'][$model->get_name()]['_checkbox_exists_'.$attribute]))
					 ))
				{
					$data['attrs']['value'] = 'f';
				}
				else
				{

					if (isset($_POST[$model->get_name()][$attribute]))
					{
						$data['attrs']['value'] = $_POST[$model->get_name()][$attribute];
					}
					elseif (!empty($params['group']) && isset($_POST[$params['group']][$model->get_name()][$attribute]))
					{
						$data['attrs']['value'] = $_POST[$params['group']][$model->get_name()][$attribute];
					}
					elseif (!empty($params['number']) && isset($_POST[$model->get_name()][$params['number']][$attribute]))
					{
						$data['attrs']['value'] = $_POST[$model->get_name()][$params['number']][$attribute];
					}
					elseif (isset($_SESSION['_controller_data'][$model->get_name()][$attribute]))
					{
						$data['attrs']['value'] = $_SESSION['_controller_data'][$model->get_name()][$attribute];
					}
					elseif ($model->isLoaded())
					{
						$data['attrs']['value'] = $model->$attribute;
					}

				}

				if ((empty($data['attrs']['value']) || $params['type']=='radio') && $field->has_default == 1 && $field->name != $model->idField && !$model->isLoaded())
				{
					$data['attrs']['value'] = $field->default_value;
				}

				if ((!isset($data['attrs']['value']) || empty($data['attrs']['value'])) && isset($params['value']))
				{
					$data['attrs']['value'] = $params['value'];
				}

			}
			else
			{

				if (!empty($params['group']))
				{

					$data['attrs']['name'] = $params['group'] . '[' . $model->get_name() . '][' . $attribute . ']';

					if (isset($_POST[$params['group']][$model->get_name()][$attribute]))
					{
						$data['attrs']['value'] = $_POST[$params['group']][$model->get_name()][$attribute];
					}

				}
				elseif (isset($params['number']))
				{

					$data['attrs']['name'] = $model->get_name() . '[' . $params['number'] . '][' . $attribute . ']';

					if (isset($_POST[$model->get_name()][$params['number']][$attribute]))
					{
						$data['attrs']['value'] = $_POST[$model->get_name()][$params['number']][$attribute];
					}

				}
				else
				{

					$data['attrs']['name'] = $model->get_name() . '[' . $attribute . ']';

					if (isset($_POST[$model->get_name()][$attribute]))
					{
						$data['attrs']['value'] = $_POST[$model->get_name()][$attribute];
					}

				}

				$data['attrs']['id'] = $model->get_name() . '_' . $attribute . $rowid;
			}

		}

	}
	else
	{
		if (!empty($params['group']))
		{
			$data['attrs']['name'] = $params['group'] . '[' . $attribute . ']';
		}
		elseif (isset($params['number']))
		{
			$data['attrs']['name'] = $attribute . '[' . $params['number'] . ']';
		}
		else
		{
			$data['attrs']['name']	= $attribute;
		}

		$data['attrs']['id']	= $attribute . $rowid;
	}

	// set field (data) attribute
	if (isset($field) && !empty($field))
	{
		$data['attrs']['data-field'] = $field->name;
	}
	else
	{
		$data['attrs']['data-field'] = $attribute;
	}

	// set row number (data) attribute
	if (isset($params['rowid']) && !empty($params['rowid']))
	{
		$data['attrs']['data-row-number'] = $params['rowid'];
	}

	if (strpos((string) $attribute, 'confirmation_') === 0)
	{

		if (empty($label))
		{
			$data['label']['value'] = prettify($attribute);
		}

		$data['attrs']['class'][] = 'confirmation';

	}

	if (($field->not_null == 1 && $params['type'] !== 'checkbox' && empty($params['alias']) && !isset($params['readonly'])))
	{
		$data['label']['value']		.= ' <span class="req-field-indicator">*</span>';
		$data['attrs']['class'][]	 = 'required';
	}

	if (isset($params['readonly']) && $params['readonly'] !== FALSE)
	{
		$data['attrs']['readonly'] = 'readonly';
	}

	if (!empty($params['class']) && $params['class'] !== 'compulsory')
	{
		$data['attrs']['class'][] = $params['class'];
	}

	if (isset($params['id']))
	{
		$data['attrs']['id'] = $params['id'];
	}

	if (isset($params['name']))
	{
		$data['attrs']['name'] = $params['name'];
	}

	if (!empty($params['type']))
	{
		$data['attrs']['type'] = $params['type'];
	}

	switch ($params['type'])
	{

		case 'hidden':
			$data['display_tags']	= FALSE;
			$data['display_label']	= FALSE;
			$data['attrs']['type']	= 'hidden';
			break;

		case 'checkbox':

			if (in_array($data['attrs']['value'], array('t', 'true', 'on')))
			{
				$data['attrs']['checked'] = 'checked';
 			}

			if (isset($params['disabled']) && $params['disabled'])
			{
				$data['attrs']['disabled'] = 'disabled';
			}

			$data['attrs']['value']			= 'on';
			$data['attrs']['class'][]		= 'checkbox';
			$data['attrs_checkbox']['name']	= str_replace('['.$attribute, '[_checkbox_exists_' . $attribute, $data['attrs']['name']);
			$data['attrs_checkbox']['value']	= 'true';
			$data['attrs_checkbox']['type']	= 'hidden';

			break;

		case 'date':

			if (is_numeric($data['attrs']['value']))
			{
				$data['attrs']['value'] = date(DATE_FORMAT, $data['attrs']['value']);
			}
			else
			{

				if (!empty($data['attrs']['value']))
				{
					$modelvalue = $data['attrs']['value'];
				}
				else
				{
					$modelvalue = $model->$attribute;
				}

				if (!empty($modelvalue))
				{

					if (strpos((string) $modelvalue, '/'))
					{
						$data['attrs']['value'] = $modelvalue;
					} else {
						$data['attrs']['value'] = date(DATE_FORMAT, strtotime((string) $modelvalue));
					}

				}

			}

			$data['attrs']['type']		= 'text';
			$data['attrs']['class'][]	= 'icon date slim datefield';

			break;

		case 'datetime':

			if (is_numeric($data['attrs']['value'])) {

				if ($field->type === 'timestamp')
				{
					$data['attrs']['value'] = date(DATE_TIME_FORMAT, $data['attrs']['value']);
				}
				else
				{
					$data['attrs']['value'] = date(DATE_FORMAT, $data['attrs']['value']);
				}

			}

			$data['attrs']['type']		= 'text';
			$data['attrs']['class'][]	= 'datetimefield';

			break;

		case 'file':
			$data['attrs']['class'][] = 'file';

		    break;

		case 'radio':
			// $data['attrs']['value'] should either be the field default (new) or the field value (edit)
			// check this against the $params['value'] and set checked if match
			if ($data['attrs']['value'] == $params['value'] || ($data['attrs']['value'] == ($params['value']=='t')))
			{
				$data['attrs']['checked'] = '';
			}

			// The actual value for the radio button is the $params['value']
			if (isset($params['value']))
			{
				$data['attrs']['value'] = $params['value'];
			}

			$data['attrs']['class'][] = $attribute;

		default:
			$data['attrs']['value'] = uzh(trim((string) $data['attrs']['value']), ENT_QUOTES);

			break;

	}

	$data['label']['attrs']['for'] = $data['attrs']['id'];
	$data['label']['attrs']['id'] = $data['attrs']['id'].'_label';

	// convert attrs array to a string
	if (isset($data['attrs_checkbox']))
	{
		$data['attrs_checkbox']			= build_attribute_string($data['attrs_checkbox']);
	}
	$data['attrs']			= build_attribute_string($data['attrs']);
	$data['label']['attrs']	= build_attribute_string($data['label']['attrs']);

	// fetch smarty plugin template
	return smarty_plugin_template($smarty, $data, 'function.input');

}

// end of function.input.php

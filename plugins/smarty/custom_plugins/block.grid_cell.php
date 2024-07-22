<?php

/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/* $Revision: 1.20 $ */

function smarty_block_grid_cell($params, $content, &$smarty, $repeat)
{

	if (!empty($content))
	{

		// no point continuing if we're dealing with an id field
		if (substr((string) $params['field'], -2) == 'id') {
			return '';
		}

		// set vars
		$attrs = array();

		$id		= $smarty->getTemplateVars('gridrow_id');

		$with	= &$smarty->getTemplateVars('with');

		if (!empty($params['model']))
		{
			$model=&$params['model'];
		}
		else
		{
			$model=$with['model'];
		}

		if($model->getField($params['field'])->type !== 'html' && !isset($params['no_escape']))
		{
			$content = uzh(trim((string) $content));
		}

		if ($params['cell_num'] == 1)
		{

			if ($smarty->getTemplateVars('clickaction') <> 'none')
			{
				$link = array();

				$self = $smarty->getTemplateVars('self');

				if ($smarty->getTemplateVars('clickmodule'))
				{
					$link['modules'] = $smarty->getTemplateVars('clickmodule');
				}
				else
				{
					$link['modules'] = $self['modules'];
				}

				if ($smarty->getTemplateVars('clickcontroller'))
				{
					$clickcontroller = $smarty->getTemplateVars('clickcontroller');
				}
				else
				{
					$clickcontroller = $self['controller'];
				}

				if ($params['collection']->clickcontroller)
				{
					$clickcontroller = $params['collection']->clickcontroller;
				}

				$link['controller'] = $clickcontroller;

				if ($params['collection']->editclickaction)
				{
					$link['action'] = $params['collection']->editclickaction;
				}
				else
				{
					$link['action'] = $smarty->getTemplateVars('clickaction');
				}

				if ($smarty->getTemplateVars('linkfield'))
				{
				    $linkfield = $smarty->getTemplateVars('linkfield');
				}
				else
				{
				    $linkfield = $model->idField;
				}

				if ($smarty->getTemplateVars('linkvaluefield'))
				{
				    $link[$linkfield]=$model->{$smarty->getTemplateVars('linkvaluefield')};
				}
				else
				{
				    $link[$linkfield]=$model->$linkfield;
				}

				if ($smarty->getTemplateVars('linkdata'))
				{
				    foreach ($smarty->getTemplateVars('linkdata') as $field => $value)
				    {
				    	$link[$field] = $value;
				    }
				}

				foreach ($params as $field => $value)
				{
					if (substr((string) $field, 0, 1) == '_' && $model->isField(substr((string) $field,1)))
					{
						$link[substr((string) $field,1)] = $value;
					}
				}

				$link['value'] = $content;

				if (empty($link['pid']))
				{
					$ao			= AccessObject::Instance();
					$link['pid']= $ao->getPermission($link['modules'], $link['controller'], $link['action']);
				}

				if (!empty($link['pid']) && !empty($link['action']))
				{
					$content	= link_to($link, $data=true);

					$_SESSION['cache'][get_class($model)][$params['field']] = array('pid'		=> $link['pid']
																				   ,'modules'	=> $link['modules']
																				   ,'controller'=> $link['controller']
																				   ,'action'	=> $link['action']);
				}
				// Add a target for more info if the model has clickInfoData defined
				if($model->clickInfoData){
			        $attrs['data-id'] = $id;
			        $attrs['data-module'] = $link['modules'];
			        $attrs['data-controller'] = $link['controller'];
			        $content = $content . '<img class="click-info" src="assets/graphics/info.png" alt="More info" title="More info"/>';
			     }
		}
		elseif (!empty($content) && isset($_SESSION['cache'][get_class($model)][$params['field']]))
		{
			$content = link_to(
						array(
							'pid'			=> $_SESSION['cache'][get_class($model)][$params['field']]['pid'],
							'module'		=> $_SESSION['cache'][get_class($model)][$params['field']]['module'],
							'controller'	=> $_SESSION['cache'][get_class($model)][$params['field']]['controller'],
							'action'		=> 'view',
							$_SESSION['cache'][get_class($model)][$params['field']]['id_field']	=> $model->{$_SESSION['cache'][get_class($model)][$params['field']]['fk_field']},
							'value'			=> $content
						)
					);
			}

		}
		elseif (!empty($content))
		{
			// If the field is in a belongsTo FK link, then enable the link
			// if the user has access to the target function
			// TODO: this code is copied from view_data so could be moved to generic function?
			$temp_lookups = array();

			if (isset($model->belongsToField[$params['field']]))
			{
				$belongs_field = strtolower((string) $model->belongsToField[$params['field']]);
				$belongs_model = strtolower((string) $model->belongsTo[$belongs_field]['model']);
				$fk_field		= $model->belongsTo[$belongs_field]['field'];
			}

			if (isset($model->belongsTo[$params['field']]))
			{
				$belongs_model	= strtolower((string) $model->belongsTo[$params['field']]['model']);
				$fk_field		= $model->belongsTo[$params['field']]['field'];
			}
			if (!empty($belongs_model))
			{
				if (!isset($temp_lookups[$belongs_model]))
				{
					$modulecomponent = DataObjectFactory::Factory('ModuleComponent');
					$modulecomponent->loadBy(array('name', 'type'), array($belongs_model.'scontroller', 'C'));
					if ($modulecomponent->isLoaded())
					{
						$temp_lookups[$params['field']] = $modulecomponent->module->name;
					}
				}
			}
			else
			{
				$belongs_model = $params['field'];
			}
			if (isset($temp_lookups[$params['field']]) || (array_key_exists('fk', $params) && isset($temp_lookups[$params['fk']])))
			{

				if (isset($params['fk_field']))
				{
					$fk_field = $params['fk_field'];
				}
				elseif (empty($fk_field))
				{
					$fk_field = $params['field'] . '_id';
				}

				if (isset($params['fk']))
				{
					$belongs_model	= $params['fk'];
					$module	= $temp_lookups[$params['fk']];
				}
				else
				{
					$module	= $temp_lookups[$params['field']];
				}

				if (method_exists($belongs_model . 'scontroller', 'view') && !is_null($model->{$fk_field}))
				{

					$ao		= AccessObject::Instance();
					$pid	= $ao->getPermission($module, $belongs_model . 's', 'view');

					if (!empty($pid))
					{
						$link_model = DataObjectFactory::Factory($belongs_model);
						$content		= link_to(
							array(
								'pid'					=> $pid,
								'module'				=> $module,
								'controller'			=> $belongs_model . 's',
								'action'				=> 'view',
								$link_model->idField	=> $model->{$fk_field},
								'value'					=> $content
							)
						);
						$_SESSION['cache'][get_class($model)][$params['field']] = array('pid'=> $pid
																					   ,'module'=> $module
																					   ,'controller'=> $belongs_model . 's'
																					   ,'id_field'=> $link_model->idField
																					   ,'fk_field'=> $fk_field);
					}
					// Add a target for more info if the fk model has clickInfoData defined
				    if($link_model->clickInfoData){
			            $attrs['data-id'] = $model->{$fk_field};
			            $attrs['data-module'] = $module;
			            $attrs['data-controller'] = $belongs_model . 's';
			            $content = $content . '<img class="click-info" src="assets/graphics/info.png" alt="More info" title="More info"/>';
				    }
				}
			}
		}

		if ($params['field'] == $smarty->getTemplateVars('wide_column'))
		{
			$attrs['class'][] = 'wide_column';
		}

		if ($model->getField($params['field'])->type=='numeric')
		{
			$attrs['class'][] = 'numeric';
		}

		if ($model->getField($params['field'])->type=='bool')
		{
			$attrs['class'][] = 'icon';
			$content='<img src="/assets/graphics/' . (($model->{$params['field']}=='t')?'true':'false').'.png" alt="'.(($model->{$params['field']}=='t')?'true':'false').'" />';
		}

		if (isset($params['class']))
		{
			$attrs['class'][] = $params['class'];
		}

		$attrs['class'][] = 'row_'.$params['field'];

		if($params['field'] == 'email')
		{

			$email = $model->getField($params['field'])->value;
			if (!empty($email))
			{
				$content = '<a href="mailto:' . $email . '">' . $content . '</a>';
			}

		}

		if($params['field'] == 'company')
		{
			$field = ($model->companydetail->is_lead == 't')?'lead':$params['field'];
		}
		else
		{
			$field = $params['field'];
		}

		if (($params['field'] == 'company' || $params['field'] == 'person') && !is_null($model->{$params['field'] . '_id'}))
		{
			$content = sprintf('<a href="/?module=contacts&controller=%s&action=view&id=%s">%s</a>', $field . 's', $model->{$params['field'] . '_id'}, $content);
		}



		// Add a target for more info if the fk model has clickInfoData defined


		// convert attrs array to a string
		$attrs = build_attribute_string($attrs);
		return '<td ' . $attrs . ' >' . $content . '</td>' . "\n";

	}

}

// end of block.grid_cell.php

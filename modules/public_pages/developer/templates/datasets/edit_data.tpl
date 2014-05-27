{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper title=$title}
	<div id="view_page" class="clearfix">
		{form module=$module controller=$controller action=save_data notags=true}
			{input type="hidden" attribute='dataset_id' value=$dataset_id}
			{assign var=fk_links value=$model->belongsToField}
			{assign var=model_fields value=$model->getFields()}
			{with model=$model}
				{foreach item=tag key=name from=$fields}
					{assign var=model_field value=$model_fields[$name]}
					{if $field->isHidden || $name == 'id'}
						{input type="hidden" attribute=$name label=$tag}
					{elseif isset($fk_links.$name)}
						{select attribute=$name label=$tag}
					{elseif $model_field->type eq 'bool'}
						{input type="checkbox" attribute=$name label=$tag}
					{elseif ($model_field->type eq 'date') || ($model_field->type eq 'datetime')}
						{input type="date" attribute=$name label=$tag}
					{else}
						{input attribute=$name label=$tag}
					{/if}
				{/foreach}
			{/with}
			{submit}
		{include file='elements/saveAnother.tpl'}
		{/form}
		{include file='elements/cancelForm.tpl'}
	</div>
{/content_wrapper}
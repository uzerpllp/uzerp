{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller=$controller action="sharingsave"}
		<input type="hidden" name="id" value="{$id}"/>
		<input type="hidden" name="model" value="{$model_name}"/>
		{view_data model=$model attribute=identifierField label=$model->identifierField value=$model->getIdentifierValue()}
		<dt>
			<label for="write[]">
				Groups able to edit this {$model->getTitle()}
			</label>:
		</dt>
		<dd>
			<select multiple="multiple" name="write[]">
				{foreach from=$writeRoles item=item key=key}
					<option label="{$item.name}" value="{$key}" {if $item.selected}selected="selected"{/if}>
						{$item.name}
					</option>
				{/foreach}
			</select>
		</dd>
		<dt>
			<label for="read[]">
				Groups able to view this {$model->getTitle()}
			</label>:
		</dt>
		<dd>
			<select multiple="multiple" name="read[]">
				{foreach from=$readRoles item=item key=key}
					<option label="{$item.name}" value="{$key}" {if $item.selected}selected="selected"{/if}>
						{$item.name}
					</option>
				{/foreach}
			</select>
		</dd>
		{submit another="false"}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}